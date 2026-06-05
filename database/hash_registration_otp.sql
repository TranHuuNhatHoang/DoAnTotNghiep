-- Stores registration OTP values as SHA-256 hashes instead of plain text.
-- Safe migration: adds otp_code_hash when needed, backfills from otp_code,
-- then removes the old plain-text otp_code column.

SET @db_name = DATABASE();

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE users ADD COLUMN otp_code_hash CHAR(64) NULL AFTER role",
        "SELECT 'users.otp_code_hash already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'otp_code_hash'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_otp_code = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'otp_code'
);

SET @has_otp_code_hash = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'otp_code_hash'
);

SET @sql = IF(
    @has_otp_code > 0 AND @has_otp_code_hash > 0,
    "UPDATE users SET otp_code_hash = SHA2(otp_code, 256) WHERE otp_code IS NOT NULL AND otp_code <> '' AND otp_code_hash IS NULL",
    "SELECT 'otp_code backfill skipped' AS note"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'users'
      AND INDEX_NAME = 'idx_users_otp_code_hash'
);

SET @sql = IF(
    @index_exists = 0,
    "CREATE INDEX idx_users_otp_code_hash ON users (otp_code_hash)",
    "SELECT 'idx_users_otp_code_hash already exists' AS note"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_otp_code = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'otp_code'
);

SET @sql = IF(
    @has_otp_code > 0,
    "ALTER TABLE users DROP COLUMN otp_code",
    "SELECT 'users.otp_code already removed' AS note"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
