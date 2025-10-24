<?php
include 'database.php'; 

// YENİ EKLENEN KOD BAŞLANGICI
// Ana sayfada gösterilecek yaklaşan seferleri (günü geçmemiş) veritabanından çek.
$trips = [];
$error_message = '';
try {
    // "Trips" ve "Company" tablolarını birleştirerek
    // tarihi geçmemiş (datetime('now', 'localtime') -> şu andan ileri)
    // ve en yakın tarihli 10 seferi al.
    $sql = "SELECT T.*, C.company_name 
            FROM \"Trips\" T
            JOIN \"Company\" C ON T.company_id = C.id
            WHERE T.departure_time > datetime('now', 'localtime')
            ORDER BY T.departure_time ASC
            LIMIT 10";
            
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $trips = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Güncel seferler alınamadı: " . $e->getMessage();
}
// YENİ EKLENEN KOD SONU
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Alma Platformu</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="header">
        <div class="logo">BiletGO</div>
        <nav class="nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="admin_panel.php">Admin Paneli</a>
                <?php elseif ($_SESSION['role'] == 'company_admin'): ?>
                    <a href="company_panel.php">Firma Paneli</a>
                <?php else: ?>
                    <a href="account.php">Hesabım / Biletlerim</a>
                <?php endif; ?>
                
                <a href="logout.php" class="nav-button">Çıkış Yap</a>
                
            <?php else: ?>
                <a href="login.php">Giriş Yap</a>
                <a href="register.php" class="nav-button">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section class="search-hero">
            <div class="search-widget">
                <h1>Otobüs Bileti Ara</h1>
                
                <form action="sefer_listesi.php" method="GET" class="search-form">
                    
                    <div class="form-group">
                        <label for="kalkis">Nereden?</label>
                        <input type="text" id="kalkis" name="departure_location" placeholder="İl veya ilçe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="varis">Nereye?</label>
                        <input type="text" id="varis" name="arrival_location" placeholder="İl veya ilçe" required>
                    </div>

                    <div class="form-group">
                        <label for="tarih">Tarih</label>
                        <input type="date" id="tarih" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" class="search-button">Sefer Ara</button>
                
                </form>
            </div>
        </section>

        <section class="content-area">
            <h2>Yaklaşan Seferler</h2>
            
            <?php if ($error_message): ?>
                <p style="text-align: center; color: red;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <?php if (empty($trips) && !$error_message): ?>
                <p style="text-align: center; font-size: 18px;">Gösterilecek güncel sefer bulunamadı.</p>
            <?php endif; ?>

            <div class="trip-list">
                <?php foreach ($trips as $trip): ?>
                <div class="trip-card">
                    <div class="trip-company"><?php echo htmlspecialchars($trip['company_name']); ?></div>
                    <div class="trip-time"><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></div>
                    <div class="trip-path">
                        <strong><?php echo htmlspecialchars($trip['departure_location']); ?></strong> ->
                        <?php echo htmlspecialchars($trip['arrival_location']); ?>
                    </div>
                    <div class="trip-price"><?php echo htmlspecialchars($trip['price']); ?> TL</div>
                    <div class="trip-action">
                        <a href="trip_detail.php?id=<?php echo $trip['id']; ?>" class="search-button" style="height: auto; padding: 10px 20px;">
                            Koltuk Seç
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        </main>

</body>
</html>