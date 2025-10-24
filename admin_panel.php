<?php
include 'database.php';
require_role('admin'); 
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="header">
        <div class="logo">BiletGO (Admin)</div>
        <nav class="nav">
            <a href="index.php">Ana Sayfa</a>
            <a href="logout.php" class="nav-button">Çıkış Yap</a>
        </nav>
    </header>

    <main class="panel-container">
        <h1>Admin Yönetim Paneli</h1>
        <p>Hoşgeldin, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px;">
                Hata: <?php echo htmlspecialchars($_GET['error']); ?>
            </p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <p style="color:green; background-color: #e0ffe0; padding: 10px; border-radius: 6px;">
                İşlem başarıyla tamamlandı.
            </p>
        <?php endif; ?>
        
        <div class="panel-grid">
            
            <section class="form-section">
                <h2>1. Otobüs Firması Ekle</h2>
                <form action="admin_actions.php?action=create_company" method="POST">
                    <label for="company_name">Firma Adı:</label>
                    <input type="text" id="company_name" name="company_name" required>
                    <button type="submit" class="search-button">Firmayı Kaydet</button>
                </form>
            </section>
            
            <section class="form-section">
                <h2>2. Firma Admini Oluştur ve Ata</h2>
                <form action="admin_actions.php?action=create_company_admin" method="POST">
                    
                    <label for="ca_full_name">Admin Adı Soyadı:</label>
                    <input type="text" id="ca_full_name" name="full_name" required>
                    
                    <label for="ca_email">E-posta:</label>
                    <input type="email" id="ca_email" name="email" required>
                    
                    <label for="ca_password">Geçici Şifre:</label>
                    <input type="password" id="ca_password" name="password" required>
                    
                    <label for="ca_company">Atanacak Firma:</label>
                    <select id="ca_company" name="company_id" required>
                        <option value="">-- Firma Seçin --</option>
                        <?php
                            $stmt = $db->query("SELECT id, company_name FROM \"Company\"");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value=\"{$row['id']}\">" . htmlspecialchars($row['company_name']) . "</option>";
                            }
                        ?>
                    </select>
                    
                    <button type="submit" class="search-button">Firma Adminini Kaydet</button>
                </form>
            </section>

            <section class="form-section">
                <h2>3. İndirim Kuponu Ekle</h2>
                <form action="admin_actions.php?action=create_coupon" method="POST">
                    <label for="code">Kupon Kodu:</label>
                    <input type="text" id="code" name="code" required>
                    
                    <label for="rate">İndirim Oranı (%):</label>
                    <input type="number" id="rate" name="discount_rate" min="1" max="100" step="1" placeholder="Örn: 20" required>
                    
                    <label for="limit">Kullanım Limiti:</label>
                    <input type="number" id="limit" name="usage_limit" min="1" value="100" required>

                    <label for="expiry">Son Kullanma Tarihi:</label>
                    <input type="date" id="expiry" name="expiration_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                    
                    <button type="submit" class="search-button">Kuponu Kaydet</button>
                </form>
            </section>

        </div>
    </main>

</body>
</html>