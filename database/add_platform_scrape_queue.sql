USE web_test;

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE platform_links ADD COLUMN next_scrape_at DATETIME NULL AFTER last_scraped_at',
    'SELECT ''next_scrape_at already exists'''
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'platform_links'
    AND COLUMN_NAME = 'next_scrape_at'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE platform_links ADD COLUMN blocked_until DATETIME NULL AFTER next_scrape_at',
    'SELECT ''blocked_until already exists'''
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'platform_links'
    AND COLUMN_NAME = 'blocked_until'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE platform_links ADD COLUMN retry_count INT NOT NULL DEFAULT 0 AFTER blocked_until',
    'SELECT ''retry_count already exists'''
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'platform_links'
    AND COLUMN_NAME = 'retry_count'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE platform_links ADD COLUMN scrape_priority TINYINT NOT NULL DEFAULT 5 AFTER retry_count',
    'SELECT ''scrape_priority already exists'''
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'platform_links'
    AND COLUMN_NAME = 'scrape_priority'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'CREATE INDEX idx_platform_scrape_queue ON platform_links (platform_name, is_active, blocked_until, next_scrape_at, scrape_priority, last_scraped_at)',
    'SELECT ''idx_platform_scrape_queue already exists'''
  )
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'platform_links'
    AND INDEX_NAME = 'idx_platform_scrape_queue'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
