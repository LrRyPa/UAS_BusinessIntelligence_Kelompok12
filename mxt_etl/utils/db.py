import logging
import os
from pathlib import Path

from dotenv import load_dotenv
from sqlalchemy import Engine, create_engine, text

load_dotenv()

logger = logging.getLogger(__name__)


def get_engine(echo: bool = False) -> Engine:
    db_path = os.getenv("SQLITE_DB_PATH", "./mexican_toys_dw.db")

    Path(db_path).parent.mkdir(parents=True, exist_ok=True)

    url = f"sqlite:///{db_path}"
    engine = create_engine(
        url,
        echo=echo,
        connect_args={"check_same_thread": False},
    )

    with engine.connect() as conn:
        conn.execute(text("PRAGMA journal_mode=WAL"))
        conn.execute(text("PRAGMA foreign_keys=ON"))

    logger.info("Berhasil terhubung ke: %s", Path(db_path).resolve())
    return engine
