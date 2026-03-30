<?php
// Tắt giới hạn thời gian thực thi (vì có thể phải gửi hàng trăm email)
set_time_limit(0);

// Nạp các thư viện cần thiết
require_once __DIR__ . '/services/MailService.php';

// KẾT NỐI DATABASE TRỰC TIẾP
// 🔴 Lưu ý: Thay đổi port 3307 thành 3306 nếu XAMPP của bạn dùng port mặc định
$conn = new mysqli("127.0.0.1", "root", "", "web_test", 3307); 

if ($conn->connect_error) {
    die("Lỗi kết nối CSDL: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

echo "=================================================\n";
echo "BẮT ĐẦU QUÉT CẢNH BÁO GIÁ (" . date('Y-m-d H:i:s') . ")\n";
echo "=================================================\n\n";

// Câu lệnh SQL "Thần thánh": Ghép 4 bảng lại với nhau để tìm ra những ai được chốt đơn
$sql = "
    SELECT 
        pa.id as alert_id,
        u.email,
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
      AND pl.current_price <= pa.target_price
      AND pl.current_price > 0
    ORDER BY pl.current_price ASC
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Phát hiện " . $result->num_rows . " lượt sản phẩm đạt mức kỳ vọng!\n\n";

    while ($row = $result->fetch_assoc()) {
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
            // Cập nhật is_notified = 1 để ngày mai không spam họ nữa
            $updateStmt = $conn->prepare("UPDATE price_alerts SET is_notified = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $row['alert_id']);
            $updateStmt->execute();
            echo "   [THÀNH CÔNG] Đã gửi mail và cập nhật trạng thái.\n";
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