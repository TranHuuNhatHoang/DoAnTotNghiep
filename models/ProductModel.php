<?php
class ProductModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function fetchProductCards($orderBy, $limit = null, $having = '', $includeLastUpdate = false) {
        $lastUpdateSelect = $includeLastUpdate
            ? ", (SELECT MAX(last_scraped_at) FROM platform_links WHERE product_id = p.id) as last_update"
            : "";
        $havingSql = $having !== '' ? " HAVING " . $having : "";
        $limitSql = $limit !== null ? " LIMIT " . (int) $limit : "";

        $sql = "SELECT p.*,
                c.name as category_name,
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND is_active = 1) as total_active_links,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                {$lastUpdateSelect}
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                {$havingSql}
                ORDER BY {$orderBy}
                {$limitSql}";

        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllProductsWithStats() {
        return $this->fetchProductCards('p.id DESC', null, '', true);
    }

    public function getTrendingProducts() {
        return $this->fetchProductCards('p.id DESC', 6, '', true);
    }

    public function getNewProducts() {
        return $this->fetchProductCards('p.id DESC', 8);
    }

    public function getTopDeals() {
        return $this->fetchProductCards('total_active_links DESC', 4, 'total_active_links > 1');
    }

    public function searchProducts($keyword, $categoryId = null) {
        return $this->searchProductsAdvanced($keyword, $categoryId, null, null, null, 'newest');
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getPlatformsByProductId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM platform_links WHERE product_id = ? AND is_active = 1");
        if (!$stmt) return [];

        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPriceHistory($productId) {
        $sql = "SELECT ph.price, ph.scraped_at, pl.platform_name
                FROM price_history ph
                JOIN platform_links pl ON ph.link_id = pl.id
                WHERE pl.product_id = ?
                ORDER BY ph.scraped_at ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductSpecifications($productId) {
        $sql = "SELECT group_name, spec_name, spec_value, source_platform
                FROM product_specifications
                WHERE product_id = ?
                ORDER BY display_order ASC, id ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) return [];

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $groups = [];

        foreach ($rows as $row) {
            $groupName = trim((string) ($row['group_name'] ?? ''));
            if ($groupName === '') {
                $groupName = 'Thông tin sản phẩm';
            }

            if (!isset($groups[$groupName])) {
                $groups[$groupName] = [];
            }

            $groups[$groupName][] = $row;
        }

        return $groups;
    }

    public function createProduct($name, $description, $categoryId) {
        $stmt = $this->conn->prepare("INSERT INTO products (name, description, category_id) VALUES (?, ?, ?)");
        if (!$stmt) return false;

        $stmt->bind_param("ssi", $name, $description, $categoryId);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    public function updateProduct($id, $name, $description, $categoryId) {
        $stmt = $this->conn->prepare("UPDATE products SET name = ?, description = ?, category_id = ? WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ssii", $name, $description, $categoryId, $id);
        return $stmt->execute();
    }

    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function addPlatformLink($productId, $platformName, $url) {
        $sql = "INSERT INTO platform_links
                    (product_id, platform_name, product_url, current_price, original_price,
                     historical_sold, rating_average, review_count, status, is_active,
                     availability_status, error_message, last_checked_at, next_scrape_at,
                     next_check_at, blocked_until, retry_count, consecutive_failures)
                VALUES (?, ?, ?, 0, NULL, 0, 0, 0, 0, 1, 'unknown', NULL, NULL, NULL, NULL, NULL, 0, 0)
                ON DUPLICATE KEY UPDATE
                    current_price = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE current_price END,
                    original_price = CASE WHEN product_url <> VALUES(product_url) THEN NULL ELSE original_price END,
                    historical_sold = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE historical_sold END,
                    rating_average = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE rating_average END,
                    review_count = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE review_count END,
                    status = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE status END,
                    availability_status = CASE WHEN product_url <> VALUES(product_url) THEN 'unknown' ELSE availability_status END,
                    error_message = CASE WHEN product_url <> VALUES(product_url) THEN NULL ELSE error_message END,
                    last_checked_at = CASE WHEN product_url <> VALUES(product_url) THEN NULL ELSE last_checked_at END,
                    next_scrape_at = CASE WHEN product_url <> VALUES(product_url) THEN NULL ELSE next_scrape_at END,
                    next_check_at = CASE WHEN product_url <> VALUES(product_url) THEN NULL ELSE next_check_at END,
                    blocked_until = CASE WHEN product_url <> VALUES(product_url) THEN NULL ELSE blocked_until END,
                    retry_count = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE retry_count END,
                    consecutive_failures = CASE WHEN product_url <> VALUES(product_url) THEN 0 ELSE consecutive_failures END,
                    product_url = VALUES(product_url),
                    is_active = 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iss", $productId, $platformName, $url);
        return $stmt->execute();
    }

    public function getPriceAlert($userId, $productId) {
        $stmt = $this->conn->prepare("SELECT * FROM price_alerts WHERE user_id = ? AND product_id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function setPriceAlert($userId, $productId, $targetPrice) {
        $sql = "INSERT INTO price_alerts (user_id, product_id, target_price, is_notified)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE
                    target_price = VALUES(target_price),
                    is_notified = 0,
                    created_at = CURRENT_TIMESTAMP";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("iii", $userId, $productId, $targetPrice);
        return $stmt->execute();
    }

    public function getAllAlertsForAdmin() {
        $sql = "SELECT pa.id, u.email, p.name as product_name, p.id as p_id, pa.target_price, pa.is_notified, pa.created_at,
                       (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as current_min_price
                FROM price_alerts pa
                JOIN users u ON pa.user_id = u.id
                JOIN products p ON pa.product_id = p.id
                ORDER BY pa.created_at DESC";

        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function deletePriceAlert($userId, $productId) {
        $stmt = $this->conn->prepare("DELETE FROM price_alerts WHERE user_id = ? AND product_id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ii", $userId, $productId);
        return $stmt->execute();
    }

    public function updatePlatformLink($linkId, $url, $isActive) {
        $sql = "UPDATE platform_links
                SET product_url = ?,
                    is_active = ?,
                    current_price = CASE WHEN product_url <> ? THEN 0 ELSE current_price END,
                    original_price = CASE WHEN product_url <> ? THEN NULL ELSE original_price END,
                    historical_sold = CASE WHEN product_url <> ? THEN 0 ELSE historical_sold END,
                    rating_average = CASE WHEN product_url <> ? THEN 0 ELSE rating_average END,
                    review_count = CASE WHEN product_url <> ? THEN 0 ELSE review_count END,
                    status = CASE WHEN product_url <> ? THEN 0 ELSE status END,
                    availability_status = CASE WHEN product_url <> ? THEN 'unknown' ELSE availability_status END,
                    error_message = CASE WHEN product_url <> ? THEN NULL ELSE error_message END,
                    last_checked_at = CASE WHEN product_url <> ? THEN NULL ELSE last_checked_at END,
                    next_scrape_at = CASE WHEN product_url <> ? THEN NULL ELSE next_scrape_at END,
                    next_check_at = CASE WHEN product_url <> ? THEN NULL ELSE next_check_at END,
                    blocked_until = CASE WHEN product_url <> ? THEN NULL ELSE blocked_until END,
                    retry_count = CASE WHEN product_url <> ? THEN 0 ELSE retry_count END,
                    consecutive_failures = CASE WHEN product_url <> ? THEN 0 ELSE consecutive_failures END
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "sissssssssssssssi",
            $url,
            $isActive,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $url,
            $linkId
        );
        return $stmt->execute();
    }

    public function deletePlatformLink($linkId) {
        $stmt = $this->conn->prepare("DELETE FROM platform_links WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $linkId);
        return $stmt->execute();
    }

    public function getSuggestions($keyword) {
        $searchTerm = "%" . $keyword . "%";
        $sql = "(SELECT id,
                        CONVERT(name USING utf8mb4) COLLATE utf8mb4_unicode_ci as name,
                        CAST('product' AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as type
                 FROM products WHERE name LIKE ? LIMIT 5)
                UNION ALL
                (SELECT id,
                        CONVERT(name USING utf8mb4) COLLATE utf8mb4_unicode_ci as name,
                        CAST('category' AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci as type
                 FROM categories WHERE name LIKE ? LIMIT 3)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function searchProductsAdvanced($keyword, $catId, $platform, $minPrice, $maxPrice, $sort) {
        $searchTerm = "%" . $keyword . "%";

        $sql = "SELECT p.*, c.name as category_name,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND is_active = 1 LIMIT 1) as tiki_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND is_active = 1 LIMIT 1) as shopee_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND is_active = 1 LIMIT 1) as lazada_price,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND is_active = 1 LIMIT 1) as tiki_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND is_active = 1 LIMIT 1) as shopee_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND is_active = 1 LIMIT 1) as lazada_url,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ?";

        $params = [$searchTerm];
        $types = "s";

        if ($catId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $catId;
            $types .= "i";
        }

        if ($platform) {
            $sql .= " AND EXISTS (SELECT 1 FROM platform_links WHERE product_id = p.id AND platform_name = ? AND is_active = 1 AND current_price > 0)";
            $params[] = $platform;
            $types .= "s";
        }

        if ($minPrice) {
            $sql .= " AND (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) >= ?";
            $params[] = $minPrice;
            $types .= "i";
        }

        if ($maxPrice) {
            $sql .= " AND (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) <= ?";
            $params[] = $maxPrice;
            $types .= "i";
        }

        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY min_price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY min_price DESC";
                break;
            default:
                $sql .= " ORDER BY p.id DESC";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRelatedProducts($categoryId, $excludeProductId) {
        if (!$categoryId) return [];

        $sql = "SELECT p.*,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p
                WHERE p.category_id = ? AND p.id != ?
                ORDER BY p.id DESC LIMIT 4";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("ii", $categoryId, $excludeProductId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPriceStats($productId) {
        $sql = "SELECT MAX(price) as max_price, MIN(price) as min_price, AVG(price) as avg_price
                FROM price_history WHERE link_id IN (SELECT id FROM platform_links WHERE product_id = ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['max_price' => 0, 'min_price' => 0, 'avg_price' => 0];

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
