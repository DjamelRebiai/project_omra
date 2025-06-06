<?php
// إظهار جميع الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>صفحة اختبار</h1>";
echo "<pre>";
echo "المسار الحالي: " . $_SERVER['PHP_SELF'] . "\n";
echo "URI الكامل: " . $_SERVER['REQUEST_URI'] . "\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "معلومات الجلسة: \n";
print_r($_SESSION);
echo "</pre>";
?>
