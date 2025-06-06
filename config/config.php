<?php
// تعريف المسار الأساسي للتطبيق
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/pr_job/version1');

// إعدادات التصحيح (لبيئة التطوير فقط)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق مما إذا كانت الجلسة غير نشطة قبل ضبط إعدادات الكوكيز
if (session_status() === PHP_SESSION_NONE) {
    // إعدادات كوكيز الجلسة
    $cookieParams = [
        'lifetime' => 86400, // 24 ساعة
        'path' => '/pr_job/version1',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'secure' => isset($_SERVER['HTTPS']), // سيصبح true تلقائياً إذا كان HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    
    session_set_cookie_params($cookieParams);
    
    // بدء الجلسة
    session_start();
}

// إعدادات إضافية للتطبيق يمكن إضافتها هنا
?>