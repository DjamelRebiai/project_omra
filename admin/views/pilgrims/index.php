<?php
// admin/manage_pilgrims.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// جلب بيانات المدير الحالي
$admin_id = $_SESSION['admin_id'];
$permissions = [];
$stmt = $pdo->prepare('SELECT * FROM admin_permissions WHERE admin_id = ?');
$stmt->execute([$admin_id]);
foreach ($stmt->fetchAll() as $perm) {
    $permissions[$perm['permission_key']] = $perm;
}

// منطق الصلاحيات
$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$is_super_admin = ($admin['id'] == 1 || $admin['username'] === 'admin');
$can_access = $is_super_admin || !empty($permissions['manage_pilgrims']['allow_view']);
if (!$can_access) {
    header('Location: dashboard.php?lang=' . (isset($_GET['lang']) ? $_GET['lang'] : 'ar'));
    exit;
}

// قبول أو رفض معتمر
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'approve') {
        $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?')->execute(['approved', $id]);
    } elseif ($_GET['action'] === 'reject') {
        $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?')->execute(['rejected', $id]);
    }
    header('Location: manage_pilgrims.php');
    exit;
}

// جلب جميع الطلبات والمعتمرين
$where = [];
$params = [];
if (!empty($_GET['search_name'])) {
    $where[] = 'p.full_name LIKE ?';
    $params[] = '%' . $_GET['search_name'] . '%';
}
if (!empty($_GET['search_passport'])) {
    $where[] = 'p.passport_number LIKE ?';
    $params[] = '%' . $_GET['search_passport'] . '%';
}
if (!empty($_GET['search_nationality'])) {
    $where[] = 'p.nationality LIKE ?';
    $params[] = '%' . $_GET['search_nationality'] . '%';
}
if (!empty($_GET['search_phone'])) {
    $where[] = 'p.phone LIKE ?';
    $params[] = '%' . $_GET['search_phone'] . '%';
}
if (!empty($_GET['search_status'])) {
    $where[] = 'r.status = ?';
    $params[] = $_GET['search_status'];
}

$sql = 'SELECT r.id as request_id, r.status, p.* FROM requests r JOIN pilgrims p ON r.pilgrim_id = p.id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$texts = [
    'ar' => [
        'title' => 'إدارة المعتمرين',
        'add_pilgrim' => 'إضافة معتمر',
        'name' => 'اسم المعتمر',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'passport' => 'رقم الجواز',
        'nationality' => 'الجنسية',
        'status' => 'الحالة',
        'actions' => 'إجراءات',
        'approve' => 'موافقة',
        'reject' => 'رفض',
        'edit' => 'تعديل',
        'pending' => 'قيد الانتظار',
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
        'sent_to_agency' => 'تم إرسالها للوكالة',
        'birth_date' => 'تاريخ الميلاد'
    ],
    'fr' => [
        'title' => 'Gestion des pèlerins',
        'add_pilgrim' => 'Ajouter un pèlerin',
        'name' => 'Nom du pèlerin',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'passport' => 'N° de passeport',
        'nationality' => 'Nationalité',
        'status' => 'Statut',
        'actions' => 'Actions',
        'approve' => 'Approuver',
        'reject' => 'Rejeter',
        'edit' => 'Modifier',
        'pending' => 'En attente',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'sent_to_agency' => 'Envoyé à l\'agence',
        'birth_date' => 'Date de naissance'
    ]
];

$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$t = $texts[$lang];
$page_title = $t['title'];

// بداية القالب
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
    .search-form button:hover {
        opacity: 0.9;
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
    .actions a.approve {
        background: #28a745;
    }
    .actions a.reject {
        background: #dc3545;
    }
    .actions a.edit {
        background: #ffc107;
        color: #000;
    }
    .actions a:hover {
        opacity: 0.9;
    }
</style>

<div class="search-section">    <form method="get" class="search-form">
        <input type="text" name="search_name" value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>" placeholder="<?php echo $t['name']; ?>">
        <input type="text" name="search_passport" value="<?php echo htmlspecialchars($_GET['search_passport'] ?? ''); ?>" placeholder="<?php echo $t['passport']; ?>">
        <input type="text" name="search_nationality" value="<?php echo htmlspecialchars($_GET['search_nationality'] ?? ''); ?>" placeholder="<?php echo $t['nationality']; ?>">
        <input type="text" name="search_phone" value="<?php echo htmlspecialchars($_GET['search_phone'] ?? ''); ?>" placeholder="<?php echo $t['phone']; ?>">
        <select name="search_status">
            <option value=""><?php echo $t['status']; ?></option>
            <option value="pending" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='pending') ? 'selected' : ''; ?>><?php echo $t['pending']; ?></option>
            <option value="approved" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='approved') ? 'selected' : ''; ?>><?php echo $t['approved']; ?></option>
            <option value="rejected" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='rejected') ? 'selected' : ''; ?>><?php echo $t['rejected']; ?></option>
            <option value="sent_to_agency" <?php echo (isset($_GET['search_status']) && $_GET['search_status']==='sent_to_agency') ? 'selected' : ''; ?>><?php echo $t['sent_to_agency']; ?></option>
        </select>
        <button type="submit">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <?php echo $lang==='ar'?'بحث':'Search'; ?>
        </button>
        <input type="hidden" name="lang" value="<?php echo $lang; ?>">
    </form>
</div>

<div class="table-container">
    <table>
        <tr>
            <th><?php echo $t['name']; ?></th>
            <th><?php echo $t['passport']; ?></th>
            <th><?php echo $t['phone']; ?></th>
            <th><?php echo $t['email']; ?></th>
            <th><?php echo $t['birth_date']; ?></th>
            <th><?php echo $t['nationality']; ?></th>
            <th><?php echo $t['status']; ?></th>
            <th><?php echo $t['actions']; ?></th>
        </tr>
        <?php foreach ($requests as $r): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['full_name']); ?></td>
            <td><?php echo htmlspecialchars($r['passport_number']); ?></td>
            <td><?php echo htmlspecialchars($r['phone']); ?></td>
            <td><?php echo htmlspecialchars($r['email'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($r['birth_date'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($r['nationality'] ?? ''); ?></td>
            <td><?php 
                if ($r['status'] === 'pending') echo $t['pending'];
                elseif ($r['status'] === 'approved') echo $t['approved'];
                elseif ($r['status'] === 'rejected') echo $t['rejected'];
                elseif ($r['status'] === 'sent_to_agency') echo $t['sent_to_agency'];
            ?></td>            <td class="actions">
                <?php if ($r['status'] === 'pending'): ?>
                    <a href="?action=approve&id=<?php echo $r['request_id']; ?>&lang=<?php echo $lang; ?>" class="approve"><?php echo $t['approve']; ?></a>
                    <a href="?action=reject&id=<?php echo $r['request_id']; ?>&lang=<?php echo $lang; ?>" class="reject"><?php echo $t['reject']; ?></a>
                <?php endif; ?>
                <?php if ($is_super_admin || !empty($permissions['manage_pilgrims']['allow_edit'])): ?>
                    <a href="edit_pilgrim.php?id=<?php echo $r['pilgrim_id']; ?>&lang=<?php echo $lang; ?>" class="edit"><?php echo $t['edit']; ?></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once 'template/footer.php'; ?>
