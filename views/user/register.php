<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Hệ Thống So Sánh Giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 450px; padding: 20px; }
    </style>
</head>
<body>

<div class="card auth-card">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>Đăng Ký</h2>
            <p class="text-muted">Tạo tài khoản để nhận Cảnh báo Giá qua Email</p>
        </div>

        <?php if(isset($_GET['error']) && $_GET['error'] == 'password_mismatch'): ?>
            <div class="alert alert-danger py-2">Mật khẩu nhập lại không khớp!</div>
        <?php elseif(isset($_GET['error']) && $_GET['error'] == 'email_exists'): ?>
            <div class="alert alert-danger py-2">Email này đã được sử dụng!</div>
        <?php endif; ?>

        <form action="index.php?role=user&controller=auth&action=postRegister" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Địa chỉ Email</label>
                <input type="email" name="email" class="form-control form-control-lg" placeholder="nhapemail@gmail.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Mật khẩu</label>
                <input type="password" name="password" class="form-control form-control-lg" placeholder="••••••••" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Nhập lại Mật khẩu</label>
                <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-3">Đăng Ký Ngay</button>
            <div class="text-center">
                Đã có tài khoản? <a href="index.php?role=user&controller=auth&action=login" class="text-decoration-none fw-bold">Đăng nhập</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>