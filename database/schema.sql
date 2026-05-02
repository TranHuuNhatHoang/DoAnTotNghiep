CREATE DATABASE IF NOT EXISTS web_test
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE web_test;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  icon VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(500) NOT NULL,
  normalized_name VARCHAR(500) NULL,
  description TEXT NULL,
  category_id INT NULL,
  thumbnail_url TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_products_normalized_name (normalized_name),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_specifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  group_name VARCHAR(255) NOT NULL DEFAULT 'Thông tin sản phẩm',
  spec_name VARCHAR(255) NOT NULL,
  spec_value TEXT NOT NULL,
  display_order INT NOT NULL DEFAULT 0,
  source_platform ENUM('Tiki', 'Shopee', 'Lazada', 'Manual') NOT NULL DEFAULT 'Manual',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_product_specs_product_order (product_id, display_order, id),
  KEY idx_product_specs_name (spec_name),
  CONSTRAINT fk_product_specs_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  otp_code VARCHAR(10) NULL,
  otp_expires_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS platform_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  platform_name ENUM('Tiki', 'Shopee', 'Lazada') NOT NULL,
  product_url TEXT NOT NULL,
  platform_product_id VARCHAR(120) NULL,
  normalized_url TEXT NULL,
  url_hash CHAR(40) NULL,
  current_price INT NOT NULL DEFAULT 0,
  original_price INT NULL,
  historical_sold INT NOT NULL DEFAULT 0,
  rating_average DECIMAL(3,2) NOT NULL DEFAULT 0,
  review_count INT NOT NULL DEFAULT 0,
  status TINYINT NOT NULL DEFAULT 0 COMMENT '0=pending, 1=success, 2=no_price, 3=error, 4=captcha_or_login',
  availability_status ENUM('unknown', 'active', 'out_of_stock', 'temporarily_unavailable', 'discontinued', 'invalid_url', 'fetch_error', 'blocked_or_captcha') NOT NULL DEFAULT 'unknown',
  error_message VARCHAR(500) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  match_score INT NULL,
  last_scraped_at DATETIME NULL,
  last_checked_at DATETIME NULL,
  next_scrape_at DATETIME NULL,
  next_check_at DATETIME NULL,
  blocked_until DATETIME NULL,
  retry_count INT NOT NULL DEFAULT 0,
  consecutive_failures INT NOT NULL DEFAULT 0,
  scrape_priority TINYINT NOT NULL DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_platform_product (product_id, platform_name),
  UNIQUE KEY uq_platform_product_id (platform_name, platform_product_id),
  UNIQUE KEY uq_platform_url_hash (platform_name, url_hash),
  KEY idx_platform_active (platform_name, is_active),
  KEY idx_platform_product_id (platform_name, platform_product_id),
  KEY idx_platform_url_hash (platform_name, url_hash),
  KEY idx_platform_scrape_queue (platform_name, is_active, blocked_until, next_scrape_at, scrape_priority, last_scraped_at),
  KEY idx_platform_link_availability (platform_name, is_active, availability_status, status, next_check_at),
  CONSTRAINT fk_platform_links_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS price_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  link_id INT NOT NULL,
  price INT NOT NULL,
  scraped_at DATETIME NOT NULL,
  KEY idx_price_history_link_time (link_id, scraped_at),
  CONSTRAINT fk_price_history_link
    FOREIGN KEY (link_id) REFERENCES platform_links(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS price_alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  target_price INT NOT NULL,
  is_notified TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_price_alert_user_product (user_id, product_id),
  CONSTRAINT fk_price_alerts_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_price_alerts_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notifications_user_time (user_id, created_at),
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_notifications_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
