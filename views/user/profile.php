<?php
$successMessage = $_SESSION['profile_success'] ?? '';
$errorMessage = $_SESSION['profile_error'] ?? '';
unset($_SESSION['profile_success'], $_SESSION['profile_error']);

function e_profile($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$fullName = $user['full_name'] ?? '';
$email = $user['email'] ?? '';
$role = $user['role'] ?? 'user';
$createdAt = !empty($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Chưa rõ';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - SmartPrice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --ink: #111827;
            --muted: #667085;
            --brand: #f7c600;
            --navy: #0f172a;
            --line: #e4e7ec;
            --bg: #f3f6fb;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--ink);
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }

        .topbar {
            background: var(--navy);
            border-bottom: 4px solid var(--brand);
            color: #fff;
        }

        .topbar-inner {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand-link,
        .top-action {
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: var(--brand);
            color: #111827;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .top-action {
            min-height: 40px;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 8px;
            padding: 0 14px;
            background: rgba(255,255,255,.08);
        }

        .top-action:hover { color: #fff; background: rgba(255,255,255,.14); }

        .profile-shell {
            max-width: 980px;
            margin: 34px auto 54px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 18px;
        }

        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 16px 36px rgba(15,23,42,.08);
        }

        .profile-card {
            padding: 24px;
        }

        .avatar {
            width: 74px;
            height: 74px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: #fff8d6;
            color: #111827;
            border: 1px solid #ffe58a;
            font-size: 2rem;
            margin-bottom: 18px;
        }

        .profile-name {
            font-size: 1.35rem;
            font-weight: 950;
            margin: 0 0 4px;
        }

        .profile-email {
            color: var(--muted);
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        .meta-list {
            margin-top: 22px;
            display: grid;
            gap: 12px;
        }

        .meta-item {
            border-top: 1px solid var(--line);
            padding-top: 12px;
        }

        .meta-label {
            color: var(--muted);
            font-size: .82rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .meta-value {
            margin-top: 2px;
            font-weight: 850;
        }

        .form-panel {
            padding: 26px;
        }

        .section-title {
            font-weight: 950;
            margin-bottom: 6px;
        }

        .section-subtitle {
            color: var(--muted);
            font-weight: 650;
            margin-bottom: 22px;
        }

        .form-control {
            min-height: 46px;
            border-radius: 8px;
            border-color: var(--line);
            font-weight: 650;
        }

        .btn-brand {
            min-height: 46px;
            border: 0;
            border-radius: 8px;
            background: var(--brand);
            color: #111827;
            font-weight: 900;
            padding: 0 18px;
        }

        .btn-soft {
            min-height: 46px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            font-weight: 850;
            padding: 0 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-danger-soft {
            color: #b42318;
            border-color: #fecdca;
            background: #fff5f5;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 767px) {
            .topbar-inner { align-items: flex-start; flex-direction: column; padding: 14px 0; }
            .profile-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand-link" href="index.php">
                <span class="brand-mark"><i class="fas fa-bolt"></i></span>
                <span>SmartPrice</span>
            </a>
            <div class="top-actions">
                <a class="top-action" href="index.php"><i class="fas fa-house"></i>Trang chủ</a>
                <a class="top-action" href="index.php?role=user&controller=product&action=myAlerts"><i class="fas fa-chart-line"></i>Dashboard</a>
            </div>
        </div>
    </header>

    <main class="container profile-shell">
        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success fw-semibold"><?php echo e_profile($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger fw-semibold"><?php echo e_profile($errorMessage); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <aside class="panel profile-card">
                <div class="avatar"><i class="fas fa-user"></i></div>
                <h1 class="profile-name"><?php echo e_profile($fullName !== '' ? $fullName : 'Chưa cập nhật họ tên'); ?></h1>
                <div class="profile-email"><?php echo e_profile($email); ?></div>

                <div class="meta-list">
                    <div class="meta-item">
                        <div class="meta-label">Vai trò</div>
                        <div class="meta-value"><?php echo e_profile($role); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Ngày tạo</div>
                        <div class="meta-value"><?php echo e_profile($createdAt); ?></div>
                    </div>
                </div>
            </aside>

            <section class="panel form-panel">
                <h2 class="section-title">Hồ sơ cá nhân</h2>
                <p class="section-subtitle">Cập nhật tên hiển thị của bạn trên hệ thống. Email đăng nhập không thể thay đổi tại đây.</p>

                <form action="index.php?role=user&controller=auth&action=updateProfile" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" value="<?php echo e_profile($email); ?>" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Họ tên</label>
                        <input type="text" name="full_name" class="form-control" maxlength="120" value="<?php echo e_profile($fullName); ?>" placeholder="Nhập họ tên của bạn">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-brand"><i class="fas fa-save me-2"></i>Lưu thay đổi</button>
                        <a href="index.php?role=user&controller=auth&action=logout" class="btn-soft btn-danger-soft">
                            <i class="fas fa-right-from-bracket"></i>Đăng xuất
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
