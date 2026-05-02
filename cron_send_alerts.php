<?php
// Tắt giới hạn thời gian thực thi
set_time_limit(0);

// Nạp các thư viện cần thiết
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/services/MailService.php';

// KẾT NỐI DATABASE
$database = new Database();
$conn = $database->getConnection();

echo "=================================================\n";
echo "BẮT ĐẦU QUÉT CẢNH BÁO GIÁ (" . date('Y-m-d H:i:s') . ")\n";
echo "=================================================\n\n";

$sql = "
    SELECT 
        pa.id as alert_id,
        u.id as user_id,
        u.email,
        p.id as product_id,
        p.name as product_name,
        pa.target_price,
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
      AND pl.current_price <= pa.target_price
      AND pl.current_price > 0
    ORDER BY pl.current_price ASC
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Phát hiện " . $result->num_rows . " lượt khớp giá (bao gồm các sàn bị trùng)...\n\n";

    // 🔴 THÊM MỚI: Mảng ghi sổ các cảnh báo đã xử lý
    $processed_alerts = [];

    while ($row = $result->fetch_assoc()) {
        
        // 🔴 THÊM MỚI: Kiểm tra xem cảnh báo này đã được gửi (cho sàn rẻ hơn) trước đó chưa?
        if (in_array($row['alert_id'], $processed_alerts)) {
            // Nếu đã gửi rồi, bỏ qua dòng này (Bỏ qua các sàn đắt hơn)
            continue;
        }

        echo "-> Đang gửi thông báo cho: " . $row['email'] . " (Sản phẩm: " . $row['product_name'] . ")...\n";
        
        // Gọi hàm gửi Email
        $isSent = MailService::sendPriceAlert(
            $row['email'], 
            $row['product_name'], 
            $row['target_price'], 
            $row['current_price'], 
            $row['platform_name'], 
            $row['product_url']
        );

        if ($isSent) {
            $updateStmt = $conn->prepare("UPDATE price_alerts SET is_notified = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $row['alert_id']);
            $updateStmt->execute();

            $msg = "🎉 Tin vui! Sản phẩm <strong>" . $row['product_name'] . "</strong> đã giảm xuống còn <strong>" . number_format($row['current_price']) . "đ</strong> (Rẻ nhất tại " . $row['platform_name'] . "). Mua ngay!";
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, product_id, message) VALUES (?, ?, ?)");
            $notifStmt->bind_param("iis", $row['user_id'], $row['product_id'], $msg); 
            $notifStmt->execute();

            echo "   [THÀNH CÔNG] Đã gửi mail (Sàn: ".$row['platform_name'].") và tạo thông báo Web.\n";
            
            // 🔴 THÊM MỚI: Ghi sổ lại ID cảnh báo này để các vòng lặp sau không gửi nữa
            $processed_alerts[] = $row['alert_id'];
            
        } else {
            echo "   [THẤT BẠI] Không thể gửi email.\n";
        }
    }
} else {
    echo "Hôm nay không có sản phẩm nào giảm giá đạt kỳ vọng.\n";
}

echo "\n=================================================\n";
echo "HOÀN THÀNH QUÉT CẢNH BÁO GIÁ!\n";
echo "=================================================\n";

$conn->close();
?>
