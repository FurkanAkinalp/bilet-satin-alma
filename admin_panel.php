<?php
include 'database.php';
require_role('admin');

$error_message = $_GET['error'] ?? null;
$success_message = isset($_GET['success']);

try {
    $stmt_companies = $db->query("SELECT * FROM \"Company\" ORDER BY company_name ASC");
    $companies = $stmt_companies->fetchAll();
    $sql_company_admins = "SELECT U.*, C.company_name FROM \"User\" U LEFT JOIN \"Company\" C ON U.company_id = C.id WHERE U.role = 'company_admin' ORDER BY U.full_name ASC";
    $stmt_company_admins = $db->query($sql_company_admins);
    $company_admins = $stmt_company_admins->fetchAll();
    $stmt_coupons = $db->query("SELECT * FROM \"Coupons\" ORDER BY expiration_date DESC");
    $coupons = $stmt_coupons->fetchAll();
} catch (PDOException $e) {
    $error_message = "Veri listelenirken hata oluştu: " . $e->getMessage();
    $companies = $company_admins = $coupons = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title> <link rel="stylesheet" href="styles.css">
    <style> .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; } .data-table th, .data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle;} .data-table th { background-color: #f2f2f2; } .data-table tr:nth-child(even){ background-color: #f9f9f9; } .action-links a { margin-right: 8px; font-size: 13px; white-space: nowrap; } .delete-link { color: #d9002c; } .success-message { color:green; background-color: #e0ffe0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
</head>
<body>
    <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs (Admin)</a></div>
        <nav class="nav"> <a href="index.php">Ana Sayfa</a> <a href="logout.php" class="nav-button">Çıkış Yap</a> </nav>
    </header>
    <main class="panel-container">
        <h1>Admin Yönetim Paneli</h1>
        <p style="margin-bottom: 20px;">Hoşgeldin, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        <?php if ($error_message): ?> <p class="error-message">Hata: <?php echo htmlspecialchars(urldecode($error_message)); ?></p> <?php endif; ?>
        <?php if ($success_message): ?> <p class="success-message">İşlem başarıyla tamamlandı.</p> <?php endif; ?>
        <div class="panel-grid">
            <section class="form-section">
                <h2>1. Otobüs Firmaları</h2>
                <form action="admin_actions.php?action=create_company" method="POST" style="margin-bottom: 20px;">
                    <label for="company_name">Yeni Firma Adı:</label>
                    <input type="text" id="company_name" name="company_name" required>
                    <button type="submit" class="search-button" style="height: auto; padding: 10px; width: auto;">Firmayı Ekle</button>
                </form>
                <div style="max-height: 300px; overflow-y: auto;">
                    <table class="data-table">
                        <thead><tr><th>Firma Adı</th><th>İşlemler</th></tr></thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                            <tr> <td><?php echo htmlspecialchars($company['company_name']); ?></td> <td class="action-links"> <a href="edit_company.php?id=<?php echo $company['id']; ?>">Düzenle</a> <a href="admin_actions.php?action=delete_company&id=<?php echo $company['id']; ?>" class="delete-link" onclick="return confirm('Bu firmayı silmek istediğinize emin misiniz? İlişkili seferler ve adminler etkilenebilir.');">Sil</a> </td> </tr>
                            <?php endforeach; ?>
                            <?php if(empty($companies)): ?><tr><td colspan="2" style="text-align: center;">Firma bulunamadı.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="form-section">
                <h2>2. Firma Adminleri</h2>
                <form action="admin_actions.php?action=create_company_admin" method="POST" style="margin-bottom: 20px;">
                    <label style="margin-bottom: 10px; font-weight: bold;">Yeni Firma Admini Ekle:</label>
                    <input type="text" name="full_name" placeholder="Ad Soyad" required>
                    <input type="email" name="email" placeholder="E-posta" required>
                    <input type="password" name="password" placeholder="Geçici Şifre" required>
                    <select name="company_id" required> <option value="">-- Firma Seçin --</option> <?php foreach ($companies as $company): ?> <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['company_name']); ?></option> <?php endforeach; ?> </select>
                    <button type="submit" class="search-button" style="height: auto; padding: 10px; width: auto;">Firma Adminini Ekle</button>
                </form>
                 <div style="max-height: 300px; overflow-y: auto;">
                    <table class="data-table">
                        <thead><tr><th>Ad Soyad</th><th>E-posta</th><th>Firma</th><th>İşlemler</th></tr></thead>
                        <tbody>
                            <?php foreach ($company_admins as $admin): ?>
                            <tr> <td><?php echo htmlspecialchars($admin['full_name']); ?></td> <td><?php echo htmlspecialchars($admin['email']); ?></td> <td><?php echo htmlspecialchars($admin['company_name'] ?? 'Atanmamış'); ?></td> <td class="action-links"> <a href="admin_actions.php?action=delete_user&id=<?php echo $admin['id']; ?>" class="delete-link" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">Sil</a> </td> </tr>
                            <?php endforeach; ?>
                            <?php if(empty($company_admins)): ?><tr><td colspan="4" style="text-align: center;">Firma admini bulunamadı.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                 </div>
            </section>
            <section class="form-section" style="grid-column: span 2;">
                <h2>3. İndirim Kuponları</h2>
                <form action="admin_actions.php?action=create_coupon" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px;">
                    <div style="flex: 2;"><label for="code">Kod:</label><input type="text" id="code" name="code" required></div>
                    <div style="flex: 1;"><label for="rate">Oran (%):</label><input type="number" id="rate" name="discount_rate" min="1" max="100" step="1" required></div>
                    <div style="flex: 1;"><label for="limit">Limit:</label><input type="number" id="limit" name="usage_limit" min="1" value="100" required></div>
                    <div style="flex: 2;"><label for="expiry">Son Tarih:</label><input type="date" id="expiry" name="expiration_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required></div>
                    <button type="submit" class="search-button" style="height: auto; padding: 10px; flex-basis: 100px;">Kuponu Ekle</button>
                </form>
                 <div style="max-height: 300px; overflow-y: auto;">
                    <table class="data-table">
                        <thead><tr><th>Kod</th><th>Oran</th><th>Kullanım</th><th>Son Tarih</th><th>İşlemler</th></tr></thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr> <td><?php echo htmlspecialchars($coupon['code']); ?></td> <td>%<?php echo htmlspecialchars($coupon['discount_rate'] * 100); ?></td> <td><?php echo $coupon['usage_count']; ?> / <?php echo $coupon['usage_limit']; ?></td> <td><?php echo date('d.m.Y', strtotime($coupon['expiration_date'])); ?></td> <td class="action-links"> <a href="edit_coupon.php?id=<?php echo $coupon['id']; ?>">Düzenle</a> <a href="admin_actions.php?action=delete_coupon&id=<?php echo $coupon['id']; ?>" class="delete-link" onclick="return confirm('Bu kuponu silmek istediğinize emin misiniz?');">Sil</a> </td> </tr>
                            <?php endforeach; ?>
                            <?php if(empty($coupons)): ?><tr><td colspan="5" style="text-align: center;">Kupon bulunamadı.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                 </div>
            </section>
        </div>
    </main>
</body>
</html>