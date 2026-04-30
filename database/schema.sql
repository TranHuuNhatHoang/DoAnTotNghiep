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
  description TEXT NULL,
  category_id INT NULL,
  thumbnail_url TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
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
  current_price INT NOT NULL DEFAULT 0,
  original_price INT NULL,
  historical_sold INT NOT NULL DEFAULT 0,
  rating_average DECIMAL(3,2) NOT NULL DEFAULT 0,
  review_count INT NOT NULL DEFAULT 0,
  status TINYINT NOT NULL DEFAULT 0 COMMENT '0=pending, 1=success, 2=no_price, 3=error',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  match_score INT NULL,
  last_scraped_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_platform_product (product_id, platform_name),
  KEY idx_platform_active (platform_name, is_active),
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
