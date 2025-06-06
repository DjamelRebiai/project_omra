<?php
// يجب أن يكون هذا أول شيء في الملف
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/db.php';

// إضافة دعم اللغة
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'fr']) ? $_GET['lang'] : 'ar';
$t = [
    'ar' => [
        'lang_switch' => 'Français',
        'login_title' => 'تسجيل الدخول',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'login' => 'دخول'
    ],
    'fr' => [
        'lang_switch' => 'العربية',
        'login_title' => 'Connexion',
        'username' => "Nom d'utilisateur",
        'password' => 'Mot de passe',
        'login' => 'Se connecter'
    ]
][$lang];

// منع حلقة إعادة التوجيه
$current_page = basename($_SERVER['SCRIPT_NAME']);
if (isset($_SESSION['admin_id']) && $current_page != 'dashboard.php') {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/admin/views/auth/login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "يرجى إدخال اسم المستخدم وكلمة المرور";
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $error = "اسم المستخدم غير صحيح";
        } elseif (!password_verify($password, $admin['password'])) {
            $error = "كلمة المرور غير صحيحة";
        } else {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_permissions'] = []; // يمكنك إضافة الصلاحيات هنا
            
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f5f6fa 100%);
            font-family: <?php echo $lang === 'ar' ? "'Cairo'" : "'Roboto'"; ?>, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e7ff;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .submit-btn:hover {
            background: #2980b9;
        }
        .error-message {
            background: #fee;
            color: #e74c3c;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .lang-switch {
            text-align: <?php echo $lang === 'ar' ? 'left' : 'right'; ?>;
            margin-bottom: 20px;
        }
        .lang-switch a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .lang-switch a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="lang-switch">
            <a href="?lang=<?php echo $lang === 'ar' ? 'fr' : 'ar'; ?>">
                <?php echo $t['lang_switch']; ?>
            </a>
        </div>
        <div class="login-header">
            <h1><?php echo $t['login_title']; ?></h1>
        </div>
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username"><?php echo $t['username']; ?></label>
                <input type="text" id="username" name="username" placeholder="<?php echo $t['username']; ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password"><?php echo $t['password']; ?></label>
                <input type="password" id="password" name="password" placeholder="<?php echo $t['password']; ?>" required>
            </div>
            <button type="submit" class="submit-btn">
                <?php echo $t['login']; ?>
            </button>
        </form>
    </div>
</body>
</html>