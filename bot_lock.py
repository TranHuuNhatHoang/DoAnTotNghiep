from pathlib import Path

try:
    import msvcrt
except ImportError:
    msvcrt = None

try:
    import fcntl
except ImportError:
    fcntl = None


class FileLock:
    def __init__(self, name, stale_after_minutes=120):
        self.name = "".join(ch if ch.isalnum() or ch in {"_", "-"} else "_" for ch in name)
        self.path = Path(__file__).resolve().parent / "storage" / "bot_locks" / f"{self.name}.lock"
        self.handle = None

    def acquire(self):
        self.path.parent.mkdir(parents=True, exist_ok=True)
        self.handle = self.path.open("a+", encoding="utf-8")

        try:
            self.handle.seek(0)
            first_byte = self.handle.read(1)
        except OSError:
            self.handle.close()
            self.handle = None
            return False

        if first_byte == "":
            self.handle.write("0")
            self.handle.flush()
        self.handle.seek(0)

        if not self._lock_non_blocking():
            self.handle.close()
            self.handle = None
            return False

        self.handle.seek(0)
        self.handle.truncate()
        self.handle.write("locked\n")
        self.handle.flush()
        return True

    def release(self):
        if self.handle is None:
            return

        try:
            self.handle.seek(0)
            self.handle.truncate()
            self.handle.write("released\n")
            self.handle.flush()
        except OSError:
            pass

        try:
            self._unlock()
        finally:
            self.handle.close()
            self.handle = None

    def _lock_non_blocking(self):
        if msvcrt:
            try:
                msvcrt.locking(self.handle.fileno(), msvcrt.LK_NBLCK, 1)
                return True
            except OSError:
                return False

        if fcntl:
            try:
                fcntl.flock(self.handle.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
                return True
            except OSError:
                return False

        return True

    def _unlock(self):
        if msvcrt:
            self.handle.seek(0)
            try:
                msvcrt.locking(self.handle.fileno(), msvcrt.LK_UNLCK, 1)
            except OSError:
                pass
            return

        if fcntl:
            try:
                fcntl.flock(self.handle.fileno(), fcntl.LOCK_UN)
            except OSError:
                pass

    def is_locked(self):
        if self.handle is not None:
            return True
        if not self.acquire():
            return True
        self.release()
        return False

    def __enter__(self):
        return self.acquire()

    def __exit__(self, exc_type, exc, traceback):
        self.release()
