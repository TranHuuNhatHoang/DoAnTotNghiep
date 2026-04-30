<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP - SmartPrice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --ink:#111827; --muted:#667085; --line:#e6e8ef; --brand:#f7c600; }
        body { min-height:100vh; margin:0; background:linear-gradient(135deg,#111827 0%,#1f2937 48%,#f7c600 48%,#f7c600 100%); font-family:"Segoe UI",Arial,sans-serif; display:grid; place-items:center; padding:24px; }
        .otp-card { width:min(500px,100%); background:#fff; border-radius:12px; padding:34px; box-shadow:0 24px 70px rgba(16,24,40,0.26); text-align:center; }
        .mail-icon { width:74px; height:74px; border-radius:12px; background:#fffbeb; color:#111827; display:grid; place-items:center; font-size:2rem; margin:0 auto 20px; }
        h1 { font-size:1.75rem; font-weight:900; margin-bottom:8px; color:var(--ink); }
        .sub { color:var(--muted); font-weight:600; margin-bottom:24px; }
        .otp-input { height:58px; border-radius:8px; border:1px solid var(--line); text-align:center; font-size:1.5rem; font-weight:900; letter-spacing:10px; }
        .otp-input:focus { border-color:var(--brand); box-shadow:0 0 0 4px rgba(247,198,0,0.18); }
        .btn-auth { height:50px; border:0; border-radius:8px; background:#111827; color:#fff; font-weight:900; width:100%; }
        .btn-auth:hover { background:#1f2937; color:#fff; }
        .auth-link { color:#0b5fff; font-weight:800; text-decoration:none; }
        .alert { border-radius:8px; font-weight:700; text-align:left; }
    </style>
</head>
<body>
<main class="otp-card">
    <div class="mail-icon"><i class="fas fa-envelope-open-text"></i></div>
    <h1>Xác thực email</h1>
    <p class="sub">
        Mã OTP 6 số đã được gửi đến<br>
        <strong><?php echo isset($_SESSION['temp_email']) ? htmlspecialchars($_SESSION['temp_email'], ENT_QUOTES, 'UTF-8') : ''; ?></strong>
    </p>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'resent'): ?>
        <div class="alert alert-success">Đã gửi lại mã OTP mới. Vui lòng kiểm tra hộp thư.</div>
    <?php elseif(isset($_GET['error']) && $_GET['error'] == 'mail_failed'): ?>
        <div class="alert alert-danger">Không thể gửi email OTP. Hãy kiểm tra cấu hình SMTP trong file `.env`.</div>
    <?php elseif(isset($_GET['error']) && $_GET['error'] == 'invalid_otp'): ?>
        <div class="alert alert-danger">Mã OTP sai hoặc đã hết hạn.</div>
    <?php elseif(isset($_GET['error']) && $_GET['error'] == 'resend_failed'): ?>
        <div class="alert alert-danger">Không thể gửi lại mã lúc này.</div>
    <?php endif; ?>

    <form action="index.php?role=user&controller=auth&action=postVerify" method="POST">
        <div class="mb-4">
            <input type="text" name="otp_code" class="form-control otp-input" maxlength="6" placeholder="------" required autocomplete="off" inputmode="numeric">
        </div>
        <button type="submit" class="btn-auth">Xác nhận OTP</button>
    </form>

    <div class="text-muted small mt-4 fw-semibold">
        Không nhận được mã?
        <a href="index.php?role=user&controller=auth&action=resendOTP" id="resendLink" class="auth-link text-secondary" style="pointer-events:none;">
            Gửi lại sau <span id="timer">60</span>s
        </a>
    </div>
</main>

<script>
let timeLeft = 60;
const timerSpan = document.getElementById('timer');
const resendLink = document.getElementById('resendLink');
const countdown = setInterval(function() {
    timeLeft -= 1;
    timerSpan.textContent = timeLeft;
    if (timeLeft <= 0) {
        clearInterval(countdown);
        resendLink.textContent = 'Gửi lại ngay';
        resendLink.classList.remove('text-secondary');
        resendLink.style.pointerEvents = 'auto';
    }
}, 1000);
</script>
</body>
</html>
