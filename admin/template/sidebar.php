<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
if (!isset($t)) {
    $texts_template = [
        'ar' => [
            'dashboard' => 'لوحة التحكم',
            'agencies' => 'إدارة الوكالات',
            'pilgrims' => 'إدارة المعتمرين',
            'admins' => 'إدارة المدراء',
            'offers' => 'إدارة العروض',
            'logout' => 'تسجيل الخروج'
        ],
        'fr' => [
            'dashboard' => 'Tableau de bord',
            'agencies' => 'Gestion des agences',
            'pilgrims' => 'Gestion des pèlerins',
            'admins' => 'Gestion des administrateurs',
            'offers' => 'Gestion des offres',
            'logout' => 'Déconnexion'
        ]
    ];
    
    $lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
    $t = array_merge($texts_template[$lang], $t ?? []);
} else {
    // إضافة النصوص المطلوبة إذا لم تكن موجودة
    if (!isset($t['dashboard'])) $t['dashboard'] = $lang === 'ar' ? 'لوحة التحكم' : 'Tableau de bord';
    if (!isset($t['agencies'])) $t['agencies'] = $lang === 'ar' ? 'إدارة الوكالات' : 'Gestion des agences';
    if (!isset($t['pilgrims'])) $t['pilgrims'] = $lang === 'ar' ? 'إدارة المعتمرين' : 'Gestion des pèlerins';
    if (!isset($t['admins'])) $t['admins'] = $lang === 'ar' ? 'إدارة المدراء' : 'Gestion des administrateurs';
    if (!isset($t['offers'])) $t['offers'] = $lang === 'ar' ? 'إدارة العروض' : 'Gestion des offres';
    if (!isset($t['logout'])) $t['logout'] = $lang === 'ar' ? 'تسجيل الخروج' : 'Déconnexion';
}

// تحديد الصفحة الحالية
$current_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$current_page = basename($current_script, '.php');

// استخدام BASE_URL في الروابط
$dashboard_url = BASE_URL . '/admin/dashboard.php';
$agencies_url = BASE_URL . '/admin/views/agencies/index.php';
$pilgrims_url = BASE_URL . '/admin/views/pilgrims/index.php';
$admins_url = BASE_URL . '/admin/views/admins/index.php';
$offers_url = BASE_URL . '/admin/views/offers/index.php';
$logout_url = BASE_URL . '/admin/logout.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang ?? 'ar'; ?>" dir="<?php echo ($lang ?? 'ar') === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'لوحة التحكم'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e90ff;
            --sidebar-width: 280px;
            --header-height: 60px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: <?php echo ($lang ?? 'ar') === 'ar' ? "'Cairo'" : "'Roboto'"; ?>, sans-serif;
            background: #f4f6fa;
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            <?php echo ($lang ?? 'ar') === 'ar' ? 'right' : 'left'; ?>: 0;
            height: 100vh;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            background: var(--primary-color);
            color: #fff;
        }
        .sidebar-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
        }
        .nav-links {
            padding: 20px 0;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            border-<?php echo ($lang ?? 'ar') === 'ar' ? 'right' : 'left'; ?>: 3px solid transparent;
        }
        .nav-link.active {
            background: #e6f2ff;
            color: var(--primary-color);
            border-<?php echo ($lang ?? 'ar') === 'ar' ? 'right' : 'left'; ?>-color: var(--primary-color);
        }
        .nav-link:hover {
            background: #f0f7ff;
        }
        .nav-link svg {
            width: 20px;
            height: 20px;
            margin-<?php echo ($lang ?? 'ar') === 'ar' ? 'left' : 'right'; ?>: 10px;
        }
        .main-content {
            flex: 1;
            margin-<?php echo ($lang ?? 'ar') === 'ar' ? 'right' : 'left'; ?>: var(--sidebar-width);
            padding: 20px;
        }
        .page-header {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .page-header h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .lang-switch {
            margin-top: 10px;
        }
        .lang-switch a {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(<?php echo ($lang ?? 'ar') === 'ar' ? '100%' : '-100%'; ?>);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-<?php echo ($lang ?? 'ar') === 'ar' ? 'right' : 'left'; ?>: 0;
            }
            .toggle-sidebar {
                display: block;
                position: fixed;
                top: 10px;
                <?php echo ($lang ?? 'ar') === 'ar' ? 'right' : 'left'; ?>: 10px;
                z-index: 1001;
                padding: 10px;
                background: var(--primary-color);
                border: none;
                border-radius: 4px;
                color: #fff;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>
    <button class="toggle-sidebar" onclick="document.querySelector('.sidebar').classList.toggle('active')" style="display: none;">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 12h18M3 6h18M3 18h18"/>
        </svg>
    </button>
    
    <div class="sidebar">
        <div class="sidebar-header">
            <h1><?php echo $lang === 'ar' ? 'لوحة التحكم' : 'Admin Panel'; ?></h1>
        </div>
        <nav class="nav-links">
            <a href="<?php echo $dashboard_url; ?>?lang=<?php echo $lang; ?>" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <?php echo $t['dashboard']; ?>
            </a>
            <?php if ($can_agencies ?? true): ?>
            <a href="<?php echo $agencies_url; ?>?lang=<?php echo $lang; ?>" class="nav-link <?php echo $current_page === 'manage_agencies' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <?php echo $t['agencies']; ?>
            </a>
            <?php endif; ?>
            <?php if ($can_pilgrims ?? true): ?>
            <a href="<?php echo $pilgrims_url; ?>?lang=<?php echo $lang; ?>" class="nav-link <?php echo $current_page === 'manage_pilgrims' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <?php echo $t['pilgrims']; ?>
            </a>
            <?php endif; ?>
            <?php if ($can_admins ?? true): ?>
            <a href="<?php echo $admins_url; ?>?lang=<?php echo $lang; ?>" class="nav-link <?php echo $current_page === 'manage_admins' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <?php echo $t['admins']; ?>
            </a>
            <?php endif; ?>
            <a href="<?php echo $logout_url; ?>" class="nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <?php echo $t['logout']; ?>
            </a>
        </nav>
    </div>
    <main class="main-content">
        <div class="page-header">
            <h2><?php echo $page_title ?? 'لوحة التحكم'; ?></h2>
            <div class="lang-switch">
                <a href="?lang=<?php echo ($lang ?? 'ar') === 'ar' ? 'fr' : 'ar'; ?>">
                    <?php echo ($lang ?? 'ar') === 'ar' ? 'Français' : 'العربية'; ?>
                </a>
            </div>
        </div>
        <div class="content">
