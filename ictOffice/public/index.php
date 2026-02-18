<?php
$app_root = dirname(__DIR__, 2);
if (!defined('BASE_URL')) { require_once $app_root . '/auth/path_config_loader.php'; }
require_once $app_root . '/auth/session_init.php';
require_once $app_root . '/auth/admin_helper.php';
require_once $app_root . '/auth/student_access.php';

$page = $_GET['page'] ?? 'dashboard';
$isAdmin = isAdminLoggedIn();

if ($page !== 'logbook' && !$isAdmin) {
    require_once $app_root . '/auth/check_admin_access.php';
}

if ($page === 'logbook') {
    require_once __DIR__ . '/../views/logbook.php';
    exit;
}

require_once __DIR__ . '/../header_unified.php';

switch ($page) {
    case 'home':
    case 'dashboard':
        require_once __DIR__ . '/../views/dashboard.php';
        break;
    case 'inventory':
        require_once __DIR__ . '/../views/inventory.php';
        break;
    case 'categories':
        require_once __DIR__ . '/../views/category.php';
        break;
    case 'locations':
        require_once __DIR__ . '/../views/location.php';
        break;
    case 'users':
        require_once __DIR__ . '/../views/users.php';
        break;  
    case 'logbook':
        require_once __DIR__ . '/../views/logbook.php';
        break;
    case 'logbook-records':
        require_once __DIR__ . '/../views/logbook-records.php';
        break;
    case 'reports':
        require_once __DIR__ . '/../views/reports.php';
        break;
    case 'logbook-report':
        require_once __DIR__ . '/../views/logbook-report.php';
        break;
    case 'login':
        $ict_public = (rtrim(BASE_URL, '/') === '' ? '' : rtrim(BASE_URL, '/')) . '/ictOffice/public';
        header("Location: " . $ict_public . "/?page=dashboard");
        exit;
        break;
    case 'logout':
        require_once __DIR__ . '/../views/logout.php';
        break;

    default:
        http_response_code(404);
        echo "Page not found";
}

require_once __DIR__ . '/../footer_unified.php';
