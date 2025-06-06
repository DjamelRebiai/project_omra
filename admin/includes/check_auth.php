<?php
// يجب أن يكون هذا أول شيء في الملف
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/config.php';

// منع حلقة إعادة التوجيه
$current_page = basename($_SERVER['SCRIPT_NAME']);
$login_page = 'login.php';

if (!isset($_SESSION['admin_id']) && $current_page != $login_page) {
    header('Location: ' . BASE_URL . '/admin/views/auth/login.php');
    exit;
}

// تنظيف الجلسة عند الطلب
if (isset($_GET['reset'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/admin/views/auth/login.php');
    exit;
}
?>