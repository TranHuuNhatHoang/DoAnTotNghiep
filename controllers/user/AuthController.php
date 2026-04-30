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
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: index.php?role=admin&controller=dashboard&action=index");
        } else {
            header("Location: index.php");
        }
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>
