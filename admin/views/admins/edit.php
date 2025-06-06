<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../includes/db.php';

// جلب بيانات المدير وصلاحياته
$admin_id = $_SESSION['admin_id'];
$permissions = [];
$stmt = $pdo->prepare('SELECT * FROM admin_permissions WHERE admin_id = ?');
$stmt->execute([$admin_id]);
foreach ($stmt->fetchAll() as $perm) {
    $permissions[$perm['permission_key']] = $perm;
}

$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
$is_super_admin = ($admin['id'] == 1 || $admin['username'] === 'admin');
$can_edit = $is_super_admin || !empty($permissions['manage_admins']['allow_edit']);

if (!$can_edit) {
    header('Location: dashboard.php?lang=' . (isset($_GET['lang']) ? $_GET['lang'] : 'ar'));
    exit;
}

// التحقق من وجود معرف المدير المراد تعديله
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$edit_id) {
    header('Location: manage_admins.php');
    exit;
}

// لا يمكن تعديل المدير العام أو المدير الحالي
$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$edit_id]);
$admin_to_edit = $stmt->fetch();

if (!$admin_to_edit || $edit_id == 1 || $admin_to_edit['username'] === 'admin' || $edit_id == $admin_id) {
    header('Location: manage_admins.php');
    exit;
}

$texts = [
    'ar' => [
        'title' => 'تعديل بيانات المدير',
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'password_note' => 'اتركها فارغة إذا لم ترد تغييرها',
        'email' => 'البريد الإلكتروني',
        'full_name' => 'الاسم الكامل',
        'permissions' => 'الصلاحيات',
        'view' => 'عرض',
        'add' => 'إضافة',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'save' => 'حفظ التعديلات',
        'cancel' => 'إلغاء',
        'agency_management' => 'إدارة الوكالات',
        'pilgrim_management' => 'إدارة المعتمرين',
        'admin_management' => 'إدارة المدراء',
        'offer_management' => 'إدارة العروض',
        'success' => 'تم تحديث البيانات بنجاح',
        'error' => 'حدث خطأ أثناء تحديث البيانات'
    ],
    'fr' => [
        'title' => 'Modifier l\'administrateur',
        'username' => 'Nom d\'utilisateur',
        'password' => 'Mot de passe',
        'password_note' => 'Laissez vide pour ne pas changer',
        'email' => 'E-mail',
        'full_name' => 'Nom complet',
        'permissions' => 'Permissions',
        'view' => 'Voir',
        'add' => 'Ajouter',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'save' => 'Enregistrer les modifications',
        'cancel' => 'Annuler',
        'agency_management' => 'Gestion des agences',
        'pilgrim_management' => 'Gestion des pèlerins',
        'admin_management' => 'Gestion des administrateurs',
        'offer_management' => 'Gestion des offres',
        'success' => 'Informations mises à jour avec succès',
        'error' => 'Erreur lors de la mise à jour des informations'
    ]
];

$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$t = $texts[$lang];
$page_title = $t['title'];

// جلب صلاحيات المدير المراد تعديله
$stmt = $pdo->prepare('SELECT * FROM admin_permissions WHERE admin_id = ?');
$stmt->execute([$edit_id]);
$current_permissions = [];
foreach ($stmt->fetchAll() as $perm) {
    $current_permissions[$perm['permission_key']] = $perm;
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    
    if ($username && $email && $full_name) {
        try {            // تحديث بيانات المدير
            if ($password) {
                $stmt = $pdo->prepare('UPDATE admins SET username = ?, password = ?, email = ?, full_name = ? WHERE id = ?');
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email, $full_name, $edit_id]);
            } else {
                $stmt = $pdo->prepare('UPDATE admins SET username = ?, email = ?, full_name = ? WHERE id = ?');
                $stmt->execute([$username, $email, $full_name, $edit_id]);
            }
            
            // حذف الصلاحيات القديمة
            $pdo->prepare('DELETE FROM admin_permissions WHERE admin_id = ?')->execute([$edit_id]);
            
            // إضافة الصلاحيات الجديدة
            $permission_keys = ['manage_agencies', 'manage_pilgrims', 'manage_admins', 'manage_offers'];
            foreach ($permission_keys as $key) {
                $view = isset($_POST[$key . '_view']) ? 1 : 0;
                $add = isset($_POST[$key . '_add']) ? 1 : 0;
                $edit = isset($_POST[$key . '_edit']) ? 1 : 0;
                $delete = isset($_POST[$key . '_delete']) ? 1 : 0;
                  if ($view || $add || $edit || $delete) {
                    $stmt = $pdo->prepare('INSERT INTO admin_permissions (admin_id, permission_key, allow_view, allow_add, allow_edit, allow_delete) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$edit_id, $key, $view, $add, $edit, $delete]);
                }
            }
            
            header('Location: manage_admins.php?lang=' . $lang . '&msg=updated');
            exit;
        } catch (PDOException $e) {
            $error = $t['error'];
        }
    }
}

require_once 'template/sidebar.php';
?>

<style>
    .edit-form {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #555;
    }
    .form-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1em;
    }
    .password-note {
        color: #666;
        font-size: 0.9em;
        margin-top: 4px;
    }
    .permissions-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .permissions-table th,
    .permissions-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
    }
    .permissions-table th:first-child,
    .permissions-table td:first-child {
        text-align: <?php echo $lang === 'ar' ? 'right' : 'left'; ?>;
    }
    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 1em;
    }
    .btn-primary {
        background: var(--primary-color);
        color: #fff;
    }
    .btn-secondary {
        background: #6c757d;
        color: #fff;
    }
    .btn:hover {
        opacity: 0.9;
    }
    .error-message {
        color: #dc3545;
        margin-bottom: 15px;
    }
</style>

<div class="edit-form">
    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="username"><?php echo $t['username']; ?></label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin_to_edit['username']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password"><?php echo $t['password']; ?></label>
            <input type="password" id="password" name="password">
            <div class="password-note"><?php echo $t['password_note']; ?></div>
        </div>
        
        <div class="form-group">
            <label for="full_name"><?php echo $t['full_name']; ?></label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin_to_edit['full_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email"><?php echo $t['email']; ?></label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_to_edit['email']); ?>" required>
        </div>
        
        <label><?php echo $t['permissions']; ?></label>
        <table class="permissions-table">
            <tr>
                <th></th>
                <th><?php echo $t['view']; ?></th>
                <th><?php echo $t['add']; ?></th>
                <th><?php echo $t['edit']; ?></th>
                <th><?php echo $t['delete']; ?></th>
            </tr>
            <tr>
                <td><?php echo $t['agency_management']; ?></td>
                <td><input type="checkbox" name="manage_agencies_view" <?php echo !empty($current_permissions['manage_agencies']['allow_view']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_agencies_add" <?php echo !empty($current_permissions['manage_agencies']['allow_add']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_agencies_edit" <?php echo !empty($current_permissions['manage_agencies']['allow_edit']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_agencies_delete" <?php echo !empty($current_permissions['manage_agencies']['allow_delete']) ? 'checked' : ''; ?>></td>
            </tr>
            <tr>
                <td><?php echo $t['pilgrim_management']; ?></td>
                <td><input type="checkbox" name="manage_pilgrims_view" <?php echo !empty($current_permissions['manage_pilgrims']['allow_view']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_pilgrims_add" <?php echo !empty($current_permissions['manage_pilgrims']['allow_add']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_pilgrims_edit" <?php echo !empty($current_permissions['manage_pilgrims']['allow_edit']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_pilgrims_delete" <?php echo !empty($current_permissions['manage_pilgrims']['allow_delete']) ? 'checked' : ''; ?>></td>
            </tr>
            <tr>
                <td><?php echo $t['admin_management']; ?></td>
                <td><input type="checkbox" name="manage_admins_view" <?php echo !empty($current_permissions['manage_admins']['allow_view']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_admins_add" <?php echo !empty($current_permissions['manage_admins']['allow_add']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_admins_edit" <?php echo !empty($current_permissions['manage_admins']['allow_edit']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_admins_delete" <?php echo !empty($current_permissions['manage_admins']['allow_delete']) ? 'checked' : ''; ?>></td>
            </tr>
            <tr>
                <td><?php echo $t['offer_management']; ?></td>
                <td><input type="checkbox" name="manage_offers_view" <?php echo !empty($current_permissions['manage_offers']['allow_view']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_offers_add" <?php echo !empty($current_permissions['manage_offers']['allow_add']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_offers_edit" <?php echo !empty($current_permissions['manage_offers']['allow_edit']) ? 'checked' : ''; ?>></td>
                <td><input type="checkbox" name="manage_offers_delete" <?php echo !empty($current_permissions['manage_offers']['allow_delete']) ? 'checked' : ''; ?>></td>
            </tr>
        </table>
        
        <div class="form-actions">
            <a href="manage_admins.php?lang=<?php echo $lang; ?>" class="btn btn-secondary"><?php echo $t['cancel']; ?></a>
            <button type="submit" class="btn btn-primary"><?php echo $t['save']; ?></button>
        </div>
    </form>
</div>

<?php require_once 'template/footer.php'; ?>
