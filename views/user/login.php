<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - SmartPrice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --ink:#111827; --muted:#667085; --line:#e6e8ef; --brand:#f7c600; --page:#f5f7fb; --accent:#d92d20; }
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #111827 0%, #1f2937 48%, #f7c600 48%, #f7c600 100%);
            font-family: "Segoe UI", Arial, sans-serif;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .auth-shell {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(16,24,40,0.26);
        }
        .auth-panel {
            background: #111827;
            color: #fff;
            padding: 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 560px;
        }
        .brand { display:flex; align-items:center; gap:10px; font-size:1.25rem; font-weight:900; }
        .brand-icon { width:42px; height:42px; border-radius:8px; background:var(--brand); color:#111827; display:grid; place-items:center; }
        .auth-copy h1 { font-size:2.25rem; font-weight:900; line-height:1.08; margin-bottom:14px; }
        .auth-copy p { color:#d0d5dd; margin:0; font-weight:600; }
        .auth-points { display:grid; gap:12px; color:#d0d5dd; font-weight:700; }
        .auth-points span { display:flex; align-items:center; gap:10px; }
        .auth-points i { color:var(--brand); }
        .form-panel { padding:46px; display:flex; align-items:center; }
        .form-inner { width:100%; max-width:430px; margin:0 auto; }
        .form-title { font-weight:900; color:var(--ink); margin-bottom:6px; }
        .form-subtitle { color:var(--muted); font-weight:600; margin-bottom:28px; }
        .form-label { font-weight:800; color:var(--ink); }
        .form-control { height:50px; border-radius:8px; border:1px solid var(--line); }
        .form-control:focus { border-color:var(--brand); box-shadow:0 0 0 4px rgba(247,198,0,0.18); }
        .btn-auth { height:50px; border:0; border-radius:8px; background:#111827; color:#fff; font-weight:900; width:100%; }
        .btn-auth:hover { background:#1f2937; color:#fff; }
        .auth-link { color:#0b5fff; font-weight:800; text-decoration:none; }
        .alert { border-radius:8px; font-weight:700; }
        @media (max-width: 860px) {
            .auth-shell { grid-template-columns:1fr; }
            .auth-panel { min-height:auto; gap:34px; padding:30px; }
            .form-panel { padding:30px; }
        }
    </style>
</head>
<body>
<main class="auth-shell">
    <section class="auth-panel">
        <a href="index.php" class="brand text-white text-decoration-none">
            <span class="brand-icon"><i class="fas fa-tags"></i></span>
            <span>SmartPrice</span>
        </a>
        <div class="auth-copy">
            <h1>Theo dõi giá đa sàn gọn hơn.</h1>
            <p>Đăng nhập để lưu mức giá mong muốn, nhận thông báo giảm giá và quản lý danh sách sản phẩm đang theo dõi.</p>
        </div>
        <div class="auth-points">
            <span><i class="fas fa-bell"></i>Cảnh báo giá qua email</span>
            <span><i class="fas fa-chart-line"></i>Lịch sử giá theo từng sàn</span>
            <span><i class="fas fa-shield-alt"></i>Dữ liệu tập trung, dễ so sánh</span>
        </div>
    </section>

    <section class="form-panel">
        <div class="form-inner">
            <h2 class="form-title">Đăng nhập</h2>
            <p class="form-subtitle">Chào mừng bạn quay lại.</p>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'verified'): ?>
                <div class="alert alert-success">Xác thực thành công. Bạn có thể đăng nhập.</div>
            <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'reset_success'): ?>
                <div class="alert alert-success">Mật khẩu đã được cập nhật. Bạn có thể đăng nhập bằng mật khẩu mới.</div>
            <?php elseif(isset($_GET['error']) && $_GET['error'] == 'invalid_credentials'): ?>
                <div class="alert alert-danger">Email hoặc mật khẩu không đúng.</div>
            <?php elseif(isset($_GET['error']) && $_GET['error'] == 'unverified'): ?>
                <div class="alert alert-warning">Tài khoản chưa xác thực OTP.</div>
            <?php endif; ?>

            <form action="index.php?role=user&controller=auth&action=postLogin" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="ban@example.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                </div>
                <div class="text-end mb-3">
                    <a href="index.php?role=user&controller=auth&action=forgotPassword" class="auth-link">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn-auth">Đăng nhập</button>
            </form>

            <div class="text-center mt-4 text-muted fw-semibold">
                Chưa có tài khoản?
                <a href="index.php?role=user&controller=auth&action=register" class="auth-link">Đăng ký ngay</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
