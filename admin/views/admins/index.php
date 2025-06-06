<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

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
$can_manage_admins = $is_super_admin || !empty($permissions['manage_admins']['allow_view']);

if (!$can_manage_admins) {
    header('Location: dashboard.php?lang=' . (isset($_GET['lang']) ? $_GET['lang'] : 'ar'));
    exit;
}

$texts = [    'ar' => [
        'title' => 'إدارة المدراء',
        'add_admin' => 'إضافة مدير جديد',
        'edit_admin' => 'تعديل المدير',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'email' => 'البريد الإلكتروني',
        'full_name' => 'الاسم الكامل',
        'permissions' => 'الصلاحيات',
        'actions' => 'إجراءات',
        'view' => 'عرض',
        'add' => 'إضافة',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'confirm_delete' => 'هل أنت متأكد من حذف هذا المدير؟',
        'agency_management' => 'إدارة الوكالات',
        'pilgrim_management' => 'إدارة المعتمرين',
        'admin_management' => 'إدارة المدراء',
        'offer_management' => 'إدارة العروض',
        'success' => 'تمت العملية بنجاح'
    ],    'fr' => [
        'title' => 'Gestion des administrateurs',
        'add_admin' => 'Ajouter un administrateur',
        'edit_admin' => 'Modifier l\'administrateur',
        'username' => 'Nom d\'utilisateur',
        'password' => 'Mot de passe',
        'email' => 'E-mail',
        'full_name' => 'Nom complet',
        'permissions' => 'Permissions',
        'actions' => 'Actions',
        'view' => 'Voir',
        'add' => 'Ajouter',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cet administrateur ?',
        'agency_management' => 'Gestion des agences',
        'pilgrim_management' => 'Gestion des pèlerins',
        'admin_management' => 'Gestion des administrateurs',
        'offer_management' => 'Gestion des offres',
        'success' => 'Opération réussie'
    ]
];

$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$t = $texts[$lang];
$page_title = $t['title'];

// جلب جميع المدراء
$stmt = $pdo->query('SELECT * FROM admins ORDER BY id ASC');
$admins = $stmt->fetchAll();

require_once 'template/sidebar.php';
?>

<style>
    .table-container {
        margin: 20px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95em;
    }
    th {
        background: #f8f9fa;
        padding: 12px;
        text-align: <?php echo $lang === 'ar' ? 'right' : 'left'; ?>;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    .actions {
        display: flex;
        gap: 8px;
        justify-content: <?php echo $lang === 'ar' ? 'flex-end' : 'flex-start'; ?>;
        white-space: nowrap;
    }
    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        color: #fff;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }
    .btn-edit {
        background: var(--primary-color);
    }
    .btn-delete {
        background: #dc3545;
    }
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .permission-badge {
        display: inline-block;
        padding: 4px 8px;
        background: #e9ecef;
        border-radius: 4px;
        margin: 2px;
        font-size: 0.9em;
        color: #495057;
    }
    .add-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: var(--primary-color);
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        margin: 20px;
        transition: all 0.2s;
    }
    .add-button:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    @media (max-width: 768px) {
        .table-container {
            margin: 10px;
            border-radius: 6px;
        }
        td, th {
            padding: 8px;
        }
        .permission-badge {
            font-size: 0.8em;
        }
    }
</style>

<?php if ($is_super_admin || !empty($permissions['manage_admins']['allow_add'])): ?>
<a href="add_admin.php?lang=<?php echo $lang; ?>" class="add-button">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    <?php echo $t['add_admin']; ?>
</a>
<?php endif; ?>

<div class="table-container">
    <table>
        <tr>
            <th><?php echo $t['username']; ?></th>
            <th><?php echo $t['full_name']; ?></th>
            <th><?php echo $t['email']; ?></th>
            <th><?php echo $t['permissions']; ?></th>
            <th><?php echo $t['actions']; ?></th>
        </tr>
        <?php foreach ($admins as $a): ?>
        <tr>
            <td><?php echo htmlspecialchars($a['username']); ?></td>
            <td><?php echo htmlspecialchars($a['full_name']); ?></td>
            <td><?php echo htmlspecialchars($a['email']); ?></td>
            <td>                <?php
                $stmt = $pdo->prepare('SELECT * FROM admin_permissions WHERE admin_id = ?');
                $stmt->execute([$a['id']]);
                $admin_perms = $stmt->fetchAll();
                foreach ($admin_perms as $perm):
                    $actions = [];
                    if ($perm['allow_view']) $actions[] = $t['view'];
                    if ($perm['allow_add']) $actions[] = $t['add'];
                    if ($perm['allow_edit']) $actions[] = $t['edit'];
                    if ($perm['allow_delete']) $actions[] = $t['delete'];
                    if ($actions):
                ?>
                    <span class="permission-badge">
                        <?php echo $t[$perm['permission_key']] ?? $perm['permission_key']; ?>
                        (<?php echo implode(', ', $actions); ?>)
                    </span>
                <?php 
                    endif;
                endforeach;
                ?>
            </td>
            <td class="actions">
                <?php if ($a['id'] != 1 && $a['username'] !== 'admin' && $a['id'] != $admin_id): ?>
                    <?php if ($is_super_admin || !empty($permissions['manage_admins']['allow_edit'])): ?>
                        <a href="edit_admin.php?id=<?php echo $a['id']; ?>&lang=<?php echo $lang; ?>" class="btn btn-edit">
                            <?php echo $t['edit']; ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($is_super_admin || !empty($permissions['manage_admins']['allow_delete'])): ?>
                        <a href="?action=delete&id=<?php echo $a['id']; ?>&lang=<?php echo $lang; ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('<?php echo $t['confirm_delete']; ?>')">
                            <?php echo $t['delete']; ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once 'template/footer.php'; ?>