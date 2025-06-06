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
$can_edit = $is_super_admin || !empty($permissions['manage_pilgrims']['allow_edit']);

if (!$can_edit) {
    header('Location: dashboard.php?lang=' . (isset($_GET['lang']) ? $_GET['lang'] : 'ar'));
    exit;
}

// التحقق من وجود معرف المعتمر
$pilgrim_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pilgrim_id) {
    header('Location: manage_pilgrims.php');
    exit;
}

$texts = [
    'ar' => [
        'title' => 'تعديل بيانات المعتمر',
        'name' => 'اسم المعتمر',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'passport' => 'رقم الجواز',
        'nationality' => 'الجنسية',
        'birth_date' => 'تاريخ الميلاد',
        'save' => 'حفظ التعديلات',
        'cancel' => 'إلغاء',
        'success' => 'تم تحديث البيانات بنجاح',
        'error' => 'حدث خطأ أثناء تحديث البيانات'
    ],
    'fr' => [
        'title' => 'Modifier les informations du pèlerin',
        'name' => 'Nom du pèlerin',
        'email' => 'E-mail',
        'phone' => 'Téléphone',
        'passport' => 'N° de passeport',
        'nationality' => 'Nationalité',
        'birth_date' => 'Date de naissance',
        'save' => 'Enregistrer les modifications',
        'cancel' => 'Annuler',
        'success' => 'Informations mises à jour avec succès',
        'error' => 'Erreur lors de la mise à jour des informations'
    ]
];

$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$t = $texts[$lang];
$page_title = $t['title'];

// جلب بيانات المعتمر
$stmt = $pdo->prepare('SELECT * FROM pilgrims WHERE id = ?');
$stmt->execute([$pilgrim_id]);
$pilgrim = $stmt->fetch();

if (!$pilgrim) {
    header('Location: manage_pilgrims.php');
    exit;
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $passport_number = trim($_POST['passport_number']);
    $nationality = trim($_POST['nationality']);
    $birth_date = trim($_POST['birth_date']);
    
    if ($full_name && $passport_number) {
        try {
            $stmt = $pdo->prepare('UPDATE pilgrims SET 
                full_name = $1,
                email = $2,
                phone = $3,
                passport_number = $4,
                nationality = $5,
                birth_date = $6
                WHERE id = $7');
            
            $stmt->execute([
                $full_name,
                $email,
                $phone,
                $passport_number,
                $nationality,
                $birth_date,
                $pilgrim_id
            ]);
            
            header('Location: manage_pilgrims.php?lang=' . $lang . '&msg=updated');
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
        max-width: 600px;
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
            <label for="full_name"><?php echo $t['name']; ?></label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($pilgrim['full_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email"><?php echo $t['email']; ?></label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($pilgrim['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="phone"><?php echo $t['phone']; ?></label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($pilgrim['phone']); ?>">
        </div>
        
        <div class="form-group">
            <label for="passport_number"><?php echo $t['passport']; ?></label>
            <input type="text" id="passport_number" name="passport_number" value="<?php echo htmlspecialchars($pilgrim['passport_number']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="nationality"><?php echo $t['nationality']; ?></label>
            <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($pilgrim['nationality'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="birth_date"><?php echo $t['birth_date']; ?></label>
            <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($pilgrim['birth_date'] ?? ''); ?>">
        </div>
        
        <div class="form-actions">
            <a href="manage_pilgrims.php?lang=<?php echo $lang; ?>" class="btn btn-secondary"><?php echo $t['cancel']; ?></a>
            <button type="submit" class="btn btn-primary"><?php echo $t['save']; ?></button>
        </div>
    </form>
</div>

<?php require_once 'template/footer.php'; ?>
