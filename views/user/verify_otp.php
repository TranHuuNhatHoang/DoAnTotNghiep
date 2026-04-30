<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác thực OTP - Hệ Thống So Sánh Giá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 450px; padding: 20px; text-align: center; }
        .otp-input { font-size: 2rem; letter-spacing: 15px; text-align: center; font-weight: bold; border: 2px solid #3498db; }
    </style>
</head>
<body>

<div class="card auth-card">
    <div class="card-body p-4">
        <div class="mb-4 text-warning">
            <i class="fas fa-envelope-open-text fa-4x"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">Xác thực Email</h3>
        <p class="text-muted mb-4">Chúng tôi đã gửi một mã OTP gồm 6 chữ số đến email <br>
            <strong><?php echo isset($_SESSION['temp_email']) ? htmlspecialchars($_SESSION['temp_email']) : ''; ?></strong>
        </p>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'resent'): ?>
            <div class="alert alert-success py-2 small fw-bold"><i class="fas fa-paper-plane me-1"></i> Đã gửi lại mã OTP mới! Vui lòng kiểm tra hộp thư.</div>
        <?php elseif(isset($_GET['error']) && $_GET['error'] == 'mail_failed'): ?>
            <div class="alert alert-danger py-2 small fw-bold"><i class="fas fa-times-circle me-1"></i> Khong the gui email OTP. Hay kiem tra cau hinh SMTP trong file .env.</div>
        <?php elseif(isset($_GET['error']) && $_GET['error'] == 'invalid_otp'): ?>
            <div class="alert alert-danger py-2 small fw-bold"><i class="fas fa-exclamation-circle me-1"></i> Mã OTP sai hoặc đã hết hạn!</div>
        <?php elseif(isset($_GET['error']) && $_GET['error'] == 'resend_failed'): ?>
            <div class="alert alert-danger py-2 small fw-bold"><i class="fas fa-times-circle me-1"></i> Lỗi hệ thống: Không thể gửi lại mã lúc này.</div>
        <?php endif; ?>

        <form action="index.php?role=user&controller=auth&action=postVerify" method="POST">
            <div class="mb-4">
                <input type="text" name="otp_code" class="form-control form-control-lg otp-input rounded-3" maxlength="6" placeholder="------" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm mb-3">
                <i class="fas fa-check-circle me-2"></i> Xác nhận OTP
            </button>
        </form>
        
        <div class="text-muted small">
            Không nhận được mã? 
            <a href="index.php?role=user&controller=auth&action=resendOTP" id="resendLink" class="text-decoration-none fw-bold text-secondary" style="pointer-events: none;">
                Gửi lại (<span id="timer">60</span>s)
            </a>
        </div>

    </div>
</div>

<script>
    // Script đếm ngược 60 giây cho nút Gửi lại
    let timeLeft = 60;
    const timerSpan = document.getElementById('timer');
    const resendLink = document.getElementById('resendLink');

    const countdown = setInterval(function() {
        timeLeft--;
        timerSpan.textContent = timeLeft;

        // Khi đếm ngược về 0
        if (timeLeft <= 0) {
            clearInterval(countdown); // Dừng bộ đếm
            resendLink.innerHTML = "Gửi lại ngay"; // Đổi text
            resendLink.classList.remove('text-secondary'); // Bỏ màu xám
            resendLink.classList.add('text-primary'); // Chuyển sang màu xanh mượt mà
            resendLink.style.pointerEvents = "auto"; // Mở khóa cho phép click
        }
    }, 1000); // Lặp lại mỗi 1000ms (1 giây)
</script>
</body>
</html>
