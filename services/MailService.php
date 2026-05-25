<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    private static function configureMailer(PHPMailer $mail) {
        $username = AppEnv::get('MAIL_USERNAME');
        $password = AppEnv::get('MAIL_PASSWORD');

        if (!$username || !$password) {
            error_log('Mail config is missing MAIL_USERNAME or MAIL_PASSWORD.');
            return false;
        }

        $mail->isSMTP();
        $mail->Host = AppEnv::get('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->Port = (int) AppEnv::get('MAIL_PORT', 465);
        $mail->CharSet = 'UTF-8';

        $encryption = strtolower(AppEnv::get('MAIL_ENCRYPTION', 'smtps'));
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'smtps' || $encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->setFrom(
            AppEnv::get('MAIL_FROM_ADDRESS', $username),
            AppEnv::get('MAIL_FROM_NAME', 'He Thong So Sanh Gia')
        );

        return true;
    }

    public static function sendOTP($toEmail, $otpCode) {
        $mail = new PHPMailer(true);

        try {
            if (!self::configureMailer($mail)) {
                return false;
            }

            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Ma xac thuc OTP - Dang ky tai khoan';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f7f6;'>
                    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #3498db; text-align: center;'>Xac thuc tai khoan</h2>
                        <p>Chao ban,</p>
                        <p>Ban vua yeu cau dang ky tai khoan tren He Thong So Sanh Gia. Duoi day la ma OTP cua ban:</p>
                        <h1 style='text-align: center; color: #e74c3c; font-size: 40px; letter-spacing: 5px; background: #fdf2e9; padding: 15px; border-radius: 8px;'>{$otpCode}</h1>
                        <p style='color: #7f8c8d; font-size: 14px;'>Ma nay co hieu luc trong vong <strong>5 phut</strong>. Vui long khong chia se ma nay cho bat ky ai.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #bdc3c7; text-align: center;'>&copy; " . date("Y") . " He Thong So Sanh Gia Da San</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Send OTP email failed: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendPasswordResetOtp($toEmail, $otpCode) {
        $mail = new PHPMailer(true);

        try {
            if (!self::configureMailer($mail)) {
                return false;
            }

            $safeOtp = htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8');

            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Ma OTP dat lai mat khau - SmartPrice';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f7f6;'>
                    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #111827; text-align: center;'>Dat lai mat khau</h2>
                        <p>Chao ban,</p>
                        <p>Ma OTP dat lai mat khau SmartPrice cua ban la:</p>
                        <h1 style='text-align: center; color: #e74c3c; font-size: 40px; letter-spacing: 5px; background: #fdf2e9; padding: 15px; border-radius: 8px;'>{$safeOtp}</h1>
                        <p style='color: #7f8c8d; font-size: 14px;'>Ma nay co hieu luc trong vong <strong>15 phut</strong>. Vui long khong chia se ma nay cho bat ky ai.</p>
                        <p style='color: #7f8c8d; font-size: 14px;'>Neu ban khong yeu cau dat lai mat khau, hay bo qua email nay.</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Send password reset OTP email failed: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendPriceAlert($toEmail, $productName, $targetPrice, $currentPrice, $platformName, $productUrl) {
        $mail = new PHPMailer(true);

        try {
            if (!self::configureMailer($mail)) {
                return false;
            }

            $safeProductName = htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');
            $safePlatformName = htmlspecialchars($platformName, ENT_QUOTES, 'UTF-8');
            $safeProductUrl = htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8');
            $formattedTarget = number_format((float) $targetPrice) . 'd';
            $formattedCurrent = number_format((float) $currentPrice) . 'd';
            $color = ($platformName === 'Shopee') ? '#ee4d2d' : (($platformName === 'Tiki') ? '#0d6efd' : '#00008b');

            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Tin vui: San pham ban theo doi da giam gia';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f7f6;'>
                    <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; border-top: 5px solid #f1c40f; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                        <h2 style='color: #e67e22; text-align: center;'>Da dat muc gia ky vong</h2>
                        <p>Chao ban,</p>
                        <p>San pham <strong>{$safeProductName}</strong> da giam xuong muc gia ban mong muon.</p>
                        <div style='background: #fdf2e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <p style='margin: 0 0 10px 0;'>Muc gia ban mong doi: <strong>{$formattedTarget}</strong></p>
                            <p style='margin: 0; font-size: 18px;'>Gia hien tai: <strong style='color: #e74c3c; font-size: 24px;'>{$formattedCurrent}</strong></p>
                            <p style='margin: 10px 0 0 0;'>San dang ban re nhat: <span style='background: {$color}; color: white; padding: 3px 10px; border-radius: 15px; font-size: 12px; font-weight: bold;'>{$safePlatformName}</span></p>
                        </div>
                        <div style='text-align: center; margin-top: 30px;'>
                            <a href='{$safeProductUrl}' style='background-color: #e74c3c; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; display: inline-block;'>Den noi ban</a>
                        </div>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Send price alert email failed: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
?>
