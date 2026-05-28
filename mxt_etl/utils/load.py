import logging
from dataclasses import dataclass, field
from typing import Dict, List, Literal

import pandas as pd
from sqlalchemy import Engine, inspect, text

logger = logging.getLogger(__name__)

LoadStrategy = Literal["replace", "upsert"]

_PK_MAP: Dict[str, str] = {
    "dim_product":    "product_key",
    "dim_store":      "store_key",
    "dim_category":   "category_key",
    "dim_date":       "date_key",
    "fact_sales":     "sales_key",
    "fact_inventory": "inventory_key",
}


@dataclass
class LoadResult:
    loaded:  List[str] = field(default_factory=list)
    skipped: List[str] = field(default_factory=list)
    failed:  List[str] = field(default_factory=list)

    @property
    def success(self) -> bool:
        return len(self.failed) == 0

    def summary(self) -> str:
        return (
            f"Loaded: {len(self.loaded)} | "
            f"Skipped: {len(self.skipped)} | "
            f"Failed: {len(self.failed)}"
        )


class DWLoader:
    def __init__(
        self,
        engine: Engine,
        schema: str = "dw",
        strategy: LoadStrategy = "replace",
        chunk_size: int = 5000,
        dry_run: bool = False,
    ) -> None:
        self.engine     = engine
        self.schema     = schema
        self.strategy   = strategy
        self.chunk_size = chunk_size
        self.dry_run    = dry_run


    def load_all(self, dw_tables: Dict[str, pd.DataFrame]) -> LoadResult:
        logger.info(
            "=== DW LOAD [strategy=%s, dry_run=%s]: %d tabel ===",
            self.strategy, self.dry_run, len(dw_tables),
        )
        result = LoadResult()

        dims  = {k: v for k, v in dw_tables.items() if k.startswith("dim_")}
        facts = {k: v for k, v in dw_tables.items() if k.startswith("fact_")}

        for name, df in {**dims, **facts}.items():
            outcome = self._load_table(name, df)
            getattr(result, outcome).append(name)

        logger.info("=== DW LOAD selesai: %s ===", result.summary())
        return result

    def load_table(self, table_name: str, df: pd.DataFrame) -> str:
        return self._load_table(table_name, df)

    def list_dw_tables(self) -> List[str]:
        prefix    = f"{self.schema}__"
        inspector = inspect(self.engine)
        return [t for t in inspector.get_table_names() if t.startswith(prefix)]

    def row_counts(self) -> Dict[str, int]:
        counts = {}
        with self.engine.connect() as conn:
            for tbl in self.list_dw_tables():
                counts[tbl] = conn.execute(
                    text(f'SELECT COUNT(*) FROM "{tbl}"')
                ).scalar()
        return counts

    def _sqlite_table(self, logical_name: str) -> str:
        return f"{self.schema}__{logical_name}"

    def _load_table(self, table_name: str, df: pd.DataFrame) -> str:
        if df.empty:
            logger.warning("Skip '%s': DataFrame kosong.", table_name)
            return "skipped"

        sqlite_table = self._sqlite_table(table_name)

        if self.dry_run:
            logger.info(
                "  [DRY RUN] Akan load '%s': %d baris", sqlite_table, len(df)
            )
            return "loaded"

        try:
            df = self._coerce_for_sqlite(df)
            if self.strategy == "replace":
                self._do_replace(sqlite_table, df)
            else:
                self._do_upsert(sqlite_table, table_name, df)
            logger.info("  Loaded '%s': %d baris", sqlite_table, len(df))
            return "loaded"
        except Exception as exc:
            logger.error("  Gagal '%s': %s", sqlite_table, exc)
            return "failed"

    def _do_replace(self, sqlite_table: str, df: pd.DataFrame) -> None:
        df.to_sql(
            name=sqlite_table,
            con=self.engine,
            if_exists="replace",
            index=False,
            chunksize=self.chunk_size,
        )

    def _do_upsert(
        self, sqlite_table: str, logical_name: str, df: pd.DataFrame
    ) -> None:
        pk_col = _PK_MAP.get(logical_name)
        if not pk_col or pk_col not in df.columns:
            logger.debug("Tidak ada PK untuk '%s'; fallback ke replace.", logical_name)
            self._do_replace(sqlite_table, df)
            return

        tmp = f"_tmp_{sqlite_table}"

        with self.engine.begin() as conn:
            self._ensure_table_with_pk(conn, sqlite_table, df, pk_col)

            df.to_sql(tmp, conn, if_exists="replace", index=False,
                      chunksize=self.chunk_size)

            cols = ", ".join(f'"{c}"' for c in df.columns)
            conn.execute(text(f"""
                INSERT OR REPLACE INTO "{sqlite_table}" ({cols})
                SELECT {cols} FROM "{tmp}";
            """))

            conn.execute(text(f'DROP TABLE IF EXISTS "{tmp}"'))

    @staticmethod
    def _ensure_table_with_pk(
        conn, table: str, df: pd.DataFrame, pk_col: str
    ) -> None:
        def _sqlite_type(dtype) -> str:
            s = str(dtype)
            if "int"   in s: return "INTEGER"
            if "float" in s: return "REAL"
            return "TEXT"

        col_defs = []
        for col in df.columns:
            t = _sqlite_type(df[col].dtype)
            if col == pk_col:
                col_defs.append(f'"{col}" {t} PRIMARY KEY')
            else:
                col_defs.append(f'"{col}" {t}')

        ddl = f'CREATE TABLE IF NOT EXISTS "{table}" ({", ".join(col_defs)})'
        conn.execute(text(ddl))

    @staticmethod
    def _coerce_for_sqlite(df: pd.DataFrame) -> pd.DataFrame:
        df = df.copy()
        for col in df.select_dtypes(include=["datetime64[ns]"]).columns:
            df[col] = df[col].dt.strftime("%Y-%m-%d")
        for col in df.select_dtypes(include=["bool"]).columns:
            df[col] = df[col].astype(int)
        for col in df.select_dtypes(include=["Int64"]).columns:
            df[col] = df[col].astype("int64")
        return df
