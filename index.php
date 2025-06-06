<?php
require_once 'config/config.php';

// توجيه المستخدم إلى صفحة تسجيل الدخول
$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
header('Location: ' . rtrim(BASE_URL, '/') . '/admin/views/auth/login.php?lang=' . $lang);
exit;
