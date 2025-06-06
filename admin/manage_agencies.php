<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// تحديد اللغة
$texts = [
    'ar' => [
        'title' => 'إدارة الوكالات',
        'add_agency' => 'إضافة وكالة',
        'name' => 'اسم الوكالة',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'address' => 'العنوان',
        'status' => 'الحالة',
        'actions' => 'إجراءات',
        'active' => 'مفعلة',
        'inactive' => 'معطلة',
        'pending' => 'بانتظار التفعيل',
        'activate' => 'تفعيل',
        'deactivate' => 'تعطيل',
        'delete' => 'حذف',
        'search' => 'بحث',
    ],
    'fr' => [
        'title' => 'Gestion des agences',
        'add_agency' => 'Ajouter une agence',
        'name' => 'Nom de l\'agence',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'address' => 'Adresse',
        'status' => 'Statut',
        'actions' => 'Actions',
        'active' => 'Active',
        'inactive' => 'Désactivée',
        'pending' => 'En attente',
        'activate' => 'Activer',
        'deactivate' => 'Désactiver',
        'delete' => 'Supprimer',
        'search' => 'Rechercher',
    ]
];

$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$t = $texts[$lang];
$page_title = $t['title'];

// منطق الصلاحيات
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$permissions = [];
$stmt = $pdo->prepare('SELECT * FROM admin_permissions WHERE admin_id = ?');
$stmt->execute([$admin_id]);
foreach ($stmt->fetchAll() as $perm) {
    $permissions[$perm['permission_key']] = $perm;
}

$is_super_admin = ($admin['id'] == 1 || $admin['username'] === 'admin');
$can_access = $is_super_admin || !empty($permissions['manage_agencies']['allow_view']);

if (!$can_access) {
    header('Location: dashboard.php?lang=' . $lang);
    exit;
}

// جلب الوكالات مع البحث
$where = [];
$params = [];
if (!empty($_GET['search_name'])) {
    $where[] = 'name ILIKE ?';
    $params[] = '%' . $_GET['search_name'] . '%';
}
if (!empty($_GET['search_phone'])) {
    $where[] = 'phone LIKE ?';
    $params[] = '%' . $_GET['search_phone'] . '%';
}
if (!empty($_GET['search_status'])) {
    $where[] = 'status = ?';
    $params[] = $_GET['search_status'];
}

$sql = 'SELECT * FROM agencies';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$agencies = $stmt->fetchAll();

require_once 'template/sidebar.php';
?>

<style>
    .search-section {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .search-form {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .search-form input,
    .search-form select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        min-width: 120px;
    }
    .search-form button {
        background: var(--primary-color);
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .add-button {
        background: #28a745;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        margin-bottom: 20px;
    }
    .table-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th {
        background: #f8f9fa;
        padding: 12px;
        text-align: <?php echo $lang === 'ar' ? 'right' : 'left'; ?>;
        border-bottom: 2px solid #dee2e6;
    }
    td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9em;
    }
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .actions {
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    .actions a {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        color: #fff;
    }
    .actions .activate {
        background: #28a745;
    }
    .actions .deactivate {
        background: #dc3545;
    }
</style>

<div class="search-section">
    <form method="get" class="search-form">
        <input type="text" name="search_name" value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>" placeholder="<?php echo $t['name']; ?>">
        <input type="text" name="search_phone" value="<?php echo htmlspecialchars($_GET['search_phone'] ?? ''); ?>" placeholder="<?php echo $t['phone']; ?>">
        <select name="search_status">
            <option value=""><?php echo $t['status']; ?></option>
            <option value="active" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='active') ? 'selected' : ''; ?>><?php echo $t['active']; ?></option>
            <option value="inactive" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='inactive') ? 'selected' : ''; ?>><?php echo $t['inactive']; ?></option>
            <option value="pending" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='pending') ? 'selected' : ''; ?>><?php echo $t['pending']; ?></option>
        </select>
        <button type="submit">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <?php echo $t['search']; ?>
        </button>
        <input type="hidden" name="lang" value="<?php echo $lang; ?>">
    </form>
</div>

<?php if ($is_super_admin || !empty($permissions['manage_agencies']['allow_add'])): ?>
<a href="add_agency.php?lang=<?php echo $lang; ?>" class="add-button">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: inline-block; vertical-align: -2px; margin-<?php echo $lang === 'ar' ? 'left' : 'right'; ?>: 8px;">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    <?php echo $t['add_agency']; ?>
</a>
<?php endif; ?>

<div class="table-container">
    <table>
        <tr>
            <th><?php echo $t['name']; ?></th>
            <th><?php echo $t['email']; ?></th>
            <th><?php echo $t['phone']; ?></th>
            <th><?php echo $t['address']; ?></th>
            <th><?php echo $t['status']; ?></th>
            <th><?php echo $t['actions']; ?></th>
        </tr>
        <?php foreach ($agencies as $agency): ?>
        <tr>
            <td><?php echo htmlspecialchars($agency['name']); ?></td>
            <td><?php echo htmlspecialchars($agency['email']); ?></td>
            <td><?php echo htmlspecialchars($agency['phone'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($agency['address'] ?? ''); ?></td>
            <td>
                <span class="status-badge status-<?php echo $agency['status']; ?>">
                    <?php echo $t[$agency['status']]; ?>
                </span>
            </td>
            <td class="actions">
                <?php if ($is_super_admin || !empty($permissions['manage_agencies']['allow_edit'])): ?>
                    <?php if ($agency['status'] !== 'active'): ?>
                        <a href="?action=activate&id=<?php echo $agency['id']; ?>&lang=<?php echo $lang; ?>" class="activate"><?php echo $t['activate']; ?></a>
                    <?php else: ?>
                        <a href="?action=deactivate&id=<?php echo $agency['id']; ?>&lang=<?php echo $lang; ?>" class="deactivate"><?php echo $t['deactivate']; ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once 'template/footer.php'; ?>
