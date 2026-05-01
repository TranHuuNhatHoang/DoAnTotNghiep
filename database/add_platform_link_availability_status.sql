-- Adds business-level availability tracking for platform_links.
-- Safe migration: every column/index is checked before it is added.
-- Existing price/link data is preserved.

SET @db_name = DATABASE();

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN availability_status ENUM('unknown', 'active', 'out_of_stock', 'temporarily_unavailable', 'discontinued', 'invalid_url', 'fetch_error', 'blocked_or_captcha') NOT NULL DEFAULT 'unknown' AFTER status",
        "SELECT 'availability_status already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'availability_status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN error_message VARCHAR(500) NULL AFTER availability_status",
        "SELECT 'error_message already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'error_message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN last_checked_at DATETIME NULL AFTER last_scraped_at",
        "SELECT 'last_checked_at already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'last_checked_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN next_check_at DATETIME NULL AFTER next_scrape_at",
        "SELECT 'next_check_at already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'next_check_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN consecutive_failures INT NOT NULL DEFAULT 0 AFTER retry_count",
        "SELECT 'consecutive_failures already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'consecutive_failures'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "CREATE INDEX idx_platform_link_availability ON platform_links (platform_name, is_active, availability_status, status, next_check_at)",
        "SELECT 'idx_platform_link_availability already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND INDEX_NAME = 'idx_platform_link_availability'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @duplicate_platform_links = (
    SELECT COUNT(*)
    FROM (
        SELECT product_id, platform_name
        FROM platform_links
        GROUP BY product_id, platform_name
        HAVING COUNT(*) > 1
    ) duplicate_rows
);

SET @unique_index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND INDEX_NAME = 'uq_platform_product'
);

SET @sql = IF(
    @unique_index_exists = 0 AND @duplicate_platform_links = 0,
    "ALTER TABLE platform_links ADD UNIQUE KEY uq_platform_product (product_id, platform_name)",
    "SELECT 'uq_platform_product skipped: already exists or duplicate rows need manual cleanup' AS note"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
