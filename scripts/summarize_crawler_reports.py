import argparse
import json
from collections import defaultdict
from pathlib import Path


PROJECT_ROOT = Path(__file__).resolve().parent.parent
REPORT_DIR = PROJECT_ROOT / "storage" / "crawler_reports"


LEGACY_MANUAL_EOF = "EOF when reading a line"


def load_reports(excluded_reports=None):
    excluded_reports = set(excluded_reports or [])
    if not REPORT_DIR.exists():
        return []

    reports = []
    for path in sorted(REPORT_DIR.glob("*.json")):
        if path.name in excluded_reports:
            continue
        try:
            reports.append(json.loads(path.read_text(encoding="utf-8")))
        except Exception:
            continue
    return reports


def rebuild_summary_from_items(report, items):
    summary = dict(report.get("summary") or {})
    processed = len(items)
    success_count = sum(1 for item in items if item.get("status") == "success")
    captcha_count = sum(1 for item in items if item.get("status") == "captcha")
    no_price_count = sum(1 for item in items if item.get("status") == "no_price")
    error_count = sum(1 for item in items if item.get("status") == "error")
    availability_counts = defaultdict(int)
    elapsed_values = []

    for item in items:
        availability_counts[item.get("availability_status") or "unknown"] += 1
        if item.get("elapsed_seconds") is not None:
            elapsed_values.append(float(item["elapsed_seconds"]))

    summary.update(
        {
            "processed_links": processed,
            "success_count": success_count,
            "failed_count": max(0, processed - success_count),
            "captcha_count": captcha_count,
            "no_price_count": no_price_count,
            "error_count": error_count,
            "availability_counts": dict(availability_counts),
            "success_rate_percent": round((success_count / processed) * 100, 2) if processed else 0,
            "avg_seconds_per_link": round(sum(elapsed_values) / len(elapsed_values), 3) if elapsed_values else 0,
            "total_seconds": round(sum(elapsed_values), 3) if elapsed_values else 0,
        }
    )
    return summary


def clean_legacy_eof_items(reports):
    cleaned = []
    for report in reports:
        items = report.get("items") or []
        filtered_items = [
            item
            for item in items
            if str(item.get("error_message") or "") != LEGACY_MANUAL_EOF
        ]
        if len(filtered_items) == len(items):
            cleaned.append(report)
            continue

        filtered_report = dict(report)
        notes = list(filtered_report.get("notes") or [])
        notes.append(f"excluded_legacy_manual_eof_items={len(items) - len(filtered_items)}")
        filtered_report["items"] = filtered_items
        filtered_report["notes"] = notes
        filtered_report["summary"] = rebuild_summary_from_items(filtered_report, filtered_items)
        cleaned.append(filtered_report)
    return cleaned


def fmt(value):
    if value is None:
        return "-"
    if isinstance(value, float):
        return f"{value:.2f}"
    return str(value)


def print_markdown_table(headers, rows):
    print("| " + " | ".join(headers) + " |")
    print("| " + " | ".join(["---"] * len(headers)) + " |")
    for row in rows:
        print("| " + " | ".join(fmt(cell) for cell in row) + " |")


def aggregate_reports(reports):
    grouped = defaultdict(
        lambda: {
            "runs": 0,
            "processed": 0,
            "success": 0,
            "failed": 0,
            "captcha": 0,
            "no_price": 0,
            "error": 0,
            "total_seconds": 0.0,
            "avg_weighted_seconds": 0.0,
            "python_memory_values": [],
            "tree_memory_values": [],
            "child_memory_values": [],
            "availability": defaultdict(int),
        }
    )

    for report in reports:
        platform = report.get("platform") or "Unknown"
        summary = report.get("summary") or {}
        row = grouped[platform]
        processed = int(summary.get("processed_links") or 0)

        row["runs"] += 1
        row["processed"] += processed
        row["success"] += int(summary.get("success_count") or 0)
        row["failed"] += int(summary.get("failed_count") or 0)
        row["captcha"] += int(summary.get("captcha_count") or 0)
        row["no_price"] += int(summary.get("no_price_count") or 0)
        row["error"] += int(summary.get("error_count") or 0)
        row["total_seconds"] += float(summary.get("total_seconds") or 0)
        row["avg_weighted_seconds"] += float(summary.get("avg_seconds_per_link") or 0) * processed

        has_tree_memory = summary.get("peak_total_process_tree_memory_mb") is not None
        python_memory = summary.get("peak_python_process_memory_mb") if has_tree_memory else None
        child_memory = summary.get("peak_child_process_memory_mb")
        tree_memory = summary.get("peak_total_process_tree_memory_mb")
        if python_memory is not None:
            row["python_memory_values"].append(float(python_memory))
        if child_memory is not None:
            row["child_memory_values"].append(float(child_memory))
        if tree_memory is not None:
            row["tree_memory_values"].append(float(tree_memory))

        for key, value in (summary.get("availability_counts") or {}).items():
            row["availability"][key] += int(value or 0)

    return grouped


def parse_args():
    parser = argparse.ArgumentParser(description="Tong hop report crawler thanh bang Markdown.")
    parser.add_argument(
        "--exclude-legacy-eof",
        action="store_true",
        help="Loai cac item loi cu 'EOF when reading a line' do web UI khong co terminal tuong tac.",
    )
    parser.add_argument(
        "--exclude-report",
        action="append",
        default=[],
        help="Bo qua mot file report theo ten file, co the dung nhieu lan.",
    )
    return parser.parse_args()


def main():
    args = parse_args()
    reports = load_reports(args.exclude_report)
    if args.exclude_legacy_eof:
        reports = clean_legacy_eof_items(reports)

    if not reports:
        print(f"Chua co report trong: {REPORT_DIR}")
        return

    grouped = aggregate_reports(reports)

    crawler_rows = []
    performance_rows = []
    status_rows = []

    for platform, row in sorted(grouped.items()):
        processed = row["processed"]
        success_rate = round((row["success"] / processed) * 100, 2) if processed else 0
        avg_seconds = round(row["avg_weighted_seconds"] / processed, 2) if processed else 0
        avg_python_memory = round(sum(row["python_memory_values"]) / len(row["python_memory_values"]), 2) if row["python_memory_values"] else None
        avg_child_memory = round(sum(row["child_memory_values"]) / len(row["child_memory_values"]), 2) if row["child_memory_values"] else None
        avg_tree_memory = round(sum(row["tree_memory_values"]) / len(row["tree_memory_values"]), 2) if row["tree_memory_values"] else None
        availability = row["availability"]

        crawler_rows.append(
            [
                platform,
                processed,
                row["success"],
                f"{success_rate}%",
                row["failed"],
                row["captcha"],
            ]
        )

        performance_rows.append(
            [
                platform,
                row["runs"],
                avg_seconds,
                round(row["total_seconds"], 2),
                avg_python_memory,
                avg_child_memory,
                avg_tree_memory,
            ]
        )

        status_rows.append(
            [
                platform,
                availability.get("active", 0),
                availability.get("out_of_stock", 0),
                availability.get("temporarily_unavailable", 0),
                availability.get("discontinued", 0),
                availability.get("fetch_error", 0),
                availability.get("blocked_or_captcha", 0),
            ]
        )

    print("\nBang danh gia crawler")
    print_markdown_table(
        ["San", "Link xu ly", "Thanh cong", "Ty le", "That bai", "Captcha/block"],
        crawler_rows,
    )

    print("\nBang danh gia hieu nang")
    print_markdown_table(
        ["San", "So lan chay", "Giay/link TB", "Tong giay", "RAM Python MB", "RAM Selenium/Chrome MB", "RAM tong process tree MB"],
        performance_rows,
    )

    print("\nBang trang thai du lieu thu thap")
    print_markdown_table(
        ["San", "Active", "Het hang", "Tam ngung", "Ngung ban", "Loi fetch", "Captcha/block"],
        status_rows,
    )


if __name__ == "__main__":
    main()
