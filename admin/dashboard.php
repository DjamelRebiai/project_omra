<?php
// يجب أن يكون هذا أول شيء في الملف
if (session_status() === PHP_SESSION_NONE) {
    // إعدادات الجلسة هنا بدلاً من config.php
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/pr_job/version1',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => false, // true إذا كنت تستخدم HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// تحميل ملف التهيئة
$configPath = __DIR__ . '/../config/config.php';
if (!file_exists($configPath)) {
    die('ملف التهيئة غير موجود في المسار المحدد: ' . $configPath);
}
require_once $configPath;

// تحميل ملف التحقق من المصادقة
$checkAuthPath = __DIR__ . '/includes/check_auth.php';
if (!file_exists($checkAuthPath)) {
    die('ملف التحقق من المصادقة غير موجود: ' . $checkAuthPath);
}
require_once $checkAuthPath;

require_once __DIR__ . '/../includes/db.php';

// جلب الإحصائيات من قاعدة البيانات
try {
    // عدد الوكالات
    $stmt = $pdo->query("SELECT COUNT(*) FROM agencies");
    $total_agencies = $stmt->fetchColumn();

    // عدد المعتمرين
    $stmt = $pdo->query("SELECT COUNT(*) FROM pilgrims");
    $total_pilgrims = $stmt->fetchColumn();

    // عدد العروض النشطة
    $stmt = $pdo->query("SELECT COUNT(*) FROM offers WHERE type = 'standard' OR type = 'golden'");
    $total_offers = $stmt->fetchColumn();

    // عدد الطلبات المعلقة
    $stmt = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'");
    $pending_requests = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_agencies = $total_pilgrims = $total_offers = $pending_requests = 0;
}

// إعداد اللغة
$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$translations = [
    'ar' => [
        'welcome' => 'مرحباً',
        'dashboard' => 'لوحة التحكم',
        'logout' => 'تسجيل الخروج',
        'quick_links' => 'روابط سريعة',
        'admins' => 'المدراء',
        'agencies' => 'الوكالات',
        'pilgrims' => 'المعتمرون',
        'offers' => 'العروض'
    ],
    'fr' => [
        'welcome' => 'Bienvenue',
        'dashboard' => 'Tableau de bord',
        'logout' => 'Déconnexion',
        'quick_links' => 'Liens rapides',
        'admins' => 'Administrateurs',
        'agencies' => 'Agences',
        'pilgrims' => 'Pèlerins',
        'offers' => 'Offres'
    ]
];
$t = $translations[$lang];
$page_title = $t['dashboard'];
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم منصة العمرة</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            --danger-gradient: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            --dark-bg: #1a1d29;
            --card-bg: #ffffff;
            --sidebar-bg: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-color: #e2e8f0;
            --shadow-light: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-heavy: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-heavy);
        }

        .sidebar-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .logo i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #667eea;
        }

        .nav-item i {
            margin-left: 1rem;
            font-size: 1.2rem;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-right: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0.05;
            z-index: 1;
        }

        .header-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text h1 {
            color: var(--text-primary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-text p {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-light);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            z-index: 1;
        }

        .stat-card.primary::before { background: var(--primary-gradient); }
        .stat-card.success::before { background: var(--success-gradient); }
        .stat-card.warning::before { background: var(--warning-gradient); }
        .stat-card.danger::before { background: var(--danger-gradient); }

        .stat-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-info h3 {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-icon {
            font-size: 3rem;
            opacity: 0.3;
        }

        .stat-card.primary .stat-icon { color: #667eea; }
        .stat-card.success .stat-icon { color: #4facfe; }
        .stat-card.warning .stat-icon { color: #fcb69f; }
        .stat-card.danger .stat-icon { color: #ff9a9e; }

        /* Quick Actions */
        .quick-actions {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 0;
            width: 60px;
            height: 3px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            border-radius: 16px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            border-color: #667eea;
        }

        .action-card:hover::before {
            opacity: 0.05;
        }

        .action-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .action-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Recent Activity */
        .recent-activity {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-light);
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            font-size: 1rem;
        }

        .activity-icon.success { background: rgba(79, 172, 254, 0.1); color: #4facfe; }
        .activity-icon.warning { background: rgba(252, 182, 159, 0.1); color: #fcb69f; }
        .activity-icon.danger { background: rgba(255, 154, 158, 0.1); color: #ff9a9e; }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .activity-time {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0;
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card, .action-card, .recent-activity {
            animation: fadeInUp 0.6s ease forwards;
        }

        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.2s; }
        .stat-card:nth-child(4) { animation-delay: 0.3s; }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1001;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 1.2rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-kaaba"></i>
                منصة العمرة
            </div>
            <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">نظام إدارة شامل</p>
        </div>
        
        <div class="sidebar-nav">
            <a href="#" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                الرئيسية
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-users-cog"></i>
                المديرين
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-building"></i>
                الوكالات
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                المعتمرين
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-tags"></i>
                العروض والباقات
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-clipboard-list"></i>
                الطلبات
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                التقارير والإحصائيات
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                الإعدادات
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                تسجيل الخروج
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="welcome-text">
                    <h1>مرحباً بك، أحمد المدير 👋</h1>
                    <p>إليك نظرة شاملة على أداء منصة العمرة اليوم</p>
                </div>
                <div class="header-actions">
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        إضافة جديد
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <section class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>إجمالي الوكالات</h3>
                        <div class="stat-number"><?php echo $total_agencies; ?></div>
                    </div>
                    <i class="fas fa-building stat-icon"></i>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>المعتمرين المسجلين</h3>
                        <div class="stat-number"><?php echo $total_pilgrims; ?></div>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>العروض النشطة</h3>
                        <div class="stat-number"><?php echo $total_offers; ?></div>
                    </div>
                    <i class="fas fa-tags stat-icon"></i>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-content">
                    <div class="stat-info">
                        <h3>الطلبات المعلقة</h3>
                        <div class="stat-number"><?php echo $pending_requests; ?></div>
                    </div>
                    <i class="fas fa-clock stat-icon"></i>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <h2 class="section-title">الإجراءات السريعة</h2>
            <div class="actions-grid">
                <a href="#" class="action-card">
                    <div class="action-content">
                        <i class="fas fa-user-plus action-icon"></i>
                        <div class="action-title">إضافة مدير جديد</div>
                        <div class="action-desc">إنشاء حساب مدير جديد وتحديد الصلاحيات</div>
                    </div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-content">
                        <i class="fas fa-building action-icon"></i>
                        <div class="action-title">تسجيل وكالة</div>
                        <div class="action-desc">إضافة وكالة عمرة جديدة للمنصة</div>
                    </div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-content">
                        <i class="fas fa-clipboard-check action-icon"></i>
                        <div class="action-title">مراجعة الطلبات</div>
                        <div class="action-desc">مراجعة وموافقة طلبات العمرة المعلقة</div>
                    </div>
                </a>

                <a href="#" class="action-card">
                    <div class="action-content">
                        <i class="fas fa-chart-bar action-icon"></i>
                        <div class="action-title">عرض التقارير</div>
                        <div class="action-desc">استعراض تقارير الأداء والإحصائيات</div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="recent-activity">
            <h2 class="section-title">الأنشطة الأخيرة</h2>
            
            <div class="activity-item">
                <div class="activity-icon success">
                    <i class="fas fa-check"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">تم قبول طلب عمرة جديد</div>
                    <div class="activity-time">قبل 15 دقيقة</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon warning">
                    <i class="fas fa-building"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">وكالة الحرمين سجلت عرض جديد</div>
                    <div class="activity-time">قبل ساعة واحدة</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon success">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">تسجيل معتمر جديد: محمد أحمد علي</div>
                    <div class="activity-time">قبل 3 ساعات</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-icon danger">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">طلب عمرة يحتاج مراجعة عاجلة</div>
                    <div class="activity-time">قبل 5 ساعات</div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });

        // Add smooth animations to cards on scroll
        function animateOnScroll() {
            const cards = document.querySelectorAll('.stat-card, .action-card');
            cards.forEach(card => {
                const cardTop = card.getBoundingClientRect().top;
                const cardVisible = 150;
                
                if (cardTop < window.innerHeight - cardVisible) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        }

        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>