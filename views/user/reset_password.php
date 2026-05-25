<?php
$prefillEmail = $_SESSION['password_reset_email'] ?? '';
$notice = $_SESSION['password_reset_notice'] ?? '';
unset($_SESSION['password_reset_notice']);

function e_reset_password($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$errorMessage = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'password_mismatch') {
        $errorMessage = 'Mật khẩu nhập lại không khớp.';
    } elseif ($_GET['error'] === 'weak_password') {
        $errorMessage = 'Mật khẩu mới cần có tối thiểu 6 ký tự.';
    } else {
        $errorMessage = 'Email, mã OTP hoặc yêu cầu đặt lại mật khẩu không hợp lệ.';
    }
}

if ($notice === '' && ($_GET['msg'] ?? '') === 'otp_sent') {
    $notice = 'Nếu email tồn tại trong hệ thống, mã OTP đặt lại mật khẩu đã được gửi.';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - SmartPrice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --ink:#111827; --muted:#667085; --line:#e6e8ef; --brand:#f7c600; }
        body { min-height:100vh; margin:0; background:linear-gradient(135deg,#111827 0%,#1f2937 48%,#f7c600 48%,#f7c600 100%); font-family:"Segoe UI",Arial,sans-serif; display:grid; place-items:center; padding:24px; }
        .auth-card { width:min(520px,100%); background:#fff; border-radius:12px; padding:34px; box-shadow:0 24px 70px rgba(16,24,40,0.26); }
        .brand { display:flex; justify-content:center; align-items:center; gap:10px; color:var(--ink); font-size:1.3rem; font-weight:900; text-decoration:none; margin-bottom:26px; }
        .brand-icon { width:42px; height:42px; border-radius:8px; background:var(--brand); display:grid; place-items:center; }
        h1 { font-size:1.75rem; font-weight:900; text-align:center; margin-bottom:6px; color:var(--ink); }
        .sub { color:var(--muted); text-align:center; font-weight:600; margin-bottom:26px; }
        .form-label { font-weight:800; color:var(--ink); }
        .form-control { height:50px; border-radius:8px; border:1px solid var(--line); }
        .form-control:focus { border-color:var(--brand); box-shadow:0 0 0 4px rgba(247,198,0,0.18); }
        .btn-auth { height:50px; border:0; border-radius:8px; background:#111827; color:#fff; font-weight:900; width:100%; }
        .btn-auth:hover { background:#1f2937; color:#fff; }
        .auth-link { color:#0b5fff; font-weight:800; text-decoration:none; }
        .alert { border-radius:8px; font-weight:700; }
    </style>
</head>
<body>
<main class="auth-card">
    <a href="index.php" class="brand">
        <span class="brand-icon"><i class="fas fa-tags"></i></span>
        <span>SmartPrice</span>
    </a>

    <h1>Đặt lại mật khẩu</h1>
    <p class="sub">Nhập email, mã OTP trong email và mật khẩu mới. Mã OTP có hiệu lực trong 15 phút.</p>

    <?php if ($notice !== ''): ?>
        <div class="alert alert-success"><?php echo e_reset_password($notice); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo e_reset_password($errorMessage); ?></div>
    <?php endif; ?>

    <form action="index.php?role=user&controller=auth&action=postResetPassword" method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo e_reset_password($prefillEmail); ?>" placeholder="ban@example.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mã OTP</label>
            <input type="text" name="otp_code" class="form-control" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="Nhập 6 số OTP" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu mới</label>
            <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Nhập lại mật khẩu mới</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
        </div>
        <button type="submit" class="btn-auth">Cập nhật mật khẩu</button>
    </form>

    <div class="d-flex justify-content-between gap-3 mt-4 text-muted fw-semibold">
        <a href="index.php?role=user&controller=auth&action=forgotPassword" class="auth-link">Gửi lại OTP</a>
        <a href="index.php?role=user&controller=auth&action=login" class="auth-link">Quay lại đăng nhập</a>
    </div>
</main>
</body>
</html>
