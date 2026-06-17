<?php
require_once 'models/UserModel.php';
require_once 'services/MailService.php';

class AuthController {
    private $db;
    private $userModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new UserModel($this->db);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        require_once 'views/user/login.php';
    }

    public function register() {
        require_once 'views/user/register.php';
    }

    public function postRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=user&controller=auth&action=register");
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: index.php?role=user&controller=auth&action=register&error=invalid_email");
            exit();
        }

        if ($password !== $confirmPassword) {
            header("Location: index.php?role=user&controller=auth&action=register&error=password_mismatch");
            exit();
        }

        $otpCode = $this->userModel->register($email, $password);
        if (!$otpCode) {
            header("Location: index.php?role=user&controller=auth&action=register&error=email_exists");
            exit();
        }

        $_SESSION['temp_email'] = $email;
        if (!MailService::sendOTP($email, $otpCode)) {
            header("Location: index.php?role=user&controller=auth&action=verify&error=mail_failed");
            exit();
        }

        header("Location: index.php?role=user&controller=auth&action=verify");
        exit();
    }

    public function verify() {
        if (!isset($_SESSION['temp_email'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        require_once 'views/user/verify_otp.php';
    }

    public function postVerify() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['temp_email'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $email = $_SESSION['temp_email'];
        $otpCode = trim($_POST['otp_code'] ?? '');

        if ($this->userModel->verifyOTP($email, $otpCode)) {
            unset($_SESSION['temp_email']);
            header("Location: index.php?role=user&controller=auth&action=login&msg=verified");
            exit();
        }

        header("Location: index.php?role=user&controller=auth&action=verify&error=invalid_otp");
        exit();
    }

    public function resendOTP() {
        if (!isset($_SESSION['temp_email'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $email = $_SESSION['temp_email'];
        $newOtp = $this->userModel->refreshOTP($email);

        if ($newOtp && MailService::sendOTP($email, $newOtp)) {
            header("Location: index.php?role=user&controller=auth&action=verify&msg=resent");
            exit();
        }

        header("Location: index.php?role=user&controller=auth&action=verify&error=resend_failed");
        exit();
    }

    public function postLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->login($email, $password);
        if ($user === 'unverified') {
            header("Location: index.php?role=user&controller=auth&action=login&error=unverified");
            exit();
        }

        if (!$user) {
            header("Location: index.php?role=user&controller=auth&action=login&error=invalid_credentials");
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_full_name'] = trim($user['full_name'] ?? '');
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: index.php?role=admin&controller=dashboard&action=index");
        } else {
            header("Location: index.php");
        }
        exit();
    }

    public function forgotPassword() {
        require_once 'views/user/forgot_password.php';
    }

    public function postForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=user&controller=auth&action=forgotPassword");
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $notice = 'Nếu email tồn tại trong hệ thống, mã OTP đặt lại mật khẩu đã được gửi.';

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otpHash = hash('sha256', $otpCode);

                if ($this->userModel->createPasswordResetOtp($email, $otpHash)) {
                    if (!MailService::sendPasswordResetOtp($email, $otpCode)) {
                        error_log('Password reset OTP email failed for: ' . $email);
                    }
                }
            } catch (Exception $e) {
                error_log('Create password reset OTP failed: ' . $e->getMessage());
            }

            $_SESSION['password_reset_email'] = $email;
        }

        $_SESSION['password_reset_notice'] = $notice;
        header("Location: index.php?role=user&controller=auth&action=resetPassword&msg=otp_sent");
        exit();
    }

    public function resetPassword() {
        if (isset($_GET['token'])) {
            $_SESSION['password_reset_notice'] = 'Chức năng đặt lại mật khẩu đã chuyển sang xác nhận bằng mã OTP. Vui lòng nhập email để nhận mã OTP mới.';
            header("Location: index.php?role=user&controller=auth&action=forgotPassword&legacy=1");
            exit();
        }

        require_once 'views/user/reset_password.php';
    }

    public function postResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $otpCode = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $_SESSION['password_reset_email'] = $email;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otpCode)) {
            header("Location: index.php?role=user&controller=auth&action=resetPassword&error=invalid");
            exit();
        }

        if (strlen($password) < 6) {
            header("Location: index.php?role=user&controller=auth&action=resetPassword&error=weak_password");
            exit();
        }

        if ($password !== $confirmPassword) {
            header("Location: index.php?role=user&controller=auth&action=resetPassword&error=password_mismatch");
            exit();
        }

        $otpHash = hash('sha256', $otpCode);
        if (!$this->userModel->getUserByValidPasswordResetOtp($email, $otpHash)) {
            header("Location: index.php?role=user&controller=auth&action=resetPassword&error=invalid");
            exit();
        }

        if ($this->userModel->resetPasswordByOtp($email, $otpHash, $password)) {
            unset($_SESSION['password_reset_email']);
            header("Location: index.php?role=user&controller=auth&action=login&msg=reset_success");
            exit();
        }

        header("Location: index.php?role=user&controller=auth&action=resetPassword&error=invalid");
        exit();
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $user = $this->userModel->getUserById((int) $_SESSION['user_id']);
        if (!$user || (int) ($user['is_active'] ?? 1) === 0) {
            session_destroy();
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $_SESSION['user_full_name'] = trim($user['full_name'] ?? '');
        require_once 'views/user/profile.php';
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $fullNameLength = function_exists('mb_strlen') ? mb_strlen($fullName, 'UTF-8') : strlen($fullName);
        if ($fullNameLength > 120) {
            $_SESSION['profile_error'] = 'Họ tên không được vượt quá 120 ký tự.';
            header("Location: index.php?role=user&controller=auth&action=profile");
            exit();
        }

        if ($this->userModel->updateOwnProfile((int) $_SESSION['user_id'], $fullName)) {
            $_SESSION['user_full_name'] = $fullName;
            $_SESSION['profile_success'] = 'Đã cập nhật hồ sơ cá nhân.';
        } else {
            $_SESSION['profile_error'] = 'Không thể cập nhật hồ sơ lúc này.';
        }

        header("Location: index.php?role=user&controller=auth&action=profile");
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>
