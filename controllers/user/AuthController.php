<?php
require_once 'models/UserModel.php';
require_once 'services/MailService.php'; // Nhúng dịch vụ gửi Mail

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

    public function login() { require_once 'views/user/login.php'; }
    public function register() { require_once 'views/user/register.php'; }

    // Xử lý Đăng ký và Gửi Mail
    public function postRegister() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                header("Location: index.php?role=user&controller=auth&action=register&error=password_mismatch");
                exit();
            }

            // Gọi hàm register, nó sẽ trả về mã OTP 6 số
            $otp_code = $this->userModel->register($email, $password);

            if ($otp_code) {
                // Gửi OTP qua Email
                MailService::sendOTP($email, $otp_code);
                
                // Lưu tạm email vào session để mang sang trang Xác thực
                $_SESSION['temp_email'] = $email;
                header("Location: index.php?role=user&controller=auth&action=verify");
                exit();
            } else {
                header("Location: index.php?role=user&controller=auth&action=register&error=email_exists");
                exit();
            }
        }
    }

    // Hiển thị form nhập OTP
    public function verify() {
        if (!isset($_SESSION['temp_email'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }
        require_once 'views/user/verify_otp.php';
    }

    // Xử lý kiểm tra OTP
    public function postVerify() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['temp_email'])) {
            $email = $_SESSION['temp_email'];
            $otp_code = trim($_POST['otp_code']);

            if ($this->userModel->verifyOTP($email, $otp_code)) {
                unset($_SESSION['temp_email']); // Xóa email tạm
                header("Location: index.php?role=user&controller=auth&action=login&msg=verified");
                exit();
            } else {
                header("Location: index.php?role=user&controller=auth&action=verify&error=invalid_otp");
                exit();
            }
        }
    }
    // Xử lý khi người dùng bấm nút "Gửi lại OTP"
    public function resendOTP() {
        if (!isset($_SESSION['temp_email'])) {
            header("Location: index.php?role=user&controller=auth&action=login");
            exit();
        }
        
        $email = $_SESSION['temp_email'];
        $new_otp = $this->userModel->refreshOTP($email);
        
        if ($new_otp) {
            MailService::sendOTP($email, $new_otp); // Bắn lại mail mới
            header("Location: index.php?role=user&controller=auth&action=verify&msg=resent");
            exit();
        } else {
            header("Location: index.php?role=user&controller=auth&action=verify&error=resend_failed");
            exit();
        }
    }

    // Xử lý logic Đăng nhập (Đã chặn nếu chưa xác thực)
    // Xử lý logic Đăng nhập (Sửa lại đoạn lưu Session)
    public function postLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user === 'unverified') {
                header("Location: index.php?role=user&controller=auth&action=login&error=unverified");
                exit();
            } elseif ($user) {
                // LƯU THÊM ROLE VÀO SESSION
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role']; // <-- Thêm dòng này
                
                // ĐIỀU HƯỚNG THEO ROLE
                if ($user['role'] == 'admin') {
                    header("Location: index.php?role=admin&controller=dashboard&action=index");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                header("Location: index.php?role=user&controller=auth&action=login&error=invalid_credentials");
                exit();
            }
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>