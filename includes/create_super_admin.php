<?php
require_once __DIR__ . '/db.php';

// إنشاء المدير العام
$username = 'super_admin';
$password = password_hash('Admin@123', PASSWORD_DEFAULT);
$full_name = 'مدير عام جديد';
$email = 'super_admin@example.com';

try {
    // التحقق من عدم وجود نفس اسم المستخدم
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = $1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        die('اسم المستخدم موجود مسبقاً');
    }

    // إضافة المدير
    $stmt = $pdo->prepare('INSERT INTO admins (username, password, full_name, email) VALUES ($1, $2, $3, $4) RETURNING id');
    $stmt->execute([$username, $password, $full_name, $email]);
    $result = $stmt->fetch();
    $admin_id = $result['id'];

    // إضافة كافة الصلاحيات
    $permissions = ['manage_agencies', 'manage_pilgrims', 'manage_admins', 'manage_offers'];    foreach ($permissions as $perm) {
        $stmt = $pdo->prepare('INSERT INTO admin_permissions (admin_id, permission_key, allow_view, allow_add, allow_edit, allow_delete) VALUES ($1, $2, true, true, true, true)');
        $stmt->execute([$admin_id, $perm]);
    }

    echo "تم إنشاء المدير العام بنجاح<br>";
    echo "اسم المستخدم: $username<br>";
    echo "كلمة المرور: Admin@123<br>";
    echo "<br>يمكنك الآن تسجيل الدخول باستخدام هذه البيانات في صفحة <a href='../admin/login.php'>تسجيل الدخول</a>";
} catch (PDOException $e) {
    echo "حدث خطأ: " . $e->getMessage();
}
