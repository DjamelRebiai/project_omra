<?php
session_start();
require_once '../includes/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// الترجمات
$texts = [
    'ar' => [
        'title' => 'إدارة العروض الذهبية',
        'add_offer' => 'إضافة عرض',
        'title_offer' => 'عنوان العرض',
        'description' => 'وصف العرض',
        'price' => 'السعر',
        'type' => 'نوع العرض',
        'select_type' => 'اختر نوع العرض',
        'select_agency' => 'اختر الوكالة',
        'gold' => 'ذهبي',
        'silver' => 'فضي',
        'bronze' => 'برونزي',
        'status' => 'الحالة',
        'actions' => 'إجراءات',
        'agency' => 'الوكالة',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'back' => 'عودة',
        'save' => 'حفظ',
        'cancel' => 'إلغاء'
    ],
    'fr' => [
        'title' => 'Gestion des offres',
        'add_offer' => 'Ajouter une offre',
        'title_offer' => 'Titre de l\'offre',
        'description' => 'Description',
        'price' => 'Prix',
        'type' => 'Type d\'offre',
        'select_type' => 'Sélectionnez le type',
        'select_agency' => 'Sélectionnez l\'agence',
        'gold' => 'Or',
        'silver' => 'Argent',
        'bronze' => 'Bronze',
        'status' => 'Statut',
        'actions' => 'Actions',
        'agency' => 'Agence',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'back' => 'Retour',
        'save' => 'Enregistrer',
        'cancel' => 'Annuler'
    ]
];

$lang = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'ar';
$t = $texts[$lang];

// جلب قائمة الوكالات
try {
    // Use single quotes for string literals in PostgreSQL
    $stmt = $pdo->query("SELECT id, name FROM agencies WHERE status = 'active'");
    $agencies = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback query without status filter if column doesn't exist
    $stmt = $pdo->query('SELECT id, name FROM agencies');
    $agencies = $stmt->fetchAll();
}

// معالجة إضافة عرض جديد
if (isset($_POST['add_offer'])) {
    $title = trim($_POST['title_offer'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $type = trim($_POST['type'] ?? '');
    $agency_id = intval($_POST['agency_id'] ?? 0);

    if ($title && $description && $price && $type && $agency_id) {
        $stmt = $pdo->prepare('INSERT INTO offers (title, description, price, type, agency_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $price, $type, $agency_id]);
        header('Location: manage_offers.php?lang=' . $lang);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t['title']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f5f6fa 100%);
            font-family: '<?php echo $lang === 'ar' ? 'Cairo' : 'Roboto'; ?>', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        form {
            display: grid;
            gap: 15px;
            max-width: 600px;
            margin: 20px auto;
        }
        input, select, textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background: #1e90ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $t['title']; ?></h1>
        
        <form method="post">
            <input type="text" name="title_offer" placeholder="<?php echo $t['title_offer']; ?>" required>
            <textarea name="description" placeholder="<?php echo $t['description']; ?>" required></textarea>
            <input type="number" step="0.01" name="price" placeholder="<?php echo $t['price']; ?>" required>
            <select name="type" required>
                <option value=""><?php echo $t['select_type']; ?></option>
                <option value="gold"><?php echo $t['gold']; ?></option>
                <option value="silver"><?php echo $t['silver']; ?></option>
                <option value="bronze"><?php echo $t['bronze']; ?></option>
            </select>
            <select name="agency_id" required>
                <option value=""><?php echo $t['select_agency']; ?></option>
                <?php if (!empty($agencies)): ?>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['id']; ?>"><?php echo htmlspecialchars($agency['name']); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button type="submit" name="add_offer"><?php echo $t['add_offer']; ?></button>
        </form>

        <a href="dashboard.php?lang=<?php echo $lang; ?>"><?php echo $t['back']; ?></a>
    </div>
</body>
</html>