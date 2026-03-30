<?php
// Nhúng thủ công 3 file cốt lõi của thư viện PHPMailer
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public static function sendOTP($toEmail, $otpCode) {
        $mail = new PHPMailer(true);
        try {
            // Cấu hình Server SMTP của Google
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'loanthanh3210q@gmail.com';     // 🔴 SỬA LẠI: Điền Gmail của bạn
            $mail->Password   = 'xgzgnhyjteofqrsr';    // 🔴 SỬA LẠI: Điền Mật khẩu ứng dụng 16 ký tự
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Cấu hình người gửi & nhận
            $mail->setFrom('YOUR_GMAIL@gmail.com', 'He Thong So Sanh Gia'); // 🔴 SỬA LẠI
            $mail->addAddress($toEmail);
            $mail->CharSet = 'UTF-8';

            // Nội dung Email
            $mail->isHTML(true);
            $mail->Subject = 'Mã xác thực OTP - Đăng ký tài khoản';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f7f6;'>
                    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #3498db; text-align: center;'>Xác thực tài khoản</h2>
                        <p>Chào bạn,</p>
                        <p>Bạn vừa yêu cầu đăng ký tài khoản trên Hệ Thống So Sánh Giá. Dưới đây là mã OTP của bạn:</p>
                        <h1 style='text-align: center; color: #e74c3c; font-size: 40px; letter-spacing: 5px; background: #fdf2e9; padding: 15px; border-radius: 8px;'>{$otpCode}</h1>
                        <p style='color: #7f8c8d; font-size: 14px;'>Mã này có hiệu lực trong vòng <strong>5 phút</strong>. Vui lòng không chia sẻ mã này cho bất kỳ ai để bảo mật tài khoản.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #bdc3c7; text-align: center;'>© " . date("Y") . " Hệ Thống So Sánh Giá Đa Sàn</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    // Thêm hàm gửi thông báo giảm giá
    public static function sendPriceAlert($toEmail, $productName, $targetPrice, $currentPrice, $platformName, $productUrl) {
        $mail = new PHPMailer(true);
        try {
            // 1. Cấu hình Server SMTP của Google (Giống hệt hàm sendOTP)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'loanthanh3210q@gmail.com';     // 🔴 SỬA LẠI: Điền Gmail của bạn
            $mail->Password   = 'xgzgnhyjteofqrsr';    // 🔴 SỬA LẠI: Điền 16 ký tự mật khẩu ứng dụng
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('YOUR_GMAIL@gmail.com', 'He Thong So Sanh Gia'); // 🔴 SỬA LẠI
            $mail->addAddress($toEmail);
            $mail->CharSet = 'UTF-8';

            // 2. Nội dung Email Báo Sale
            $mail->isHTML(true);
            $mail->Subject = '🎉 TIN VUI: Sản phẩm bạn theo dõi đã GIẢM GIÁ!';
            
            $formattedTarget = number_format($targetPrice) . 'đ';
            $formattedCurrent = number_format($currentPrice) . 'đ';
            $color = ($platformName == 'Shopee') ? '#ee4d2d' : (($platformName == 'Tiki') ? '#0d6efd' : '#00008b');

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f7f6;'>
                    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; border-top: 5px solid #f1c40f; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #e67e22; text-align: center;'>🔔 ĐÃ ĐẠT MỨC GIÁ KỲ VỌNG!</h2>
                        <p>Chào bạn,</p>
                        <p>Sản phẩm <strong>{$productName}</strong> mà bạn đang theo dõi trên Hệ Thống So Sánh Giá đã chính thức giảm xuống mức giá bạn mong muốn.</p>
                        
                        <div style='background: #fdf2e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <p style='margin: 0 0 10px 0;'>🎯 Mức giá bạn mong đợi: <strong>{$formattedTarget}</strong></p>
                            <p style='margin: 0; font-size: 18px;'>🔥 Giá sốc hiện tại: <strong style='color: #e74c3c; font-size: 24px;'>{$formattedCurrent}</strong></p>
                            <p style='margin: 10px 0 0 0;'>🛒 Sàn đang bán rẻ nhất: <span style='background: {$color}; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px; font-weight: bold;'>{$platformName}</span></p>
                        </div>

                        <div style='text-align: center; margin-top: 30px;'>
                            <a href='{$productUrl}' style='background-color: #e74c3c; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block;'>Đến nơi bán để Chốt Đơn ngay</a>
                        </div>
                        
                        <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0 20px 0;'>
                        <p style='font-size: 12px; color: #bdc3c7; text-align: center;'>Hệ thống sẽ tạm ngừng gửi thông báo cho sản phẩm này cho đến khi bạn đặt lại mức giá mới.</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>