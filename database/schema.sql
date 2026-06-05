CREATE DATABASE IF NOT EXISTS web_test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE web_test;

CREATE TABLE IF NOT EXISTS categories (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  icon VARCHAR(50) DEFAULT 'fas fa-box',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  normalized_name VARCHAR(500) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  category_id INT(11) DEFAULT NULL,
  thumbnail_url VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_product_category (category_id),
  KEY idx_products_normalized_name (normalized_name),
  CONSTRAINT fk_product_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS product_specifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  product_id INT(11) NOT NULL,
  group_name VARCHAR(255) NOT NULL DEFAULT 'Thông tin sản phẩm',
  spec_name VARCHAR(255) NOT NULL,
  spec_value TEXT NOT NULL,
  display_order INT(11) NOT NULL DEFAULT 0,
  source_platform ENUM('Tiki', 'Shopee', 'Lazada', 'Manual') NOT NULL DEFAULT 'Manual',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_product_specs_product_order (product_id, display_order, id),
  KEY idx_product_specs_name (spec_name),
  CONSTRAINT fk_product_specs_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  otp_code_hash CHAR(64) DEFAULT NULL,
  otp_expires_at TIMESTAMP NULL DEFAULT NULL,
  reset_token_hash CHAR(64) DEFAULT NULL,
  reset_token_expires_at DATETIME DEFAULT NULL,
  reset_token_used_at DATETIME DEFAULT NULL,
  is_verified TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  KEY idx_users_otp_code_hash (otp_code_hash),
  KEY idx_users_role_verified_active (role, is_verified, is_active),
  KEY idx_users_reset_token_hash (reset_token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS platform_links (
  id INT(11) NOT NULL AUTO_INCREMENT,
  product_id INT(11) NOT NULL,
  platform_name VARCHAR(50) NOT NULL,
  product_url TEXT NOT NULL,
  platform_product_id VARCHAR(120) DEFAULT NULL,
  normalized_url TEXT DEFAULT NULL,
  url_hash CHAR(40) DEFAULT NULL,
  platform_sku VARCHAR(100) DEFAULT NULL,
  current_price BIGINT(20) NOT NULL,
  original_price BIGINT(20) DEFAULT NULL,
  historical_sold INT(11) DEFAULT 0,
  rating_average DECIMAL(2,1) DEFAULT 0.0,
  review_count INT(11) DEFAULT 0,
  match_score TINYINT(4) DEFAULT 100 COMMENT 'Match confidence score from 0 to 100',
  status TINYINT(4) DEFAULT 0 COMMENT '0=pending, 1=success, 2=link_error, 3=blocked',
  availability_status ENUM(
    'unknown',
    'active',
    'out_of_stock',
    'temporarily_unavailable',
    'discontinued',
    'invalid_url',
    'fetch_error',
    'blocked_or_captcha'
  ) NOT NULL DEFAULT 'unknown',
  error_message VARCHAR(500) DEFAULT NULL,
  last_scraped_at TIMESTAMP NULL DEFAULT NULL,
  last_checked_at DATETIME DEFAULT NULL,
  next_scrape_at DATETIME DEFAULT NULL,
  next_check_at DATETIME DEFAULT NULL,
  blocked_until DATETIME DEFAULT NULL,
  retry_count INT(11) NOT NULL DEFAULT 0,
  consecutive_failures INT(11) NOT NULL DEFAULT 0,
  scrape_priority TINYINT(4) NOT NULL DEFAULT 5,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_platform_product (product_id, platform_name),
  UNIQUE KEY uq_platform_product_id (platform_name, platform_product_id),
  UNIQUE KEY uq_platform_url_hash (platform_name, url_hash),
  KEY idx_platform_scrape_queue (
    platform_name,
    is_active,
    blocked_until,
    next_scrape_at,
    scrape_priority,
    last_scraped_at
  ),
  KEY idx_platform_link_availability (
    platform_name,
    is_active,
    availability_status,
    status,
    next_check_at
  ),
  KEY idx_platform_product_id (platform_name, platform_product_id),
  KEY idx_platform_url_hash (platform_name, url_hash),
  CONSTRAINT platform_links_ibfk_1
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS price_history (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  link_id INT(11) NOT NULL,
  price BIGINT(20) NOT NULL,
  scraped_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_price_history_link_id (link_id),
  KEY idx_price_history_scraped_at (scraped_at),
  CONSTRAINT price_history_ibfk_1
    FOREIGN KEY (link_id) REFERENCES platform_links(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS price_alerts (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  product_id INT(11) NOT NULL,
  target_price BIGINT(20) NOT NULL,
  is_notified TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY product_id (product_id),
  CONSTRAINT price_alerts_ibfk_1
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT price_alerts_ibfk_2
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  product_id INT(11) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0 COMMENT '0=unread, 1=read',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY product_id (product_id),
  CONSTRAINT notifications_ibfk_1
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT notifications_ibfk_2
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS product_duplicate_overrides (
  id INT(11) NOT NULL AUTO_INCREMENT,
  admin_user_id INT(11) DEFAULT NULL,
  product_id INT(11) DEFAULT NULL,
  product_name VARCHAR(500) NOT NULL,
  normalized_name VARCHAR(500) DEFAULT NULL,
  candidate_product_ids TEXT DEFAULT NULL,
  reason VARCHAR(255) NOT NULL DEFAULT 'force_create_after_name_warning',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_duplicate_overrides_product (product_id),
  KEY idx_duplicate_overrides_admin_time (admin_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
