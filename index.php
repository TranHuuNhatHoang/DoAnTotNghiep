<?php
/**
 * DO AN TOT NGHIEP - ENTRY POINT (ROUTER)
 * URL: index.php?role=admin&controller=dashboard&action=index
 */

require_once 'config/database.php';

$debug = AppEnv::bool('APP_DEBUG', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

$sessionSavePath = AppEnv::get('SESSION_SAVE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions');
if ($sessionSavePath && !is_dir($sessionSavePath)) {
    mkdir($sessionSavePath, 0775, true);
}
if ($sessionSavePath && is_dir($sessionSavePath) && is_writable($sessionSavePath)) {
    session_save_path($sessionSavePath);
}

$routes = [
    'user' => [
        'product' => [
            'class' => 'ProductController',
            'file' => 'controllers/user/ProductController.php',
            'actions' => [
                'index' => 'index',
                'search' => 'search',
                'detail' => 'detail',
                'setalert' => 'setAlert',
                'removealert' => 'removeAlert',
                'readnotification' => 'readNotification',
                'myalerts' => 'myAlerts',
                'suggest' => 'suggest',
            ],
        ],
        'auth' => [
            'class' => 'AuthController',
            'file' => 'controllers/user/AuthController.php',
            'actions' => [
                'login' => 'login',
                'register' => 'register',
                'postregister' => 'postRegister',
                'verify' => 'verify',
                'postverify' => 'postVerify',
                'resendotp' => 'resendOTP',
                'postlogin' => 'postLogin',
                'logout' => 'logout',
            ],
        ],
    ],
    'admin' => [
        'dashboard' => [
            'class' => 'DashboardController',
            'file' => 'controllers/admin/DashboardController.php',
            'actions' => [
                'index' => 'index',
                'alerts' => 'alerts',
            ],
        ],
        'adminproduct' => [
            'class' => 'AdminProductController',
            'file' => 'controllers/admin/AdminProductController.php',
            'actions' => [
                'index' => 'index',
                'add' => 'add',
                'update' => 'update',
                'delete' => 'delete',
            ],
        ],
        'admincategory' => [
            'class' => 'AdminCategoryController',
            'file' => 'controllers/admin/AdminCategoryController.php',
            'actions' => [
                'index' => 'index',
                'add' => 'add',
                'update' => 'update',
                'delete' => 'delete',
            ],
        ],
        'adminplatform' => [
            'class' => 'AdminPlatformController',
            'file' => 'controllers/admin/AdminPlatformController.php',
            'actions' => [
                'index' => 'index',
                'add' => 'add',
                'update' => 'update',
                'delete' => 'delete',
            ],
        ],
        'bot' => [
            'class' => 'BotController',
            'file' => 'controllers/admin/BotController.php',
            'actions' => [
                'index' => 'index',
                'run' => 'run',
            ],
        ],
    ],
];

function render_error_page($statusCode, $title, $message) {
    http_response_code($statusCode);
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo "<div style='text-align:center; margin-top:100px; font-family:Arial,sans-serif;'>
            <h1>{$safeTitle}</h1>
            <p>{$safeMessage}</p>
            <a href='index.php'>Quay lai Trang Chu</a>
          </div>";
    exit();
}

$role = strtolower($_GET['role'] ?? 'user');
$defaultController = ($role === 'admin') ? 'dashboard' : 'product';
$controllerKey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['controller'] ?? $defaultController));
$actionKey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action'] ?? 'index'));

if (!isset($routes[$role], $routes[$role][$controllerKey])) {
    render_error_page(404, '404 - Khong tim thay trang', 'Role hoac controller khong hop le.');
}

$route = $routes[$role][$controllerKey];
if (!isset($route['actions'][$actionKey])) {
    render_error_page(404, '404 - Khong tim thay action', 'Action khong nam trong danh sach duoc phep.');
}

if (!is_readable($route['file'])) {
    render_error_page(500, 'Loi he thong', 'Khong tim thay file controller.');
}

require_once $route['file'];

$controllerName = $route['class'];
$actionName = $route['actions'][$actionKey];

if (!class_exists($controllerName)) {
    render_error_page(500, 'Loi he thong', 'Class controller khong ton tai.');
}

$database = new Database();
$db = $database->getConnection();
$controllerObject = new $controllerName($db);

if (!method_exists($controllerObject, $actionName)) {
    render_error_page(500, 'Loi he thong', 'Method controller khong ton tai.');
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$method = new ReflectionMethod($controllerObject, $actionName);

if ($method->getNumberOfParameters() > 0) {
    $controllerObject->$actionName($id);
} else {
    $controllerObject->$actionName();
}
?>
