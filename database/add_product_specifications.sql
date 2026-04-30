USE web_test;

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
