<?php
// admin/logout.php
$lang = 'ar';
if (isset($_GET['lang']) && $_GET['lang'] === 'en') {
    $lang = 'en';
}
$texts = [
    'ar' => [
        'logout' => 'تسجيل الخروج',
        'logout_msg' => 'تم تسجيل الخروج بنجاح.',
        'login' => 'دخول',
        'lang_switch' => 'English',
    ],
    'en' => [
        'logout' => 'Logout',
        'logout_msg' => 'Logged out successfully.',
        'login' => 'Login',
        'lang_switch' => 'العربية',
    ]
];
$t = $texts[$lang];
session_start();
session_unset();
session_destroy();
?><!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t['logout']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {background: linear-gradient(135deg, #e0e7ff 0%, #f5f6fa 100%); font-family: '<?php echo $lang === 'ar' ? 'Cairo' : 'Roboto'; ?>', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh;}
        .logout-box {background: #fff; padding: 36px 30px; border-radius: 14px; box-shadow: 0 4px 24px #0002; width: 350px; text-align: center;}
        .logout-box h2 {margin-bottom: 20px; color: #1e90ff; font-size: 2em;}
        .logout-box a {display: inline-block; margin-top: 18px; color: #fff; background: #1e90ff; padding: 10px 28px; border-radius: 8px; text-decoration: none; font-size: 18px;}
        .lang-switch {text-align: <?php echo $lang === 'ar' ? 'left' : 'right'; ?>; margin-bottom: 10px;}
        .lang-switch a {color: #1e90ff; text-decoration: none; font-weight: bold;}
    </style>
</head>
<body>
    <div class="logout-box">
        <div class="lang-switch">
            <a href="?lang=<?php echo $lang === 'ar' ? 'en' : 'ar'; ?>"><?php echo $t['lang_switch']; ?></a>
        </div>
        <h2><?php echo $t['logout']; ?></h2>
        <div><?php echo $t['logout_msg']; ?></div>
        <a href="login.php?lang=<?php echo $lang; ?>"><?php echo $t['login']; ?></a>
    </div>
</body>
</html>
