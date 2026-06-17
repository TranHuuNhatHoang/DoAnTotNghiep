<?php
set_time_limit(0);
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/services/MailService.php';

function alert_log($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
}

function alert_money($value) {
    return number_format((float) $value, 0, ',', '.') . 'đ';
}

function alert_safe($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$database = new Database();
$conn = $database->getConnection();

alert_log('Bat dau quet canh bao gia');

$pendingResult = $conn->query("SELECT COUNT(*) AS total FROM price_alerts WHERE is_notified = 0");
$pendingAlerts = $pendingResult ? (int) ($pendingResult->fetch_assoc()['total'] ?? 0) : 0;
alert_log("So canh bao dang cho: {$pendingAlerts}");

$sql = "
    SELECT
        pa.id AS alert_id,
        u.id AS user_id,
        u.email,
        p.id AS product_id,
        p.name AS product_name,
        pa.target_price,
        pl.id AS link_id,
        pl.current_price,
        pl.platform_name,
        pl.product_url
    FROM price_alerts pa
    JOIN users u ON pa.user_id = u.id
    JOIN products p ON pa.product_id = p.id
    JOIN platform_links pl ON pa.product_id = pl.product_id
    WHERE pa.is_notified = 0
      AND pl.is_active = 1
      AND pl.status = 1
      AND pl.availability_status = 'active'
      AND pl.current_price > 0
      AND pl.current_price <= pa.target_price
    ORDER BY pa.id ASC, pl.current_price ASC, pl.id ASC
";

$result = $conn->query($sql);
if (!$result) {
    alert_log('Loi truy van canh bao gia: ' . $conn->error);
    $conn->close();
    exit(1);
}

$processedAlerts = [];
$matchedAlerts = 0;
$webNotificationsCreated = 0;
$emailsSent = 0;
$emailsFailed = 0;

$insertNotification = $conn->prepare("
    INSERT INTO notifications (user_id, product_id, message)
    VALUES (?, ?, ?)
");
$markAlertNotified = $conn->prepare("
    UPDATE price_alerts
    SET is_notified = 1
    WHERE id = ? AND is_notified = 0
");

if (!$insertNotification || !$markAlertNotified) {
    alert_log('Khong the chuan bi truy van notification: ' . $conn->error);
    $conn->close();
    exit(1);
}

while ($row = $result->fetch_assoc()) {
    $alertId = (int) $row['alert_id'];
    if (isset($processedAlerts[$alertId])) {
        continue;
    }
    $processedAlerts[$alertId] = true;
    $matchedAlerts++;

    $userId = (int) $row['user_id'];
    $productId = (int) $row['product_id'];
    $currentPrice = (float) $row['current_price'];
    $targetPrice = (float) $row['target_price'];
    $platformName = (string) $row['platform_name'];
    $productName = (string) $row['product_name'];

    alert_log(
        'Canh bao dat nguong: alert_id=' . $alertId .
        ', user=' . $row['email'] .
        ', product_id=' . $productId .
        ', platform=' . $platformName .
        ', current=' . (int) $currentPrice .
        ', target=' . (int) $targetPrice
    );

    $message = "🎉 Tin vui! Sản phẩm <strong>" . alert_safe($productName) .
        "</strong> đã chạm mức <strong>" . alert_money($currentPrice) .
        "</strong> tại " . alert_safe($platformName) .
        ". Mức giá bạn đặt là <strong>" . alert_money($targetPrice) . "</strong>.";

    $conn->begin_transaction();
    try {
        $insertNotification->bind_param("iis", $userId, $productId, $message);
        if (!$insertNotification->execute()) {
            throw new Exception($insertNotification->error);
        }

        $markAlertNotified->bind_param("i", $alertId);
        if (!$markAlertNotified->execute()) {
            throw new Exception($markAlertNotified->error);
        }

        $conn->commit();
        $webNotificationsCreated++;
        alert_log('Da tao thong bao web va danh dau alert da xu ly.');
    } catch (Exception $e) {
        $conn->rollback();
        alert_log('Loi tao thong bao web cho alert_id=' . $alertId . ': ' . $e->getMessage());
        continue;
    }

    $mailSent = MailService::sendPriceAlert(
        $row['email'],
        $productName,
        $targetPrice,
        $currentPrice,
        $platformName,
        $row['product_url']
    );

    if ($mailSent) {
        $emailsSent++;
        alert_log('Da gui email canh bao gia thanh cong.');
    } else {
        $emailsFailed++;
        alert_log('Gui email canh bao gia that bai, thong bao web van da duoc tao.');
    }
}

if ($matchedAlerts === 0) {
    alert_log('Khong co canh bao nao dat nguong trong lan quet nay.');
}

alert_log("Tong ket: pending={$pendingAlerts}, matched={$matchedAlerts}, web_notifications={$webNotificationsCreated}, emails_sent={$emailsSent}, emails_failed={$emailsFailed}");
alert_log('Hoan thanh quet canh bao gia');

$conn->close();
?>
