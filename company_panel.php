<?php
include 'database.php';
require_role('company_admin');

$company_id = $_SESSION['company_id'];
$error_message = $_GET['error'] ?? null;
$success_message = isset($_GET['success']);

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
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Paneli</title> <link rel="stylesheet" href="styles.css">
    <style> .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; } .data-table th, .data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: middle;} .data-table th { background-color: #f2f2f2; } .data-table tr:nth-child(even){ background-color: #f9f9f9; } .action-links a { margin-right: 8px; font-size: 13px; white-space: nowrap;} .delete-link { color: #d9002c; } .success-message { color:green; background-color: #e0ffe0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
</head>
<body>
    <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs (Firma Admin)</a></div>
        <nav class="nav"> <a href="index.php">Ana Sayfa</a> <a href="logout.php" class="nav-button">Çıkış Yap</a> </nav>
    </header>
    <main class="panel-container">
        <h1>Sefer Yönetimi</h1>
        <p style="margin-bottom: 20px;">Hoşgeldin, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        <?php if ($error_message): ?> <p class="error-message">Hata: <?php echo htmlspecialchars(urldecode($error_message)); ?></p> <?php endif; ?>
        <?php if ($success_message): ?> <p class="success-message">İşlem başarıyla tamamlandı.</p> <?php endif; ?>
        <div class="panel-grid">
            <section class="form-section">
                <h2>Yeni Sefer Oluştur</h2>
                <form action="company_actions.php?action=create_trip" method="POST">
                    <div style="display: flex; gap: 10px;"><div style="flex: 1;"><label for="departure_location">Kalkış Yeri:</label><input type="text" id="departure_location" name="departure_location" required></div><div style="flex: 1;"><label for="arrival_location">Varış Yeri:</label><input type="text" id="arrival_location" name="arrival_location" required></div></div>
                    <div style="display: flex; gap: 10px;"><div style="flex: 1;"><label for="trip_date">Tarih:</label><input type="date" id="trip_date" name="trip_date" value="<?php echo date('Y-m-d'); ?>" required></div><div style="flex: 1;"><label for="trip_time">Saat:</label><input type="time" id="trip_time" name="trip_time" required></div></div>
                    <div style="display: flex; gap: 10px;"><div style="flex: 1;"><label for="price">Fiyat (TL):</label><input type="number" id="price" name="price" step="0.01" min="0" required></div><div style="flex: 1;"><label for="seat_count">Koltuk Sayısı:</label><input type="number" id="seat_count" name="seat_count" min="1" value="40" required></div></div>
                    <button type="submit" class="search-button" style="margin-top: 15px; height: auto; padding: 10px;">Seferi Kaydet</button>
                </form>
            </section>
            <section class="form-section">
                <h2>Mevcut Seferler</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="data-table">
                        <thead><tr style="border-bottom: 1px solid #ddd;"><th style="padding: 8px;">Kalkış - Varış</th><th style="padding: 8px;">Zaman</th><th style="padding: 8px;">Fiyat</th><th style="padding: 8px;">İşlemler</th></tr></thead>
                        <tbody>
                            <?php if (empty($trips)): ?> <tr><td colspan="4" style="padding: 8px; text-align: center;">Henüz sefer oluşturulmamış.</td></tr> <?php endif; ?>
                            <?php foreach ($trips as $trip): ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 8px;"><?php echo htmlspecialchars($trip['departure_location']); ?> - <?php echo htmlspecialchars($trip['arrival_location']); ?></td>
                                    <td style="padding: 8px; white-space: nowrap;"><?php echo date('d.m H:i', strtotime($trip['departure_time'])); ?></td>
                                    <td style="padding: 8px;"><?php echo number_format($trip['price'], 2); ?> TL</td>
                                    <td class="action-links" style="padding: 8px;"><a href="edit_trip.php?id=<?php echo $trip['id']; ?>">Düzenle</a> <a href="company_actions.php?action=delete_trip&id=<?php echo $trip['id']; ?>" class="delete-link" onclick="return confirm('Bu seferi silmek istediğinize emin misiniz?');">Sil</a></td>
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