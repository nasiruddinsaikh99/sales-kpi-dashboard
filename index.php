<?php
session_start();

require_once __DIR__ . '/config/database.php';

// Router Logic
// Base Path: /sales-kpi-dashboard
$basePath = '/sales-kpi-dashboard';

// Get Current URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove Base Path from URI to get the internal route
if (strpos($requestUri, $basePath) === 0) {
    $uri = substr($requestUri, strlen($basePath));
} else {
    $uri = $requestUri;
}

$uri = rtrim($uri, '/');

// Default Route
if ($uri === '' || $uri === '/index.php') {
    $role = $_SESSION['user_role'] ?? null;
    if ($role === 'admin') {
        header("Location: $basePath/admin/dashboard");
    } elseif ($role === 'agent') {
        header("Location: $basePath/agent/dashboard");
    } else {
        header("Location: $basePath/login");
    }
    exit;
}

// Route Mapping
$routes = [
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/admin/dashboard' => ['controller' => 'AdminController', 'action' => 'dashboard'],
    '/admin/upload' => ['controller' => 'UploadController', 'action' => 'index'],
    '/admin/upload/process' => ['controller' => 'UploadController', 'action' => 'upload'],
    '/admin/upload/delete' => ['controller' => 'AdminController', 'action' => 'deleteUpload'],
    '/admin/records' => ['controller' => 'AdminController', 'action' => 'records'],
    '/admin/settings' => ['controller' => 'AdminController', 'action' => 'settings'],
    '/admin/settings/update' => ['controller' => 'AdminController', 'action' => 'updateSettings'],
    '/admin/communications' => ['controller' => 'CommunicationController', 'action' => 'adminIndex'],
    '/admin/communications/store' => ['controller' => 'CommunicationController', 'action' => 'store'],
    '/admin/communications/delete' => ['controller' => 'CommunicationController', 'action' => 'delete'],
    '/agent/dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    '/agent/communications' => ['controller' => 'CommunicationController', 'action' => 'agentIndex'],
    '/communications/download' => ['controller' => 'CommunicationController', 'action' => 'downloadFile'],
    '/communications/unread-count' => ['controller' => 'CommunicationController', 'action' => 'getUnreadCount'],
];

if (array_key_exists($uri, $routes)) {
    $route = $routes[$uri];
    $controllerName = $route['controller'];
    $actionName = $route['action'];

    $controllerFile = __DIR__ . '/src/Controllers/' . $controllerName . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->$actionName();
    } else {
        http_response_code(404);
        echo "Controller not found. URI: $uri";
    }
} else {
    // 404
    http_response_code(404);
    echo "404 Not Found. You requested: $requestUri (Internal: $uri)";
}
