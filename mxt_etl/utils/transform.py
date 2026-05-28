import logging
from datetime import date
from typing import Dict

import pandas as pd

logger = logging.getLogger(__name__)


class _InlineCleaner:
    _CURRENCY_HINTS: set = {"cost", "price"}
    _DATE_HINTS: set = {"date", "open_date"}

    def clean_all(self, datasets: Dict[str, pd.DataFrame]) -> Dict[str, pd.DataFrame]:
        return {name: self.clean(df, name) for name, df in datasets.items()}

    def clean(self, df: pd.DataFrame, name: str = "") -> pd.DataFrame:
        df = df.copy()

        for col in df.select_dtypes(include=["object", "string"]).columns:
            df[col] = df[col].astype(str).str.strip()
            df.loc[
                df[col].str.lower().isin({"nan", "none", "null", "", "n/a"}), col
            ] = pd.NA

        for col in df.select_dtypes(include=["object", "string"]).columns:
            if any(h in col.lower() for h in self._CURRENCY_HINTS):
                cleaned = df[col].astype(str).str.replace(r"[\$,\s]", "", regex=True)
                num = pd.to_numeric(cleaned, errors="coerce")
                if num.notna().sum() / max(len(df), 1) > 0.5:
                    df[col] = num

        for col in df.select_dtypes(include=["object", "string"]).columns:
            if any(h in col.lower() for h in self._DATE_HINTS):
                parsed = pd.to_datetime(df[col], errors="coerce", dayfirst=False)
                if parsed.notna().sum() / max(len(df), 1) > 0.5:
                    df[col] = parsed

        for col in df.select_dtypes(include=["object", "string"]).columns:
            num = pd.to_numeric(df[col], errors="coerce")
            if num.notna().sum() / max(len(df), 1) > 0.8:
                df[col] = num

        return df


class DataWarehouseTransformer:
    def __init__(
        self,
        datasets: Dict[str, pd.DataFrame],
        pre_clean: bool = True,
    ) -> None:
        if pre_clean:
            self._data = _InlineCleaner().clean_all(datasets)
        else:
            self._data = {k: df.copy() for k, df in datasets.items()}


    def transform(self) -> Dict[str, pd.DataFrame]:
        logger.info("====== TRANSFORM: Membuat Galaxy Schema ======")
        result: Dict[str, pd.DataFrame] = {}

        result["dim_product"]  = self._build_dim_product()
        result["dim_store"]    = self._build_dim_store()
        result["dim_category"] = self._build_dim_category(result["dim_product"])
        result["dim_date"]     = self._build_dim_date()
        result["dim_product"]  = result["dim_product"].merge(
            result["dim_category"][["category_key", "category_name"]],
            left_on="product_category",
            right_on="category_name",
            how="left"
        )

        result["dim_product"] = result["dim_product"].drop(
            columns=["category_name"]
        )

        result["dim_product"] = result["dim_product"][
            [
                "product_key",
                "product_id",
                "category_key",
                "product_name",
                "product_category",
                "product_cost",
                "product_price",
                "price_tier",
            ]
        ]
        result["fact_sales"]     = self._build_fact_sales(result)
        result["fact_inventory"] = self._build_fact_inventory(result)

        for name, df in result.items():
            logger.info(
                "  ✔ Built '%-20s' : %7d baris × %d kolom",
                name, len(df), len(df.columns),
            )

        logger.info("============ TRANSFORM selesai ============")
        return result

    def _build_dim_product(self) -> pd.DataFrame:
        df = self._data.get("products", pd.DataFrame()).copy()
        if df.empty:
            return _empty(
                "product_key", "product_id", "product_name",
                "product_category", "product_cost", "product_price", "price_tier",
            )

        df["product_id"]    = pd.to_numeric(df.get("product_id"),    errors="coerce")
        df["product_cost"]  = pd.to_numeric(df.get("product_cost"),  errors="coerce")
        df["product_price"] = pd.to_numeric(df.get("product_price"), errors="coerce")

        df["price_tier"] = df["product_price"].apply(_price_tier)

        keep = [
            "product_id", "product_name", "product_category",
            "product_cost", "product_price", "price_tier",
        ]
        df = (
            _select_existing(df, keep)
            .drop_duplicates(subset=["product_id"])
            .dropna(subset=["product_id"])
            .reset_index(drop=True)
        )
        df.insert(0, "product_key", range(1, len(df) + 1))
        return df

    def _build_dim_store(self) -> pd.DataFrame:
        df = self._data.get("stores", pd.DataFrame()).copy()
        if df.empty:
            return _empty(
                "store_key", "store_id", "store_name", "store_city",
                "store_location", "store_open_date", "store_age_years",
            )

        df["store_id"] = pd.to_numeric(df.get("store_id"), errors="coerce")

        if "store_open_date" in df.columns:
            df["store_open_date"] = pd.to_datetime(
                df["store_open_date"], errors="coerce"
            )
            today = pd.Timestamp(date.today())
            df["store_age_years"] = (
                (today - df["store_open_date"]).dt.days / 365.25
            ).round(1)
            df["store_open_date"] = df["store_open_date"].dt.strftime("%Y-%m-%d")

        keep = [
            "store_id", "store_name", "store_city",
            "store_location", "store_open_date", "store_age_years",
        ]
        df = (
            _select_existing(df, keep)
            .drop_duplicates(subset=["store_id"])
            .dropna(subset=["store_id"])
            .reset_index(drop=True)
        )
        df.insert(0, "store_key", range(1, len(df) + 1))
        df["store_key"] = df["store_key"].astype(int)
        return df

    def _build_dim_category(self, dim_product: pd.DataFrame) -> pd.DataFrame:
        if dim_product.empty or "product_category" not in dim_product.columns:
            return _empty("category_key", "category_name", "category_group")

        cats = (
            dim_product[["product_category"]]
            .drop_duplicates()
            .dropna()
            .rename(columns={"product_category": "category_name"})
            .reset_index(drop=True)
        )
        cats["category_group"] = cats["category_name"].apply(_category_group)
        cats.insert(0, "category_key", range(1, len(cats) + 1))
        cats["category_key"] = cats["category_key"].astype(int)
        return cats

    def _build_dim_date(self) -> pd.DataFrame:
        date_series = []

        cal = self._data.get("calendar", pd.DataFrame())
        if not cal.empty and "date" in cal.columns:
            parsed = pd.to_datetime(cal["date"], errors="coerce", dayfirst=False)
            date_series.append(parsed.dropna())

        sales = self._data.get("sales", pd.DataFrame())
        if not sales.empty and "date" in sales.columns:
            parsed = pd.to_datetime(sales["date"], errors="coerce")
            date_series.append(parsed.dropna())

        if not date_series:
            logger.warning("Tidak ada kolom tanggal — dim_date kosong.")
            return pd.DataFrame()

        all_dates = (
            pd.concat(date_series)
            .drop_duplicates()
            .sort_values()
            .reset_index(drop=True)
        )

        dim = pd.DataFrame({"full_date": all_dates})
        dim["date_key"]          = dim["full_date"].dt.strftime("%Y%m%d").astype(int)
        dim["year"]              = dim["full_date"].dt.year
        dim["quarter"]           = dim["full_date"].dt.quarter
        dim["month"]             = dim["full_date"].dt.month
        dim["month_name"]        = dim["full_date"].dt.month_name()
        dim["week"]              = dim["full_date"].dt.isocalendar().week.astype(int)
        dim["day_of_week"]       = dim["full_date"].dt.dayofweek + 1   # Sen=1, Min=7
        dim["day_name"]          = dim["full_date"].dt.day_name()
        dim["is_weekend"]        = dim["day_of_week"].isin([6, 7]).astype(int)
        dim["is_holiday_season"] = dim["month"].isin([11, 12]).astype(int)
        dim["full_date"]         = dim["full_date"].dt.strftime("%Y-%m-%d")

        return dim.reset_index(drop=True)

    def _build_fact_sales(self, dims: Dict[str, pd.DataFrame]) -> pd.DataFrame:
        sales = self._data.get("sales", pd.DataFrame()).copy()
        if sales.empty:
            logger.error("'sales' tidak ditemukan — fact_sales kosong.")
            return pd.DataFrame()

        for col in ["sale_id", "store_id", "product_id", "units"]:
            if col in sales.columns:
                sales[col] = pd.to_numeric(sales[col], errors="coerce")

        if "date" in sales.columns:
            sales["date"] = pd.to_datetime(sales["date"], errors="coerce")
            sales["date_key"] = (
                sales["date"].dt.strftime("%Y%m%d")
                .pipe(pd.to_numeric, errors="coerce")
                .fillna(0)
                .astype(int)
            )

        dim_prod = dims.get("dim_product", pd.DataFrame())
        if not dim_prod.empty:
            price_lookup = dim_prod[
                ["product_id", "product_key", "product_cost", "product_price"]
            ].drop_duplicates(subset=["product_id"])
            price_lookup["product_id"] = pd.to_numeric(
                price_lookup["product_id"], errors="coerce"
            )
            sales = sales.merge(price_lookup, on="product_id", how="left")

            sales["unit_price"] = pd.to_numeric(sales["product_price"], errors="coerce")
            sales["unit_cost"]  = pd.to_numeric(sales["product_cost"],  errors="coerce")
        else:
            sales["product_key"] = pd.NA
            sales["unit_price"]  = pd.NA
            sales["unit_cost"]   = pd.NA

        sales = _resolve_sk(sales, dims.get("dim_store", pd.DataFrame()),
                            "store_id", "store_key")

        sales["revenue"]      = sales["units"] * sales["unit_price"]
        sales["cogs"]         = sales["units"] * sales["unit_cost"]
        sales["gross_profit"] = sales["revenue"] - sales["cogs"]
        sales["margin_pct"]   = (
            (sales["gross_profit"] / sales["revenue"].replace(0, pd.NA)) * 100
        ).round(2)

        wanted = [
            "sale_id",
            "date_key",
            "product_key",
            "store_key",
            "units",
            "unit_price",
            "unit_cost",
            "revenue",
            "cogs",
            "gross_profit",
            "margin_pct",
        ]
        fact = _select_existing(sales, wanted).copy()
        fact.insert(0, "sales_key", range(1, len(fact) + 1))
        return fact.reset_index(drop=True)

    def _build_fact_inventory(self, dims: Dict[str, pd.DataFrame]) -> pd.DataFrame:
        inv = self._data.get("inventory", pd.DataFrame()).copy()
        if inv.empty:
            logger.warning("'inventory' tidak ditemukan — fact_inventory kosong.")
            return pd.DataFrame()

        for col in ["store_id", "product_id", "stock_on_hand"]:
            if col in inv.columns:
                inv[col] = pd.to_numeric(inv[col], errors="coerce")

        inv = _resolve_sk(inv, dims.get("dim_product", pd.DataFrame()),
                          "product_id", "product_key")
        inv = _resolve_sk(inv, dims.get("dim_store", pd.DataFrame()),
                          "store_id", "store_key")

        wanted = ["product_key", "store_key", "stock_on_hand"]
        fact   = _select_existing(inv, wanted).copy()
        fact.insert(0, "inventory_key", range(1, len(fact) + 1))
        return fact.reset_index(drop=True)

def _resolve_sk(
    fact: pd.DataFrame,
    dim: pd.DataFrame,
    nk_col: str,
    sk_col: str,
) -> pd.DataFrame:
    if dim.empty or nk_col not in fact.columns or nk_col not in dim.columns:
        fact[sk_col] = pd.NA
        return fact

    lookup = dim[[nk_col, sk_col]].drop_duplicates(subset=[nk_col]).copy()

    fact[nk_col] = fact[nk_col].astype(str)
    lookup[nk_col] = lookup[nk_col].astype(str)

    merged = fact.merge(lookup, on=nk_col, how="left")

    if sk_col in merged.columns:
        merged[sk_col] = (
            merged[sk_col]
            .fillna(0)
            .astype(int)
        )

    return merged


def _select_existing(df: pd.DataFrame, cols: list) -> pd.DataFrame:
    present = [c for c in cols if c in df.columns]
    return df[present].copy()


def _empty(*cols: str) -> pd.DataFrame:
    return pd.DataFrame(columns=list(cols))


def _price_tier(price) -> str:
    try:
        p = float(price)
        if p < 5:    return "Budget"
        if p < 15:   return "Mid-Range"
        return "Premium"
    except (TypeError, ValueError):
        return "Unknown"


def _category_group(category: str) -> str:
    mapping = {
        "Sports & Outdoors": "Active",
        "Electronics":       "Active",
        "Art & Crafts":      "Creative",
        "Toys":              "Play",
        "Games":             "Play",
    }
    return mapping.get(str(category).strip(), "Other")
