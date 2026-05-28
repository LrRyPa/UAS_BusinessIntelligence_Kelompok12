import sys
import os
sys.path.insert(0, os.path.join(os.path.dirname(__file__), ".."))

import pandas as pd
import pytest
from sqlalchemy import create_engine, inspect, text

from utils.extract   import DataExtractor
from utils.staging   import StagingCleaner, StagingLoader
from utils.transform import DataWarehouseTransformer
from utils.load      import DWLoader



SALES_ROWS = (
    "Sale_ID,Date,Store_ID,Product_ID,Units\n"
    "1,2022-01-01,1,1,2\n"
    "2,2022-01-02,2,3,1\n"
    "3,2022-01-03,1,2,3\n"
)

PRODUCTS_ROWS = (
    "Product_ID,Product_Name,Product_Category,Product_Cost,Product_Price\n"
    "1,Action Figure,Toys,$9.99 ,$15.99 \n"
    "2,Colorbuds,Electronics,$6.99 ,$14.99 \n"
    "3,Barrel O Slime,Art & Crafts,$1.99 ,$3.99 \n"
)

STORES_ROWS = (
    "Store_ID,Store_Name,Store_City,Store_Location,Store_Open_Date\n"
    "1,Maven Toys Guadalajara 1,Guadalajara,Residential,1992-09-18\n"
    "2,Maven Toys Monterrey 1,Monterrey,Residential,1995-04-27\n"
)

INVENTORY_ROWS = (
    "Store_ID,Product_ID,Stock_On_Hand\n"
    "1,1,27\n"
    "1,2,0\n"
    "2,3,15\n"
)

CALENDAR_ROWS = (
    "Date\n"
    "1/1/2022\n"
    "1/2/2022\n"
    "1/3/2022\n"
)

@pytest.fixture
def mxt_folder(tmp_path):
    (tmp_path / "sales.csv").write_text(SALES_ROWS,     encoding="utf-8")
    (tmp_path / "products.csv").write_text(PRODUCTS_ROWS, encoding="utf-8")
    (tmp_path / "stores.csv").write_text(STORES_ROWS,   encoding="utf-8")
    (tmp_path / "inventory.csv").write_text(INVENTORY_ROWS, encoding="utf-8")
    (tmp_path / "calendar.csv").write_text(CALENDAR_ROWS, encoding="utf-8")
    (tmp_path / "data_dictionary.csv").write_text("Table,Field\n", encoding="utf-8")
    return tmp_path


@pytest.fixture
def mxt_datasets(mxt_folder):
    return DataExtractor(
        source_folder=mxt_folder,
        skip_files=["data_dictionary.csv"],
    ).extract_all().datasets


@pytest.fixture
def sqlite_engine():
    return create_engine(
        "sqlite:///:memory:",
        connect_args={"check_same_thread": False},
    )

@pytest.fixture
def dw_tables(mxt_datasets):
    return DataWarehouseTransformer(datasets=mxt_datasets, pre_clean=True).transform()



class TestDataExtractor:
    def test_discovers_correct_files(self, mxt_folder):
        result = DataExtractor(
            source_folder=mxt_folder,
            skip_files=["data_dictionary.csv"],
        ).extract_all()
        assert set(result.datasets.keys()) == {
            "sales", "products", "stores", "inventory", "calendar"
        }

    def test_data_dictionary_skipped(self, mxt_folder):
        result = DataExtractor(
            source_folder=mxt_folder,
            skip_files=["data_dictionary.csv"],
        ).extract_all()
        assert "data_dictionary" not in result.datasets

    def test_column_normalisation(self, mxt_datasets):
        """Kolom harus jadi snake_case lowercase."""
        cols = list(mxt_datasets["sales"].columns)
        assert "sale_id"    in cols
        assert "store_id"   in cols
        assert "product_id" in cols

    def test_products_columns_normalised(self, mxt_datasets):
        cols = list(mxt_datasets["products"].columns)
        assert "product_id"       in cols
        assert "product_name"     in cols
        assert "product_category" in cols
        assert "product_cost"     in cols
        assert "product_price"    in cols

    def test_new_files_tracking(self, mxt_folder):
        extractor = DataExtractor(
            source_folder=mxt_folder,
            skip_files=["data_dictionary.csv"],
        )
        r1 = extractor.extract_all()
        assert len(r1.new_files) == 5   

        r2 = extractor.extract_all()
        assert r2.new_files == []       

    def test_missing_folder_raises(self):
        with pytest.raises(FileNotFoundError):
            DataExtractor(source_folder="/tidak/ada/folder")


class TestStagingCleaner:
    def test_currency_with_dollar_and_spaces(self):
        df = pd.DataFrame({"product_price": ["$15.99 ", "$3.99 ", "$9.99 "]})
        result = StagingCleaner().clean(df)
        assert result["product_price"].iloc[0] == pytest.approx(15.99)
        assert result["product_price"].iloc[1] == pytest.approx(3.99)

    def test_currency_with_comma(self):
        df = pd.DataFrame({"product_cost": ["$1,234.56", "$0.99"]})
        result = StagingCleaner().clean(df)
        assert result["product_cost"].iloc[0] == pytest.approx(1234.56)

    def test_date_iso_format(self):
        df = pd.DataFrame({"date": ["2022-01-01", "2022-06-15"]})
        result = StagingCleaner().clean(df)
        assert pd.api.types.is_datetime64_any_dtype(result["date"])
        assert result["date"].iloc[0].year == 2022

    def test_date_us_format(self):
        df = pd.DataFrame({"date": ["1/1/2022", "6/15/2022"]})
        result = StagingCleaner().clean(df)
        assert pd.api.types.is_datetime64_any_dtype(result["date"])

    def test_null_string_replacement(self):
        df = pd.DataFrame({"col": ["None", "null", "N/A", "", "valid"]})
        result = StagingCleaner().clean(df)
        assert result["col"].iloc[4] == "valid"
        for i in range(4):
            assert pd.isna(result["col"].iloc[i])

    def test_strip_whitespace(self):
        df = pd.DataFrame({"product_name": ["  Action Figure ", " Colorbuds"]})
        result = StagingCleaner().clean(df)
        assert result["product_name"].iloc[0] == "Action Figure"
        assert result["product_name"].iloc[1] == "Colorbuds"

    def test_numeric_inference(self):
        df = pd.DataFrame({"units": ["1", "2", "3"]})
        result = StagingCleaner().clean(df)

        assert result["units"].tolist() == ["1", "2", "3"]


class TestSchema:
    def test_dim_product_has_surrogate_key(self, dw_tables):
        dp = dw_tables["dim_product"]
        assert "product_key" in dp.columns
        assert "product_id"  in dp.columns
        assert len(dp) == 3

    def test_dim_product_has_price_tier(self, dw_tables):
        dp = dw_tables["dim_product"]
        assert "price_tier" in dp.columns
        row = dp[dp["product_id"] == 1].iloc[0]
        assert row["price_tier"] == "Premium"

        row2 = dp[dp["product_id"] == 2].iloc[0]
        assert row2["price_tier"] == "Mid-Range"

        row3 = dp[dp["product_id"] == 3].iloc[0]
        assert row3["price_tier"] == "Budget"

    def test_dim_store_has_surrogate_key(self, dw_tables):
        ds = dw_tables["dim_store"]
        assert "store_key"       in ds.columns
        assert "store_id"        in ds.columns
        assert "store_age_years" in ds.columns
        assert len(ds) == 2

    def test_dim_store_age_is_positive(self, dw_tables):
        ds = dw_tables["dim_store"]
        assert (ds["store_age_years"] > 0).all()

    def test_dim_category_built_from_products(self, dw_tables):
        dc = dw_tables["dim_category"]
        assert "category_key"   in dc.columns
        assert "category_name"  in dc.columns
        assert "category_group" in dc.columns
        cats = set(dc["category_name"].tolist())
        assert "Toys" in cats
        assert "Electronics" in cats

    def test_dim_category_group_mapping(self, dw_tables):
        dc = dw_tables["dim_category"]
        toys_row = dc[dc["category_name"] == "Toys"].iloc[0]
        assert toys_row["category_group"] == "Play"
        elec_row = dc[dc["category_name"] == "Electronics"].iloc[0]
        assert elec_row["category_group"] == "Active"

    def test_dim_date_calendar_attributes(self, dw_tables):
        dd = dw_tables["dim_date"]
        for col in ["date_key", "year", "quarter", "month", "month_name",
                    "week", "day_of_week", "day_name", "is_weekend",
                    "is_holiday_season"]:
            assert col in dd.columns, f"Kolom hilang: {col}"

    def test_fact_sales_has_all_fks(self, dw_tables):
        fs = dw_tables["fact_sales"]
        for fk in ["product_key", "store_key", "date_key"]:
            assert fk in fs.columns, f"FK hilang: {fk}"

    def test_fact_sales_measures(self, dw_tables):
        fs = dw_tables["fact_sales"]
        for m in ["units", "unit_price", "unit_cost",
                  "revenue", "cogs", "gross_profit", "margin_pct"]:
            assert m in fs.columns, f"Measure hilang: {m}"

    def test_fact_sales_revenue_computed(self, dw_tables):
        fs = dw_tables["fact_sales"]
        expected = fs["units"] * fs["unit_price"]
        pd.testing.assert_series_equal(
            fs["revenue"].reset_index(drop=True),
            expected.reset_index(drop=True),
            check_names=False,
        )

    def test_fact_sales_gross_profit_computed(self, dw_tables):
        fs = dw_tables["fact_sales"]
        expected = fs["revenue"] - fs["cogs"]
        pd.testing.assert_series_equal(
            fs["gross_profit"].reset_index(drop=True),
            expected.reset_index(drop=True),
            check_names=False,
        )

    def test_fact_inventory_shape(self, dw_tables):
        fi = dw_tables["fact_inventory"]
        assert "inventory_key"  in fi.columns
        assert "product_key"    in fi.columns
        assert "store_key"      in fi.columns
        assert "stock_on_hand"  in fi.columns
        assert len(fi) == 3     

class TestDWLoaderDryRun:
    def test_dry_run_no_db_needed(self, dw_tables):
        loader = DWLoader(engine=None, dry_run=True)
        result = loader.load_all(dw_tables)
        assert result.success
        assert len(result.loaded)  == 6  
        assert len(result.failed)  == 0

    def test_dry_run_skips_empty(self, sqlite_engine):
        loader = DWLoader(engine=None, dry_run=True)
        result = loader.load_all({"dim_empty": pd.DataFrame()})
        assert len(result.skipped) == 1
        assert len(result.loaded)  == 0

class TestSQLiteIntegration:
    def test_staging_writes_all_tables(self, mxt_datasets, sqlite_engine):
        StagingLoader(engine=sqlite_engine, schema="staging").load_all(mxt_datasets)
        tables = inspect(sqlite_engine).get_table_names()
        for expected in ["staging__sales", "staging__products",
                         "staging__stores", "staging__inventory"]:
            assert expected in tables, f"Tabel staging hilang: {expected}"

    def test_dw_loader_creates_schema(self, dw_tables, sqlite_engine):
        result = DWLoader(engine=sqlite_engine, schema="dw").load_all(dw_tables)
        assert result.success
        tables = inspect(sqlite_engine).get_table_names()
        for expected in [
            "dw__dim_product", "dw__dim_store", "dw__dim_category",
            "dw__dim_date", "dw__fact_sales", "dw__fact_inventory",
        ]:
            assert expected in tables, f"Tabel DW hilang: {expected}"

    def test_fact_sales_row_count(self, dw_tables, sqlite_engine):
        DWLoader(engine=sqlite_engine, schema="dw").load_all(dw_tables)
        with sqlite_engine.connect() as conn:
            count = conn.execute(
                text('SELECT COUNT(*) FROM "dw__fact_sales"')
            ).scalar()
        assert count == 3   

    def test_dim_product_row_count(self, dw_tables, sqlite_engine):
        DWLoader(engine=sqlite_engine, schema="dw").load_all(dw_tables)
        with sqlite_engine.connect() as conn:
            count = conn.execute(
                text('SELECT COUNT(*) FROM "dw__dim_product"')
            ).scalar()
        assert count == 3

    def test_upsert_idempotent(self, dw_tables, sqlite_engine):
        """Dua kali upsert tidak boleh menduplikasi data."""
        loader = DWLoader(engine=sqlite_engine, schema="dw", strategy="upsert")
        loader.load_all(dw_tables)
        loader.load_all(dw_tables)  

        with sqlite_engine.connect() as conn:
            count = conn.execute(
                text('SELECT COUNT(*) FROM "dw__dim_product"')
            ).scalar()
        assert count == 3   

    def test_row_counts_method(self, dw_tables, sqlite_engine):
        loader = DWLoader(engine=sqlite_engine, schema="dw")
        loader.load_all(dw_tables)
        counts = loader.row_counts()
        assert counts["dw__dim_product"]    == 3
        assert counts["dw__fact_sales"]     == 3
        assert counts["dw__fact_inventory"] == 3
