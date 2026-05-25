<?php
require_once 'models/UserModel.php';

class AdminUserController {
    private $db;
    private $userModel;

    public function __construct($db = null) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php");
            exit();
        }

        $this->db = $db;
        if (!$this->db) {
            require_once 'config/database.php';
            $database = new Database();
            $this->db = $database->getConnection();
        }

        $this->userModel = new UserModel($this->db);
    }

    public function index() {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'role' => $this->sanitizeRole($_GET['role_filter'] ?? ''),
            'is_verified' => $this->sanitizeBinaryFilter($_GET['is_verified'] ?? ''),
            'is_active' => $this->sanitizeBinaryFilter($_GET['is_active'] ?? ''),
        ];

        $users = $this->userModel->getUsersForAdmin($filters);
        require_once 'views/admin/users.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=admin&controller=adminUser&action=index");
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $role = $this->sanitizeRole($_POST['role'] ?? '');
        $isVerified = (int) ($_POST['is_verified'] ?? 0);
        $isActive = (int) ($_POST['is_active'] ?? 0);

        if ($id <= 0 || !in_array($role, ['user', 'admin'], true) || !in_array($isVerified, [0, 1], true) || !in_array($isActive, [0, 1], true)) {
            $_SESSION['admin_error'] = 'Dữ liệu người dùng không hợp lệ.';
            $this->redirectIndex();
        }

        $targetUser = $this->userModel->getUserForAdmin($id);
        if (!$targetUser) {
            $_SESSION['admin_error'] = 'Không tìm thấy người dùng cần cập nhật.';
            $this->redirectIndex();
        }

        $currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
        if ($id === $currentAdminId && $role !== 'admin') {
            $_SESSION['admin_error'] = 'Admin không thể tự hạ quyền của chính mình.';
            $this->redirectIndex();
        }

        if ($id === $currentAdminId && $isActive === 0) {
            $_SESSION['admin_error'] = 'Admin không thể tự vô hiệu hóa tài khoản của chính mình.';
            $this->redirectIndex();
        }

        if ($this->wouldRemoveLastActiveAdmin($targetUser, $role, $isActive)) {
            $_SESSION['admin_error'] = 'Không thể vô hiệu hóa hoặc hạ quyền admin cuối cùng trong hệ thống.';
            $this->redirectIndex();
        }

        if ($this->userModel->updateUserForAdmin($id, $fullName, $role, $isVerified, $isActive)) {
            $_SESSION['admin_success'] = 'Đã cập nhật thông tin người dùng.';
        } else {
            $_SESSION['admin_error'] = 'Không thể cập nhật người dùng. Vui lòng thử lại.';
        }

        $this->redirectIndex();
    }

    public function toggleActive() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=admin&controller=adminUser&action=index");
            exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $isActive = (int) ($_POST['is_active'] ?? 0);

        if ($id <= 0 || !in_array($isActive, [0, 1], true)) {
            $_SESSION['admin_error'] = 'Yêu cầu cập nhật trạng thái không hợp lệ.';
            $this->redirectIndex();
        }

        $targetUser = $this->userModel->getUserForAdmin($id);
        if (!$targetUser) {
            $_SESSION['admin_error'] = 'Không tìm thấy người dùng cần cập nhật.';
            $this->redirectIndex();
        }

        $currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
        if ($id === $currentAdminId && $isActive === 0) {
            $_SESSION['admin_error'] = 'Admin không thể tự vô hiệu hóa tài khoản của chính mình.';
            $this->redirectIndex();
        }

        if ($this->wouldRemoveLastActiveAdmin($targetUser, $targetUser['role'], $isActive)) {
            $_SESSION['admin_error'] = 'Không thể vô hiệu hóa admin cuối cùng trong hệ thống.';
            $this->redirectIndex();
        }

        if ($this->userModel->setUserActiveStatus($id, $isActive)) {
            $_SESSION['admin_success'] = $isActive ? 'Đã kích hoạt tài khoản.' : 'Đã vô hiệu hóa tài khoản.';
        } else {
            $_SESSION['admin_error'] = 'Không thể cập nhật trạng thái tài khoản.';
        }

        $this->redirectIndex();
    }

    private function sanitizeRole($role) {
        return in_array($role, ['user', 'admin'], true) ? $role : '';
    }

    private function sanitizeBinaryFilter($value) {
        return in_array((string) $value, ['0', '1'], true) ? (string) $value : '';
    }

    private function wouldRemoveLastActiveAdmin($targetUser, $newRole, $newIsActive) {
        $wasActiveAdmin = ($targetUser['role'] === 'admin' && (int) $targetUser['is_active'] === 1);
        if (!$wasActiveAdmin) {
            return false;
        }

        $willRemainActiveAdmin = ($newRole === 'admin' && (int) $newIsActive === 1);
        if ($willRemainActiveAdmin) {
            return false;
        }

        return $this->userModel->countActiveAdmins((int) $targetUser['id']) === 0;
    }

    private function redirectIndex() {
        header("Location: index.php?role=admin&controller=adminUser&action=index");
        exit();
    }
}
?>
