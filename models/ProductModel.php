<?php
class ProductModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Lấy danh sách cho Admin
    public function getAllProductsWithStats() {
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM platform_links WHERE product_id = p.id AND status = 1) as total_active_links,
                (SELECT MAX(last_scraped_at) FROM platform_links WHERE product_id = p.id) as last_update
                FROM products p ORDER BY p.id DESC";
        $result = $this->conn->query($sql);
        return ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // 2. Tìm kiếm sản phẩm (User) - ĐÃ BỔ SUNG LAZADA
    public function searchProducts($keyword) {
        $searchTerm = "%" . $keyword . "%";
        $sql = "SELECT p.*, 
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' LIMIT 1) as tiki_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' LIMIT 1) as shopee_price,
                (SELECT current_price FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' LIMIT 1) as lazada_price,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Tiki' LIMIT 1) as tiki_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Shopee' LIMIT 1) as shopee_url,
                (SELECT product_url FROM platform_links WHERE product_id = p.id AND platform_name = 'Lazada' LIMIT 1) as lazada_url
                FROM products p 
                WHERE p.name LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $searchTerm);
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

    // 6. Thêm sản phẩm mới vào bảng products
    public function createProduct($name, $description) {
        $stmt = $this->conn->prepare("INSERT INTO products (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id; 
        }
        return false;
    }

    // 7. Thêm một liên kết nền tảng
    public function addPlatformLink($productId, $platformName, $url) {
        $checkSql = "SELECT id FROM platform_links WHERE product_id = ? AND platform_name = ?";
        $stmtCheck = $this->conn->prepare($checkSql);
        $stmtCheck->bind_param("is", $productId, $platformName);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            $updateSql = "UPDATE platform_links SET product_url = ?, status = 0 WHERE product_id = ? AND platform_name = ?";
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
                       (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND current_price > 0) as current_min_price
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
}
?>