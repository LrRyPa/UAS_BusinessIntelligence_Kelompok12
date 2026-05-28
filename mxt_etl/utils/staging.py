import logging
import re
from typing import Dict, List, Optional

import pandas as pd
from sqlalchemy import Engine, inspect, text

logger = logging.getLogger(__name__)


class StagingCleaner:
    _CURRENCY_HINTS: set = {"cost", "price", "sales", "revenue", "amount", "target"}

    _DATE_HINTS: set = {"date", "month", "year", "open_date"}

    def clean(self, df: pd.DataFrame, table_name: str = "") -> pd.DataFrame:
        df = df.copy()
        df = self._strip_strings(df)
        df = self._replace_null_strings(df)
        df = self._parse_currencies(df)
        df = self._parse_dates(df)
        logger.debug("  Cleaned '%s': %d baris", table_name, len(df))
        return df

    @staticmethod
    def _strip_strings(df: pd.DataFrame) -> pd.DataFrame:
        for col in df.select_dtypes(include=["object", "string"]).columns:
            df[col] = df[col].astype(str).str.strip()
        return df

    @staticmethod
    def _replace_null_strings(df: pd.DataFrame) -> pd.DataFrame:
        null_vals = {"nan", "none", "null", "n/a", "na", ""}
        for col in df.select_dtypes(include=["object", "string"]).columns:
            mask = df[col].str.lower().isin(null_vals)
            df.loc[mask, col] = pd.NA
        return df

    def _parse_currencies(self, df: pd.DataFrame) -> pd.DataFrame:
        for col in df.select_dtypes(include=["object", "string"]).columns:
            if any(hint in col.lower() for hint in self._CURRENCY_HINTS):
                cleaned = df[col].astype(str).str.replace(r"[\$,\s]", "", regex=True)
                numeric = pd.to_numeric(cleaned, errors="coerce")
                if numeric.notna().sum() / max(len(df), 1) > 0.5:
                    df[col] = numeric
        return df

    def _parse_dates(self, df: pd.DataFrame) -> pd.DataFrame:
        for col in df.select_dtypes(include=["object", "string"]).columns:
            if any(hint in col.lower() for hint in self._DATE_HINTS):
                cleaned = df[col].astype(str).str.replace(
                    r"^[A-Za-z]+,\s*", "", regex=True
                )
                parsed = pd.to_datetime(cleaned, errors="coerce", dayfirst=False)
                if parsed.notna().sum() / max(len(df), 1) > 0.5:
                    df[col] = parsed
        return df


    @staticmethod
    def _infer_numerics(df: pd.DataFrame) -> pd.DataFrame:
        for col in df.select_dtypes(include=["object", "string"]).columns:
            numeric = pd.to_numeric(df[col], errors="coerce")
            if numeric.notna().sum() / max(len(df), 1) > 0.8:
                df[col] = numeric
        return df


class StagingLoader:
    def __init__(
        self,
        engine: Engine,
        schema: str = "staging",
        if_exists: str = "replace",
        cleaner: Optional[StagingCleaner] = None,
        chunk_size: int = 5000,
    ) -> None:
        self.engine     = engine
        self.schema     = schema
        self.if_exists  = if_exists
        self.cleaner    = cleaner or StagingCleaner()
        self.chunk_size = chunk_size

    def load_all(self, datasets: Dict[str, pd.DataFrame]) -> None:
        logger.info("=== STAGING: memuat %d tabel ===", len(datasets))
        success, failed = 0, 0
        for name, df in datasets.items():
            try:
                self._load_table(name, df)
                success += 1
            except Exception:
                failed += 1
        logger.info(
            "=== STAGING selesai: %d berhasil, %d gagal ===", success, failed
        )

    def load_table(self, table_name: str, df: pd.DataFrame) -> None:
        self._load_table(table_name, df)

    def list_tables(self) -> List[str]:
        prefix    = f"{self.schema}__"
        inspector = inspect(self.engine)
        return [t for t in inspector.get_table_names() if t.startswith(prefix)]

    def _load_table(self, table_name: str, df: pd.DataFrame) -> None:
        sqlite_table = f"{self.schema}__{table_name}"
        try:
            clean_df = self.cleaner.clean(df, table_name)
            clean_df = self._coerce_for_sqlite(clean_df)

            clean_df.to_sql(
                name=sqlite_table,
                con=self.engine,
                if_exists=self.if_exists,
                index=False,
                chunksize=self.chunk_size,
            )
            logger.info(
                "  ✔ Staged '%s': %d baris", sqlite_table, len(clean_df)
            )
        except Exception as exc:
            logger.error("  ✘ Gagal staging '%s': %s", sqlite_table, exc)
            raise

    @staticmethod
    def _coerce_for_sqlite(df: pd.DataFrame) -> pd.DataFrame:
        df = df.copy()
        for col in df.select_dtypes(include=["datetime64[ns]"]).columns:
            df[col] = df[col].dt.strftime("%Y-%m-%d")
            
        for col in df.select_dtypes(include=["bool"]).columns:
            df[col] = df[col].astype(int)

        for col in df.select_dtypes(include=["Int64"]).columns:
            df[col] = df[col].astype("float64")
        return df
