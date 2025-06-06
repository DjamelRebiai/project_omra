<?php
$config = [
    'host' => 'aws-0-eu-west-3.pooler.supabase.com',
    'dbname' => 'postgres',
    'username' => 'postgres.zrwtxvybdxphylsvjopi',
    'password' => 'Dj123456789.',
    'port' => 6543,
    'sslmode' => 'require'
];

try {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};sslmode={$config['sslmode']}";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // في الإنتاج لا تطبع أي رسالة نجاح
    // echo "تم الاتصال بقاعدة البيانات بنجاح!";
} catch (PDOException $e) {
    exit("خطأ في الاتصال: " . $e->getMessage());
}
?>
