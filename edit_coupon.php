<?php
include 'database.php';
require_role('admin');

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: admin_panel.php?error=MissingID"); exit; }

try {
    $stmt = $db->prepare("SELECT * FROM \"Coupons\" WHERE id = ?");
    $stmt->execute([$id]);
    $coupon = $stmt->fetch();
    if (!$coupon) { throw new Exception("Kupon bulunamadı."); }
} catch (Exception $e) { header("Location: admin_panel.php?error=" . urlencode($e->getMessage())); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><title>Kupon Düzenle</title><link rel="stylesheet" href="styles.css">
     <style> .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
</head>
<body>
     <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs (Admin)</a></div>
        <nav class="nav"> <a href="admin_panel.php">Admin Paneli</a> <a href="logout.php" class="nav-button">Çıkış Yap</a> </nav>
     </header>
     <main class="panel-container" style="max-width: 600px;">
        <h1>Kuponu Düzenle</h1>
         <?php if(isset($_GET['error'])): ?> <p class="error-message">Hata: <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p> <?php endif; ?>
        <form action="admin_actions.php" method="POST">
             <input type="hidden" name="action" value="update_coupon">
             <input type="hidden" name="id" value="<?php echo htmlspecialchars($coupon['id']); ?>">
             <div class="form-group"><label for="code">Kupon Kodu:</label><input type="text" id="code" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" required></div>
             <div class="form-group"><label for="rate">İndirim Oranı (%):</label><input type="number" id="rate" name="discount_rate" value="<?php echo htmlspecialchars($coupon['discount_rate'] * 100); ?>" min="1" max="100" step="1" required></div>
             <div class="form-group"><label for="limit">Kullanım Limiti:</label><input type="number" id="limit" name="usage_limit" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>" min="0" required></div>
             <div class="form-group"><label>Mevcut Kullanım:</label><input type="number" value="<?php echo htmlspecialchars($coupon['usage_count']); ?>" disabled style="background-color: #e9ecef;"></div>
             <div class="form-group"><label for="expiry">Son Kullanma Tarihi:</label><input type="date" id="expiry" name="expiration_date" value="<?php echo htmlspecialchars($coupon['expiration_date']); ?>" required></div>
             <button type="submit" class="search-button" style="height: auto; padding: 12px; width: auto;">Güncelle</button>
             <a href="admin_panel.php" style="margin-left: 10px; color: #6c757d;">İptal</a>
        </form>
     </main>
</body>
</html>