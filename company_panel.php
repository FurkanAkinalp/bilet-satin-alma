<?php
include 'database.php';
// Bu sayfayı sadece 'company_admin' rolü görebilir
require_role('company_admin'); 

// Oturum açan adminin firma ID'sini al (login.php'de eklendi)
$company_id = $_SESSION['company_id'];

// Firmanın mevcut seferlerini (Trips) veritabanından çek
try {
    $stmt = $db->prepare("SELECT * FROM \"Trips\" WHERE company_id = ? ORDER BY departure_time DESC");
    $stmt->execute([$company_id]);
    $trips = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Seferler alınamadı: " . $e->getMessage();
    $trips = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Paneli</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="header">
        <div class="logo">BiletGO (Firma Admin)</div>
        <nav class="nav">
            <a href="index.php">Ana Sayfa</a>
            <a href="logout.php" class="nav-button">Çıkış Yap</a>
        </nav>
    </header>

    <main class="panel-container">
        <h1>Sefer Yönetimi</h1>
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
                <h2>Yeni Sefer Oluştur</h2>
                <form action="company_actions.php?action=create_trip" method="POST">
                    
                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label for="departure_location">Kalkış Yeri:</label>
                            <input type="text" id="departure_location" name="departure_location" required>
                        </div>
                        <div style="flex: 1;">
                            <label for="arrival_location">Varış Yeri:</label>
                            <input type="text" id="arrival_location" name="arrival_location" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label for="trip_date">Tarih:</label>
                            <input type="date" id="trip_date" name="trip_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div style="flex: 1;">
                            <label for="trip_time">Saat:</label>
                            <input type="time" id="trip_time" name="trip_time" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <div style="flex: 1;">
                            <label for="price">Fiyat (TL):</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div style="flex: 1;">
                            <label for="seat_count">Koltuk Sayısı:</label>
                            <input type="number" id="seat_count" name="seat_count" min="1" value="40" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="search-button" style="margin-top: 15px;">Seferi Kaydet</button>
                </form>
            </section>
            
            <section class="form-section">
                <h2>Mevcut Seferler (CRUD)</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <th style="padding: 8px;">Kalkış - Varış</th>
                                <th style="padding: 8px;">Zaman</th>
                                <th style="padding: 8px;">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trips)): ?>
                                <tr>
                                    <td colspan="3" style="padding: 8px; text-align: center;">Henüz sefer oluşturulmamış.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($trips as $trip): ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 8px;">
                                        <?php echo htmlspecialchars($trip['departure_location']); ?> - 
                                        <?php echo htmlspecialchars($trip['arrival_location']); ?>
                                    </td>
                                    <td style="padding: 8px;"><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></td>
                                    <td style="padding: 8px;">
                                        <a href="company_actions.php?action=delete_trip&id=<?php echo $trip['id']; ?>" 
                                           onclick="return confirm('Bu seferi silmek istediğinize emin misiniz?');" 
                                           style="color: #d9002c; font-size: 13px;">Sil</a>
                                        </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </main>

</body>
</html>