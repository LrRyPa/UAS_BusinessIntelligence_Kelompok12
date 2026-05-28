import argparse
import logging
import os
import sys
from pathlib import Path

from dotenv import load_dotenv

load_dotenv()

LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO").upper()
LOG_FILE  = os.getenv("LOG_FILE",  "etl_pipeline.log")

logging.basicConfig(
    level=getattr(logging, LOG_LEVEL, logging.INFO),
    format="%(asctime)s  %(levelname)-8s  %(name)s — %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler(LOG_FILE, encoding="utf-8"),
    ],
)
logger = logging.getLogger("etl.main")

from utils.db        import get_engine
from utils.extract   import DataExtractor
from utils.staging   import StagingLoader
from utils.transform import DataWarehouseTransformer
from utils.load      import DWLoader


def run_pipeline(
    dataset_folder: str | Path = "./datasets",
    staging_schema: str = "staging",
    dw_schema: str = "dw",
    load_strategy: str = "replace",
    skip_staging: bool = False,
    dry_run: bool = False,
) -> bool:

    _banner("Mexican Toys Sales ETL Pipeline — starting")

    engine = None if dry_run else get_engine()

    _section("STAGE 1 — EXTRACT")
    try:
        extractor = DataExtractor(
            source_folder=dataset_folder,
            skip_files=["data_dictionary.csv"], 
        )
        extract_result = extractor.extract_all()
    except FileNotFoundError as exc:
        logger.error("Error: %s", exc)
        return False

    if not extract_result.datasets:
        logger.error("Tidak ada data yang ter-extract.")
        return False

    if extract_result.new_files:
        logger.info("File baru terdeteksi: %s", extract_result.new_files)

    if dry_run or skip_staging:
        _section("STAGE 2 — STAGING (dilewati)")
    else:
        _section("STAGE 2 — STAGING")
        stager = StagingLoader(
            engine=engine,
            schema=staging_schema,
            if_exists="replace",
            chunk_size=5000,
        )
        stager.load_all(extract_result.datasets)

    _section("STAGE 3 — TRANSFORM")
    transformer = DataWarehouseTransformer(
        datasets=extract_result.datasets,
        pre_clean=True,
    )
    dw_tables = transformer.transform()

    _section("STAGE 4 — LOAD")
    loader = DWLoader(
        engine=engine,
        schema=dw_schema,
        strategy=load_strategy,
        chunk_size=5000,
        dry_run=dry_run,
    )
    result = loader.load_all(dw_tables)

    if result.success:
        _banner("Pipeline selesai")
        if not dry_run:
            _print_summary(loader)
    else:
        logger.error("Pipeline selesai dengan kegagalan: %s", result.failed)

    return result.success


def _print_summary(loader: DWLoader) -> None:
    counts = loader.row_counts()
    logger.info("── DW Table Summary %s", "─" * 40)
    for tbl, cnt in sorted(counts.items()):
        logger.info("  %-40s %8d baris", tbl, cnt)
    logger.info("─" * 60)



def _parse_args() -> argparse.Namespace:
    p = argparse.ArgumentParser(
        description="Mexican Toys Sales ETL Pipeline (SQLite)"
    )
    p.add_argument(
        "--dataset-folder",
        default=os.getenv("DATASET_FOLDER", "./datasets"),
        help="Folder berisi file CSV sumber.",
    )
    p.add_argument(
        "--staging-schema",
        default=os.getenv("STAGING_SCHEMA", "staging"),
    )
    p.add_argument(
        "--dw-schema",
        default=os.getenv("DW_SCHEMA", "dw"),
    )
    p.add_argument(
        "--strategy",
        default="replace",
        choices=["replace", "upsert"],
        help="Strategi load: 'replace' (default) atau 'upsert'.",
    )
    p.add_argument(
        "--skip-staging",
        action="store_true",
        help="Lewati penulisan staging ke DB.",
    )
    p.add_argument(
        "--dry-run",
        action="store_true",
        help="Jalankan extract + transform saja, tanpa DB write.",
    )
    p.add_argument(
        "--db",
        default=None,
        help="Override SQLITE_DB_PATH dari .env.",
    )
    return p.parse_args()


def _banner(msg: str) -> None:
    border = "═" * (len(msg) + 4)
    logger.info("╔%s╗", border)
    logger.info("║  %s  ║", msg)
    logger.info("╚%s╝", border)


def _section(msg: str) -> None:
    logger.info("")
    logger.info("┌─ %s %s", msg, "─" * max(0, 55 - len(msg)))


if __name__ == "__main__":
    args = _parse_args()

    if args.db:
        os.environ["SQLITE_DB_PATH"] = args.db

    ok = run_pipeline(
        dataset_folder=args.dataset_folder,
        staging_schema=args.staging_schema,
        dw_schema=args.dw_schema,
        load_strategy=args.strategy,
        skip_staging=args.skip_staging,
        dry_run=args.dry_run,
    )
    sys.exit(0 if ok else 1)
