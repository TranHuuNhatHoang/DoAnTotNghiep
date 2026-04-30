<?php
class ProductModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Lấy danh sách cho Admin
    // Cập nhật hàm này trong ProductModel.php để lấy thêm Tên Danh Mục
    public function getAllProductsWithStats() {
        $sql = "SELECT p.*, 
                c.name as category_name,
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND is_active = 1) as total_active_links,
                (SELECT MAX(last_scraped_at) FROM platform_links WHERE product_id = p.id) as last_update
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    // Bổ sung hàm lấy 6 sản phẩm Hot trực tiếp từ DB (Tối ưu hiệu suất)
    public function getTrendingProducts() {
        $sql = "SELECT p.*, 
                c.name as category_name,
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND is_active = 1) as total_active_links,
                (SELECT MAX(last_scraped_at) FROM platform_links WHERE product_id = p.id) as last_update,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC 
                LIMIT 6";
        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    /**
     * Lấy danh sách sản phẩm mới nhất (Sắp xếp theo ID giảm dần)
     */
    public function getNewProducts() {
        $sql = "SELECT p.*, 
                c.name as category_name,
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND is_active = 1) as total_active_links,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC LIMIT 8";
        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Lấy các sản phẩm có nhiều lượt liên kết nhất (Giả lập Top Deal)
     */
    public function getTopDeals() {
        $sql = "SELECT p.*, 
                c.name as category_name,
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND is_active = 1) as total_active_links,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                HAVING total_active_links > 1
                ORDER BY total_active_links DESC LIMIT 4";
        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

   // 2. Tìm kiếm sản phẩm (User) - NÂNG CẤP: Đã tích hợp Lazada và Lọc theo Danh mục
    public function searchProducts($keyword, $categoryId = null) {
        $searchTerm = "%" . $keyword . "%";
        
        // Cốt lõi cũ (Lấy giá 3 sàn) + Tính năng mới (LEFT JOIN Categories)
        $sql = "SELECT p.*, 
                c.name as category_name,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND is_active = 1 LIMIT 1) as tiki_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND is_active = 1 LIMIT 1) as shopee_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND is_active = 1 LIMIT 1) as lazada_price,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND is_active = 1 LIMIT 1) as tiki_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND is_active = 1 LIMIT 1) as shopee_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND is_active = 1 LIMIT 1) as lazada_url
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ?";

        $params = [$searchTerm];
        $types = "s";

        // Nếu người dùng có chọn danh mục từ Dropdown, ta nối thêm điều kiện WHERE
        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
            $types .= "i"; // 'i' đại diện cho kiểu Integer
        }

        $stmt = $this->conn->prepare($sql);
        
        // Truyền mảng tham số động vào bind_param (Dùng toán tử giải nén ...)
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 3. Lấy thông tin cơ bản của 1 SP
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // 4. Lấy thông tin các sàn liên kết (Dùng cho Detail)
    public function getPlatformsByProductId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM platform_links WHERE product_id = ? AND is_active = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 5. Lấy lịch sử giá để vẽ biểu đồ (Dùng cho Chart.js)
    public function getPriceHistory($productId) {
        $sql = "SELECT ph.price, ph.scraped_at, pl.platform_name 
                FROM price_history ph
                JOIN platform_links pl ON ph.link_id = pl.id
                WHERE pl.product_id = ?
                ORDER BY ph.scraped_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductSpecifications($productId) {
        $tableCheck = $this->conn->query("SHOW TABLES LIKE 'product_specifications'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            return [];
        }

        $sql = "SELECT group_name, spec_name, spec_value, source_platform
                FROM product_specifications
                WHERE product_id = ?
                ORDER BY display_order ASC, id ASC";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) {
            return [];
        }

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

    // 6. Thêm sản phẩm mới vào bảng products
    // 6. Thêm sản phẩm mới vào bảng products (Đã bổ sung category_id)
    public function createProduct($name, $description, $categoryId) {
        $stmt = $this->conn->prepare("INSERT INTO products (name, description, category_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $categoryId);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id; 
        }
        return false;
    }

    // --- BỔ SUNG CÁC HÀM QUẢN TRỊ SẢN PHẨM ---

    // Cập nhật thông tin sản phẩm
    public function updateProduct($id, $name, $description, $categoryId) {
        $stmt = $this->conn->prepare("UPDATE products SET name = ?, description = ?, category_id = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $description, $categoryId, $id);
        return $stmt->execute();
    }

    // Xóa sản phẩm
    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // 7. Thêm một liên kết nền tảng
    public function addPlatformLink($productId, $platformName, $url) {
        $checkSql = "SELECT id FROM platform_links WHERE product_id = ? AND platform_name = ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        $stmtCheck->bind_param("is", $productId, $platformName);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            $updateSql = "UPDATE platform_links SET product_url = ?, status = 0, is_active = 1 WHERE product_id = ? AND platform_name = ?";
            $stmtUpdate = $this->conn->prepare($updateSql);
            $stmtUpdate->bind_param("sis", $url, $productId, $platformName);
            return $stmtUpdate->execute();
        } else {
            $insertSql = "INSERT INTO platform_links (product_id, platform_name, product_url, status, is_active) VALUES (?, ?, ?, 0, 1)";
            $stmtInsert = $this->conn->prepare($insertSql);
            $stmtInsert->bind_param("iss", $productId, $platformName, $url);
            return $stmtInsert->execute();
        }
    }
    // --- CÁC HÀM XỬ LÝ CẢNH BÁO GIÁ (PRICE ALERTS) ---

    // 8. Lấy thông tin cảnh báo giá của 1 User đối với 1 Sản phẩm
    public function getPriceAlert($userId, $productId) {
        $stmt = $this->conn->prepare("SELECT * FROM price_alerts WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // 9. Thêm mới hoặc Cập nhật mức giá cảnh báo
    public function setPriceAlert($userId, $productId, $targetPrice) {
        $check = $this->getPriceAlert($userId, $productId);
        
        if ($check) {
            // Nếu đã từng theo dõi -> Cập nhật lại giá kỳ vọng mới và reset cờ is_notified
            $stmt = $this->conn->prepare("UPDATE price_alerts SET target_price = ?, is_notified = 0, created_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("ii", $targetPrice, $check['id']);
            return $stmt->execute();
        } else {
            // Nếu chưa từng theo dõi -> Thêm dòng mới
            $stmt = $this->conn->prepare("INSERT INTO price_alerts (user_id, product_id, target_price) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $userId, $productId, $targetPrice);
            return $stmt->execute();
        }
    }
    // --- DÀNH CHO ADMIN: Lấy toàn bộ danh sách Cảnh báo giá ---
    public function getAllAlertsForAdmin() {
        // Lấy thông tin User, Sản phẩm, Giá mong muốn, và Giá hiện tại rẻ nhất của sản phẩm đó
        $sql = "SELECT pa.id, u.email, p.name as product_name, p.id as p_id, pa.target_price, pa.is_notified, pa.created_at,
                       (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as current_min_price
                FROM price_alerts pa
                JOIN users u ON pa.user_id = u.id
                JOIN products p ON pa.product_id = p.id
                ORDER BY pa.created_at DESC";
                
        $result = $this->conn->query($sql);
        $alerts = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $alerts[] = $row;
            }
        }
        return $alerts;
    }
    // 10. Xóa cảnh báo giá (Hủy theo dõi)
    public function deletePriceAlert($userId, $productId) {
        $stmt = $this->conn->prepare("DELETE FROM price_alerts WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $productId);
        return $stmt->execute();
    }
    // --- BỔ SUNG CÁC HÀM QUẢN LÝ LINK SÀN ---

    // Cập nhật đường link hoặc trạng thái Bật/Tắt (is_active)
    public function updatePlatformLink($linkId, $url, $isActive) {
        $stmt = $this->conn->prepare("UPDATE platform_links SET product_url = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sii", $url, $isActive, $linkId);
        return $stmt->execute();
    }

    // Xóa vĩnh viễn một link
    public function deletePlatformLink($linkId) {
        $stmt = $this->conn->prepare("DELETE FROM platform_links WHERE id = ?");
        $stmt->bind_param("i", $linkId);
        return $stmt->execute();
    }
    /**
     * 1. Lấy gợi ý nhanh cho Autocomplete (AJAX)
     */
    public function getSuggestions($keyword) {
        $searchTerm = "%" . $keyword . "%";
        // Lấy 5 sản phẩm và 3 danh mục khớp với từ khóa
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
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 2. Tìm kiếm nâng cao (Lọc theo Sàn, Giá, Sắp xếp)
     */
    public function searchProductsAdvanced($keyword, $catId, $platform, $minPrice, $maxPrice, $sort) {
        $searchTerm = "%" . $keyword . "%";
        
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' AND is_active = 1 LIMIT 1) as tiki_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' AND is_active = 1 LIMIT 1) as shopee_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' AND is_active = 1 LIMIT 1) as lazada_price,
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.name LIKE ?";

        $params = [$searchTerm];
        $types = "s";

        // Lọc theo Danh mục
        if ($catId) { $sql .= " AND p.category_id = ?"; $params[] = $catId; $types .= "i"; }
        
        // Lọc theo Sàn (Chỉ lấy SP có link trên sàn đó)
        if ($platform) {
            $sql .= " AND EXISTS (SELECT 1 FROM platform_links WHERE product_id = p.id AND platform_name = ? AND is_active = 1 AND current_price > 0)";
            $params[] = $platform; $types .= "s";
        }

        // Lọc theo Khoảng giá (Dựa trên giá rẻ nhất hiện có)
        if ($minPrice) { $sql .= " AND (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) >= ?"; $params[] = $minPrice; $types .= "i"; }
        if ($maxPrice) { $sql .= " AND (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) <= ?"; $params[] = $maxPrice; $types .= "i"; }

        // Sắp xếp
        switch($sort) {
            case 'price_asc': $sql .= " ORDER BY min_price ASC"; break;
            case 'price_desc': $sql .= " ORDER BY min_price DESC"; break;
            default: $sql .= " ORDER BY p.id DESC";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    // BỔ SUNG 1: Lấy các sản phẩm cùng danh mục (Related Products)
    public function getRelatedProducts($categoryId, $excludeProductId) {
        if (!$categoryId) return [];
        $sql = "SELECT p.*, 
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
                FROM products p 
                WHERE p.category_id = ? AND p.id != ? 
                ORDER BY p.id DESC LIMIT 4";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $categoryId, $excludeProductId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // BỔ SUNG 2: Tính toán thống kê giá (Insight)
    public function getPriceStats($productId) {
        $sql = "SELECT MAX(price) as max_price, MIN(price) as min_price, AVG(price) as avg_price 
                FROM price_history WHERE link_id IN (SELECT id FROM platform_links WHERE product_id = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
