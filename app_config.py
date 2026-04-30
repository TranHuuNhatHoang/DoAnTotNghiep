import os
from pathlib import Path


_ENV_LOADED = False


def load_env():
    global _ENV_LOADED
    if _ENV_LOADED:
        return

    env_path = Path(__file__).resolve().parent / ".env"
    if env_path.is_file():
        for raw_line in env_path.read_text(encoding="utf-8").splitlines():
            line = raw_line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue

            key, value = line.split("=", 1)
            key = key.strip()
            value = value.strip().strip('"').strip("'")
            if key and key not in os.environ:
                os.environ[key] = value

    _ENV_LOADED = True


def get_db_config():
    load_env()
    return {
        "host": os.getenv("DB_HOST", "127.0.0.1"),
        "port": int(os.getenv("DB_PORT", "3307")),
        "user": os.getenv("DB_USER", "root"),
        "password": os.getenv("DB_PASS", ""),
        "database": os.getenv("DB_NAME", "web_test"),
    }


def get_chrome_version_main(default=147):
    load_env()
    value = os.getenv("CHROME_VERSION_MAIN")
    return int(value) if value else default


def get_profile_path(default_name="master_profile"):
    load_env()
    configured_path = os.getenv("CHROME_PROFILE_PATH", "").strip()
    if configured_path:
        return str(Path(configured_path).expanduser().resolve())
    return str(Path(__file__).resolve().parent / default_name)
