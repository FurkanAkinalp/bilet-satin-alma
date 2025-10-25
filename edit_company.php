<?php
include 'database.php';
require_role('admin');

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: admin_panel.php?error=MissingID"); exit; }

try {
    $stmt = $db->prepare("SELECT * FROM \"Company\" WHERE id = ?");
    $stmt->execute([$id]);
    $company = $stmt->fetch();
    if (!$company) { throw new Exception("Firma bulunamadı."); }
} catch (Exception $e) { header("Location: admin_panel.php?error=" . urlencode($e->getMessage())); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><title>Firma Düzenle</title><link rel="stylesheet" href="styles.css">
    <style> .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
</head>
<body>
    <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs (Admin)</a></div>
        <nav class="nav"> <a href="admin_panel.php">Admin Paneli</a> <a href="logout.php" class="nav-button">Çıkış Yap</a> </nav>
    </header>
    <main class="panel-container" style="max-width: 600px;">
        <h1>Firmayı Düzenle</h1>
        <?php if(isset($_GET['error'])): ?> <p class="error-message">Hata: <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p> <?php endif; ?>
        <form action="admin_actions.php" method="POST">
             <input type="hidden" name="action" value="update_company">
             <input type="hidden" name="id" value="<?php echo htmlspecialchars($company['id']); ?>">
             <div class="form-group">
                <label for="company_name">Firma Adı:</label>
                <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
             </div>
             <button type="submit" class="search-button" style="height: auto; padding: 12px; width: auto;">Güncelle</button>
             <a href="admin_panel.php" style="margin-left: 10px; color: #6c757d;">İptal</a>
        </form>
    </main>
</body>
</html>