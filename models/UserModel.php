<?php
class UserModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function generateOtpCode() {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function hashOtpCode($otpCode) {
        return hash('sha256', $otpCode);
    }

    // NÂNG CẤP LOGIC: Xử lý triệt để lỗi "Kẹt tài khoản chưa xác thực"
    public function register($email, $password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $otp_code = $this->generateOtpCode();
        $otp_hash = $this->hashOtpCode($otp_code);

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
                $updateStmt = $this->conn->prepare("UPDATE users SET password_hash = ?, otp_code_hash = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ?");
                $updateStmt->bind_param("sss", $password_hash, $otp_hash, $email);
                
                if ($updateStmt->execute()) {
                    return $otp_code; // Trả về mã để gửi Mail
                }
                return false;
            }
        } else {
            // Trường hợp 3: Email hoàn toàn mới -> Insert bình thường
            $insertStmt = $this->conn->prepare("INSERT INTO users (email, password_hash, otp_code_hash, otp_expires_at, is_verified) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 0)");
            $insertStmt->bind_param("sss", $email, $password_hash, $otp_hash);
            
            if ($insertStmt->execute()) {
                return $otp_code; // Trả về mã để gửi Mail
            }
            return false;
        }
    }

    public function verifyOTP($email, $otp_code) {
        $otp_hash = $this->hashOtpCode($otp_code);
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND otp_code_hash = ? AND otp_expires_at > NOW() AND is_verified = 0");
        $stmt->bind_param("ss", $email, $otp_hash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $updateStmt = $this->conn->prepare("UPDATE users SET is_verified = 1, otp_code_hash = NULL, otp_expires_at = NULL WHERE email = ?");
            $updateStmt->bind_param("s", $email);
            return $updateStmt->execute();
        }
        return false; 
    }

    public function refreshOTP($email) {
        $new_otp = $this->generateOtpCode();
        $new_otp_hash = $this->hashOtpCode($new_otp);
        $stmt = $this->conn->prepare("UPDATE users SET otp_code_hash = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ? AND is_verified = 0");
        $stmt->bind_param("ss", $new_otp_hash, $email);
        
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
                if (array_key_exists('is_active', $user) && (int) $user['is_active'] === 0) {
                    return false;
                }
                return $user; 
            }
        }
        return false;
    }

    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT id, email, full_name, role, is_verified, is_active, created_at FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    public function updateOwnProfile($id, $fullName) {
        $stmt = $this->conn->prepare("UPDATE users SET full_name = ? WHERE id = ? AND is_active = 1");
        if (!$stmt) return false;

        $stmt->bind_param("si", $fullName, $id);
        return $stmt->execute();
    }

    public function createPasswordResetToken($email, $tokenHash, $expiresAt) {
        $stmt = $this->conn->prepare("
            UPDATE users
            SET reset_token_hash = ?,
                reset_token_expires_at = ?,
                reset_token_used_at = NULL
            WHERE email = ?
              AND is_verified = 1
              AND is_active = 1
        ");
        $stmt->bind_param("sss", $tokenHash, $expiresAt, $email);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function getUserByValidResetTokenHash($tokenHash) {
        $stmt = $this->conn->prepare("
            SELECT id, email
            FROM users
            WHERE reset_token_hash = ?
              AND reset_token_expires_at > NOW()
              AND reset_token_used_at IS NULL
              AND is_verified = 1
              AND is_active = 1
            LIMIT 1
        ");
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    public function resetPasswordByTokenHash($tokenHash, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            UPDATE users
            SET password_hash = ?,
                reset_token_hash = NULL,
                reset_token_expires_at = NULL,
                reset_token_used_at = NOW()
            WHERE reset_token_hash = ?
              AND reset_token_expires_at > NOW()
              AND reset_token_used_at IS NULL
              AND is_verified = 1
              AND is_active = 1
        ");
        $stmt->bind_param("ss", $passwordHash, $tokenHash);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function createPasswordResetOtp($email, $otpHash, $expiresAt = null) {
        $stmt = $this->conn->prepare("
            UPDATE users
            SET reset_token_hash = ?,
                reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE),
                reset_token_used_at = NULL
            WHERE LOWER(email) = LOWER(?)
              AND is_verified = 1
              AND is_active = 1
        ");
        $stmt->bind_param("ss", $otpHash, $email);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function getUserByValidPasswordResetOtp($email, $otpHash) {
        $stmt = $this->conn->prepare("
            SELECT id, email
            FROM users
            WHERE LOWER(email) = LOWER(?)
              AND reset_token_hash = ?
              AND reset_token_expires_at > NOW()
              AND reset_token_used_at IS NULL
              AND is_verified = 1
              AND is_active = 1
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $otpHash);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    public function resetPasswordByOtp($email, $otpHash, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            UPDATE users
            SET password_hash = ?,
                reset_token_hash = NULL,
                reset_token_expires_at = NULL,
                reset_token_used_at = NOW()
            WHERE LOWER(email) = LOWER(?)
              AND reset_token_hash = ?
              AND reset_token_expires_at > NOW()
              AND reset_token_used_at IS NULL
              AND is_verified = 1
              AND is_active = 1
        ");
        $stmt->bind_param("sss", $passwordHash, $email, $otpHash);
        return $stmt->execute() && $stmt->affected_rows === 1;
    }

    public function getUsersForAdmin($filters = []) {
        $where = [];
        $params = [];
        $types = '';

        $keyword = trim($filters['keyword'] ?? '');
        if ($keyword !== '') {
            $where[] = "(email LIKE ? OR full_name LIKE ?)";
            $like = '%' . $keyword . '%';
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        $role = $filters['role'] ?? '';
        if (in_array($role, ['user', 'admin'], true)) {
            $where[] = "role = ?";
            $params[] = $role;
            $types .= 's';
        }

        if (($filters['is_verified'] ?? '') !== '') {
            $where[] = "is_verified = ?";
            $params[] = (int) $filters['is_verified'];
            $types .= 'i';
        }

        if (($filters['is_active'] ?? '') !== '') {
            $where[] = "is_active = ?";
            $params[] = (int) $filters['is_active'];
            $types .= 'i';
        }

        $sql = "SELECT id, email, full_name, role, is_verified, is_active, created_at FROM users";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserForAdmin($id) {
        $stmt = $this->conn->prepare("SELECT id, email, full_name, role, is_verified, is_active, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }

    public function countActiveAdmins($excludeId = null) {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND is_active = 1";
        $params = [];
        $types = '';

        if ($excludeId !== null) {
            $sql .= " AND id <> ?";
            $params[] = (int) $excludeId;
            $types .= 'i';
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }

    public function updateUserForAdmin($id, $fullName, $role, $isVerified, $isActive) {
        $stmt = $this->conn->prepare("
            UPDATE users
            SET full_name = ?,
                role = ?,
                is_verified = ?,
                is_active = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssiii", $fullName, $role, $isVerified, $isActive, $id);
        return $stmt->execute();
    }

    public function setUserActiveStatus($id, $isActive) {
        $stmt = $this->conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $isActive, $id);
        return $stmt->execute();
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
                (SELECT MIN(current_price) FROM platform_links WHERE product_id = p.id AND is_active = 1 AND current_price > 0) as min_price
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
