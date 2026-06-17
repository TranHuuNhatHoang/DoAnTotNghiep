import json
import os
import re
import time
from datetime import datetime
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parent.parent
REPORT_DIR = PROJECT_ROOT / "storage" / "crawler_reports"


def _now_text():
    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


def _file_time_text():
    return datetime.now().strftime("%Y%m%d_%H%M%S")


def _safe_text(value, limit=300):
    text = re.sub(r"\s+", " ", str(value or "")).strip()
    return text[:limit] if text else None


def _memory_snapshot():
    try:
        import psutil

        process = psutil.Process(os.getpid())
        python_memory = process.memory_info().rss / 1024 / 1024
        child_memory = 0.0
        child_count = 0
        child_names = {}

        for child in process.children(recursive=True):
            try:
                child_memory += child.memory_info().rss / 1024 / 1024
                child_count += 1
                name = child.name()
                child_names[name] = child_names.get(name, 0) + 1
            except (psutil.NoSuchProcess, psutil.AccessDenied):
                continue

        return {
            "python_process_memory_mb": round(python_memory, 2),
            "child_process_memory_mb": round(child_memory, 2),
            "total_process_tree_memory_mb": round(python_memory + child_memory, 2),
            "child_process_count": child_count,
            "child_process_names": child_names,
        }
    except Exception:
        return {
            "python_process_memory_mb": None,
            "child_process_memory_mb": None,
            "total_process_tree_memory_mb": None,
            "child_process_count": None,
            "child_process_names": {},
        }


class CrawlerRunReport:
    def __init__(self, platform, config=None):
        self.platform = platform
        self.config = config or {}
        self.started_at = _now_text()
        self.started_perf = time.perf_counter()
        self.total_candidates = 0
        self.items = []
        self.notes = []
        self.peak_memory = _memory_snapshot()

    def set_total_candidates(self, total):
        self.total_candidates = int(total or 0)

    def add_note(self, note):
        text = _safe_text(note)
        if text:
            self.notes.append(text)

    def sample_memory(self):
        snapshot = _memory_snapshot()
        current_total = snapshot.get("total_process_tree_memory_mb")
        peak_total = self.peak_memory.get("total_process_tree_memory_mb")
        if current_total is not None and (peak_total is None or current_total > peak_total):
            self.peak_memory = snapshot
        return snapshot

    def record_link(
        self,
        link_id,
        product_id=None,
        status_code=None,
        availability_status=None,
        price=None,
        elapsed_seconds=None,
        error_message=None,
    ):
        memory_snapshot = self.sample_memory()
        self.items.append(
            {
                "link_id": int(link_id) if link_id is not None else None,
                "product_id": int(product_id) if product_id is not None else None,
                "status_code": int(status_code) if status_code is not None else None,
                "status": self._status_label(status_code, availability_status),
                "availability_status": availability_status,
                "price": int(price) if price not in (None, "") else None,
                "elapsed_seconds": round(float(elapsed_seconds), 3) if elapsed_seconds is not None else None,
                "error_message": _safe_text(error_message),
                "memory": memory_snapshot,
            }
        )

    def write(self, exit_code=None):
        try:
            REPORT_DIR.mkdir(parents=True, exist_ok=True)
            ended_at = _now_text()
            total_seconds = round(time.perf_counter() - self.started_perf, 3)
            summary = self._build_summary(total_seconds)

            payload = {
                "platform": self.platform,
                "run_mode": os.getenv("CRAWLER_RUN_MODE", "manual"),
                "started_at": self.started_at,
                "ended_at": ended_at,
                "exit_code": exit_code,
                "config": self.config,
                "summary": summary,
                "notes": self.notes,
                "items": self.items,
            }

            filename = f"{self.platform.lower()}_{_file_time_text()}.json"
            report_path = REPORT_DIR / filename
            report_path.write_text(
                json.dumps(payload, ensure_ascii=False, indent=2),
                encoding="utf-8",
            )
            return report_path
        except Exception:
            return None

    def _build_summary(self, total_seconds):
        processed = len(self.items)
        success_count = sum(1 for item in self.items if item["status"] == "success")
        captcha_count = sum(1 for item in self.items if item["status"] == "captcha")
        no_price_count = sum(1 for item in self.items if item["status"] == "no_price")
        error_count = sum(1 for item in self.items if item["status"] == "error")

        availability_counts = {}
        for item in self.items:
            key = item.get("availability_status") or "unknown"
            availability_counts[key] = availability_counts.get(key, 0) + 1

        elapsed_values = [
            item["elapsed_seconds"]
            for item in self.items
            if item.get("elapsed_seconds") is not None
        ]

        final_memory = _memory_snapshot()
        return {
            "total_candidates": self.total_candidates,
            "processed_links": processed,
            "success_count": success_count,
            "failed_count": max(0, processed - success_count),
            "captcha_count": captcha_count,
            "no_price_count": no_price_count,
            "error_count": error_count,
            "availability_counts": availability_counts,
            "success_rate_percent": round((success_count / processed) * 100, 2) if processed else 0,
            "total_seconds": total_seconds,
            "avg_seconds_per_link": round(sum(elapsed_values) / len(elapsed_values), 3) if elapsed_values else 0,
            "process_memory_mb": final_memory.get("python_process_memory_mb"),
            "python_process_memory_mb": final_memory.get("python_process_memory_mb"),
            "child_process_memory_mb": final_memory.get("child_process_memory_mb"),
            "total_process_tree_memory_mb": final_memory.get("total_process_tree_memory_mb"),
            "peak_python_process_memory_mb": self.peak_memory.get("python_process_memory_mb"),
            "peak_child_process_memory_mb": self.peak_memory.get("child_process_memory_mb"),
            "peak_total_process_tree_memory_mb": self.peak_memory.get("total_process_tree_memory_mb"),
            "peak_child_process_count": self.peak_memory.get("child_process_count"),
            "peak_child_process_names": self.peak_memory.get("child_process_names"),
            "memory_scope": "current_python_process_and_child_processes_only",
        }

    @staticmethod
    def _status_label(status_code, availability_status):
        if availability_status == "blocked_or_captcha":
            return "captcha"
        try:
            code = int(status_code)
        except (TypeError, ValueError):
            return "unknown"

        if code == 1:
            return "success"
        if code == 2:
            return "no_price"
        if code == 3:
            return "error"
        if code == 4:
            return "captcha"
        return "unknown"
