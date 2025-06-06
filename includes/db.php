<?php
// includes/db.php
// إعداد متغيرات الاتصال بقاعدة بيانات Supabase (PostgreSQL)
$host = 'aws-0-eu-west-3.pooler.supabase.com';
$db   = 'postgres';
$user = 'postgres.zrwtxvybdxphylsvjopi';
$pass = 'Dj123456789.';
$port = '6543';

// إنشاء DSN لاتصال PDO مع تفعيل SSL
$dsn = "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$pass;sslmode=require";

try {
    $pdo = new PDO($dsn);
    // تحقق من الاتصال (رسالة مؤقتة)
    // echo "تم الاتصال بقاعدة البيانات بنجاح!";
} catch (PDOException $e) {
    exit('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
