-- Adds product duplicate detection metadata.
-- Safe migration: columns and indexes are checked before being created.
-- Unique indexes are only created when current data has no duplicate non-null keys.

SET @db_name = DATABASE();

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE products ADD COLUMN normalized_name VARCHAR(500) NULL AFTER name",
        "SELECT 'products.normalized_name already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'products'
      AND COLUMN_NAME = 'normalized_name'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN platform_product_id VARCHAR(120) NULL AFTER product_url",
        "SELECT 'platform_links.platform_product_id already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'platform_product_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN normalized_url TEXT NULL AFTER platform_product_id",
        "SELECT 'platform_links.normalized_url already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'normalized_url'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "ALTER TABLE platform_links ADD COLUMN url_hash CHAR(40) NULL AFTER normalized_url",
        "SELECT 'platform_links.url_hash already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND COLUMN_NAME = 'url_hash'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS product_duplicate_overrides (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_user_id INT NULL,
  product_id INT NULL,
  product_name VARCHAR(500) NOT NULL,
  normalized_name VARCHAR(500) NULL,
  candidate_product_ids TEXT NULL,
  reason VARCHAR(255) NOT NULL DEFAULT 'force_create_after_name_warning',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_duplicate_overrides_product (product_id),
  KEY idx_duplicate_overrides_admin_time (admin_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "CREATE INDEX idx_products_normalized_name ON products (normalized_name)",
        "SELECT 'idx_products_normalized_name already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'products'
      AND INDEX_NAME = 'idx_products_normalized_name'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "CREATE INDEX idx_platform_product_id ON platform_links (platform_name, platform_product_id)",
        "SELECT 'idx_platform_product_id already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND INDEX_NAME = 'idx_platform_product_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        "CREATE INDEX idx_platform_url_hash ON platform_links (platform_name, url_hash)",
        "SELECT 'idx_platform_url_hash already exists' AS note"
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND INDEX_NAME = 'idx_platform_url_hash'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @duplicate_platform_product_id = (
    SELECT COUNT(*)
    FROM (
        SELECT platform_name, platform_product_id
        FROM platform_links
        WHERE platform_product_id IS NOT NULL AND platform_product_id <> ''
        GROUP BY platform_name, platform_product_id
        HAVING COUNT(*) > 1
    ) duplicate_rows
);

SET @unique_product_id_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND INDEX_NAME = 'uq_platform_product_id'
);

SET @sql = IF(
    @unique_product_id_exists = 0 AND @duplicate_platform_product_id = 0,
    "ALTER TABLE platform_links ADD UNIQUE KEY uq_platform_product_id (platform_name, platform_product_id)",
    "SELECT 'uq_platform_product_id skipped: already exists or duplicate rows need manual cleanup' AS note"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @duplicate_url_hash = (
    SELECT COUNT(*)
    FROM (
        SELECT platform_name, url_hash
        FROM platform_links
        WHERE url_hash IS NOT NULL AND url_hash <> ''
        GROUP BY platform_name, url_hash
        HAVING COUNT(*) > 1
    ) duplicate_rows
);

SET @unique_url_hash_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'platform_links'
      AND INDEX_NAME = 'uq_platform_url_hash'
);

SET @sql = IF(
    @unique_url_hash_exists = 0 AND @duplicate_url_hash = 0,
    "ALTER TABLE platform_links ADD UNIQUE KEY uq_platform_url_hash (platform_name, url_hash)",
    "SELECT 'uq_platform_url_hash skipped: already exists or duplicate rows need manual cleanup' AS note"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
