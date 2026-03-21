<?php
/**
 * DO AN TOT NGHIEP - ENTRY POINT (ROUTER)
 * Cấu trúc URL: index.php?role=admin&controller=dashboard&action=index
 */

// 1. Bật báo cáo lỗi để phục vụ việc lập trình (Sẽ tắt khi hoàn thành đồ án)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Nạp file cấu hình hệ thống
require_once 'config/database.php';

// 3. Phân tích các tham số điều hướng từ URL
// Mặc định role là user, controller là Product, action là index (Trang chủ người dùng)
$role           = isset($_GET['role']) ? strtolower($_GET['role']) : 'user';
$controllerName = isset($_GET['controller']) ? ucfirst(strtolower($_GET['controller'])) . 'Controller' : 'ProductController';
$actionName     = isset($_GET['action']) ? $_GET['action'] : 'index';

// 4. Xác định đường dẫn đến file Controller dựa trên Role (admin hoặc user)
$controllerPath = "controllers/" . $role . "/" . $controllerName . ".php";

// 5. Kiểm tra file Controller có tồn tại thực tế không
if (file_exists($controllerPath)) {
    require_once $controllerPath;
    
    // Khởi tạo kết nối Database dùng chung
    $database = new Database();
    $db = $database->getConnection();

    // Khởi tạo Object của Controller (VD: new DashboardController($db))
    if (class_exists($controllerName)) {
        $controllerObject = new $controllerName($db);

        // Kiểm tra xem hàm (Action) có tồn tại trong Controller đó không
        if (method_exists($controllerObject, $actionName)) {
            // Lấy thêm tham số ID nếu có (phục vụ trang chi tiết, xóa, sửa...)
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            
            // THỰC THI: Gọi hàm xử lý
            $controllerObject->$actionName($id);
        } else {
            die("Critical Error: Action <strong>$actionName</strong> không tồn tại trong class <strong>$controllerName</strong>.");
        }
    } else {
        die("Critical Error: Class <strong>$controllerName</strong> không tìm thấy trong file.");
    }
} else {
    // Nếu truy cập sai Role hoặc Controller, báo lỗi 404 chuyên nghiệp
    echo "<div style='text-align:center; margin-top:100px;'>
            <h1>404 - KHÔNG TÌM THẤY TRANG</h1>
            <p>Đường dẫn: <i>$controllerPath</i> không tồn tại.</p>
            <a href='index.php'>Quay lại Trang Chủ</a>
          </div>";
}
?>