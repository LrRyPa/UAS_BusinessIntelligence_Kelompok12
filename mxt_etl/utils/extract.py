import csv
import json
import logging
import re
from dataclasses import dataclass, field
from datetime import datetime, timezone
from pathlib import Path
from typing import Dict, List, Optional

import pandas as pd

logger = logging.getLogger(__name__)

_MANIFEST_NAME = ".extracted_manifest.json"


@dataclass
class ExtractResult:
    datasets:     Dict[str, pd.DataFrame]
    new_files:    List[str] = field(default_factory=list)
    all_files:    List[str] = field(default_factory=list)
    extracted_at: str = field(
        default_factory=lambda: datetime.now(timezone.utc).isoformat()
    )


class DataExtractor:
    def __init__(
        self,
        source_folder: str | Path,
        encoding: str = "utf-8-sig",
        extensions: tuple = (".csv", ".tsv"),
        track_new: bool = True,
        skip_files: Optional[List[str]] = None,
    ) -> None:
        self.source_folder  = Path(source_folder)
        self.encoding       = encoding
        self.extensions     = extensions
        self.track_new      = track_new
        self.skip_files     = {f.lower() for f in (skip_files or [])}
        self._manifest_path = self.source_folder / _MANIFEST_NAME

        if not self.source_folder.exists():
            raise FileNotFoundError(
                f"Source folder tidak ditemukan: {self.source_folder.resolve()}"
            )


    def extract_all(self) -> ExtractResult:
        data_files = sorted(
            p for p in self.source_folder.iterdir()
            if p.suffix.lower() in self.extensions
            and p.name.lower() not in self.skip_files
            and not p.name.startswith(".")
        )

        if not data_files:
            logger.warning("Tidak ada file data di '%s'", self.source_folder)
            return ExtractResult(datasets={})

        logger.info(
            "Ditemukan %d file di '%s'", len(data_files), self.source_folder
        )

        previous  = self._load_manifest() if self.track_new else set()
        datasets: Dict[str, pd.DataFrame] = {}
        new_files: List[str] = []

        for path in data_files:
            table_name = self._to_table_name(path.stem)
            df         = self._read_file(path)
            if df is not None:
                datasets[table_name] = df
                logger.info(
                    "  ✔ Extracted '%-20s' : %7d baris × %d kolom",
                    table_name, len(df), len(df.columns),
                )
                if path.name not in previous:
                    new_files.append(path.name)

        if self.track_new:
            self._save_manifest({p.name for p in data_files})

        return ExtractResult(
            datasets=datasets,
            new_files=new_files,
            all_files=[p.name for p in data_files],
        )

    def extract_file(self, filename: str) -> Optional[pd.DataFrame]:
        path = self.source_folder / filename
        if not path.suffix:
            for ext in self.extensions:
                candidate = path.with_suffix(ext)
                if candidate.exists():
                    path = candidate
                    break
        return self._read_file(path)


    def _read_file(self, path: Path) -> Optional[pd.DataFrame]:
        if not path.exists():
            logger.error("File tidak ditemukan: %s", path)
            return None

        sep      = self._sniff_separator(path)
        encoding = self._sniff_encoding(path)

        try:
            df = pd.read_csv(
                path,
                sep=sep,
                encoding=encoding,
                low_memory=False,
                dtype=str,           
                keep_default_na=False,
            )
            df.columns = [self._normalise_col(c) for c in df.columns]
            return df
        except Exception as exc:
            logger.error("Gagal membaca '%s': %s", path.name, exc)
            return None

    @staticmethod
    def _sniff_separator(path: Path, sample_bytes: int = 8192) -> str:
        for enc in ("utf-8-sig", "utf-8", "latin-1"):
            try:
                with path.open(encoding=enc, errors="replace") as fh:
                    sample = fh.read(sample_bytes)
                dialect = csv.Sniffer().sniff(sample, delimiters=",\t|;")
                return dialect.delimiter
            except (csv.Error, UnicodeDecodeError):
                continue
        return ","

    @staticmethod
    def _sniff_encoding(path: Path) -> str:
        for enc in ("utf-8-sig", "utf-8", "latin-1"):
            try:
                with path.open(encoding=enc) as fh:
                    fh.read(4096)
                return enc
            except UnicodeDecodeError:
                continue
        return "latin-1"

    @staticmethod
    def _normalise_col(name: str) -> str:
        name = name.strip()
        name = re.sub(r"[\s\-]+", "_", name)   
        name = re.sub(r"[^\w]",   "",  name)   
        return name.lower()

    @staticmethod
    def _to_table_name(stem: str) -> str:
        name = re.sub(r"(?<=[a-z])(?=[A-Z])", "_", stem)
        return name.lower()


    def _load_manifest(self) -> set:
        if self._manifest_path.exists():
            try:
                data = json.loads(self._manifest_path.read_text())
                return set(data.get("files", []))
            except Exception:
                pass
        return set()

    def _save_manifest(self, files: set) -> None:
        try:
            self._manifest_path.write_text(
                json.dumps(
                    {
                        "files":      sorted(files),
                        "updated_at": datetime.now(timezone.utc).isoformat(),
                    },
                    indent=2,
                )
            )
        except Exception as exc:
            logger.warning("Tidak bisa tulis manifest: %s", exc)
