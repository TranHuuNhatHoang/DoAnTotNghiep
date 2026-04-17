<?php
class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Kiểm tra Email đã tồn tại chưa (Hàm này có thể giữ lại để dùng cho các mục đích khác)
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // NÂNG CẤP LOGIC: Xử lý triệt để lỗi "Kẹt tài khoản chưa xác thực"
    public function register($email, $password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $otp_code = rand(100000, 999999);

        // BƯỚC 1: Kiểm tra xem Email đã có trong hệ thống chưa và Trạng thái xác thực
        $checkStmt = $this->conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if ($user['is_verified'] == 1) {
                // Trường hợp 1: Đã xác thực thành công -> Chặn không cho đăng ký
                return false; 
            } else {
                // Trường hợp 2: Có email nhưng CHƯA xác thực (Bị rớt mạng, tắt trình duyệt...)
                // -> Ghi đè mật khẩu mới, tạo OTP mới và gia hạn 5 phút
                $updateStmt = $this->conn->prepare("UPDATE users SET password_hash = ?, otp_code = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ?");
                $updateStmt->bind_param("sss", $password_hash, $otp_code, $email);
                
                if ($updateStmt->execute()) {
                    return $otp_code; // Trả về mã để gửi Mail
                }
                return false;
            }
        } else {
            // Trường hợp 3: Email hoàn toàn mới -> Insert bình thường
            $insertStmt = $this->conn->prepare("INSERT INTO users (email, password_hash, otp_code, otp_expires_at, is_verified) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 0)");
            $insertStmt->bind_param("sss", $email, $password_hash, $otp_code);
            
            if ($insertStmt->execute()) {
                return $otp_code; // Trả về mã để gửi Mail
            }
            return false;
        }
    }

    public function verifyOTP($email, $otp_code) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND otp_code = ? AND otp_expires_at > NOW() AND is_verified = 0");
        $stmt->bind_param("ss", $email, $otp_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $updateStmt = $this->conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL WHERE email = ?");
            $updateStmt->bind_param("s", $email);
            return $updateStmt->execute();
        }
        return false; 
    }

    public function refreshOTP($email) {
        $new_otp = rand(100000, 999999);
        $stmt = $this->conn->prepare("UPDATE users SET otp_code = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ? AND is_verified = 0");
        $stmt->bind_param("ss", $new_otp, $email);
        
        if ($stmt->execute()) {
            return $new_otp;
        }
        return false;
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                if ($user['is_verified'] == 0) {
                    return 'unverified'; 
                }
                return $user; 
            }
        }
        return false;
    }
    // --- CÁC HÀM XỬ LÝ THÔNG BÁO WEB ---
    
    /**
     * Lấy 10 thông báo mới nhất của người dùng kèm tên sản phẩm
     */
    public function getNotifications($userId) {
        $stmt = $this->conn->prepare("
            SELECT n.*, p.name as product_name 
            FROM notifications n
            JOIN products p ON n.product_id = p.id
            WHERE n.user_id = ? 
            ORDER BY n.created_at DESC 
            LIMIT 10
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        return $notifications;
    }

    /**
     * Đánh dấu một thông báo là đã đọc
     */
    public function markNotificationRead($notifId, $userId) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notifId, $userId);
        return $stmt->execute();
    }

    // --- CHỨC NĂNG DANH SÁCH THEO DÕI ---

    /**
     * Lấy danh sách các sản phẩm người dùng đang cài đặt cảnh báo giá
     */
    public function getUserAlerts($userId) {
        $sql = "
            SELECT 
                pa.id as alert_id,
                pa.target_price,
                pa.created_at as alert_created_at,
                p.id as product_id,
                p.name as product_name,
                p.description,
                p.thumbnail_url, -- ĐÃ BỔ SUNG LẤY ẢNH SẢN PHẨM
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND current_price > 0) as min_price
            FROM price_alerts pa
            JOIN products p ON pa.product_id = p.id
            WHERE pa.user_id = ?
            ORDER BY pa.created_at DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>