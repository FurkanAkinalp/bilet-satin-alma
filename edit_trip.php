<?php
include 'database.php';
require_role('company_admin');

$trip_id = $_GET['id'] ?? null;
$company_id = $_SESSION['company_id'];

if (!$trip_id) { header("Location: company_panel.php?error=" . urlencode("Düzenlenecek sefer ID'si eksik.")); exit; }

try {
    $stmt = $db->prepare("SELECT * FROM \"Trips\" WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id, $company_id]);
    $trip = $stmt->fetch();
    if (!$trip) { throw new Exception("Sefer bulunamadı veya bu seferi düzenleme yetkiniz yok."); }
    $departure_datetime = new DateTime($trip['departure_time']);
    $trip_date = $departure_datetime->format('Y-m-d');
    $trip_time = $departure_datetime->format('H:i');
} catch (Exception $e) { header("Location: company_panel.php?error=" . urlencode($e->getMessage())); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><title>Sefer Düzenle</title><link rel="stylesheet" href="styles.css">
     <style> .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
</head>
<body>
    <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs (Firma Admin)</a></div>
        <nav class="nav"> <a href="company_panel.php">Sefer Yönetimi</a> <a href="logout.php" class="nav-button">Çıkış Yap</a> </nav>
    </header>
    <main class="panel-container" style="max-width: 700px;">
        <h1>Seferi Düzenle</h1>
         <?php if(isset($_GET['error'])): ?> <p class="error-message">Hata: <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p> <?php endif; ?>
        <form action="company_actions.php" method="POST">
            <input type="hidden" name="action" value="update_trip">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($trip['id']); ?>">
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;"><label for="departure_location">Kalkış Yeri:</label><input type="text" id="departure_location" name="departure_location" value="<?php echo htmlspecialchars($trip['departure_location']); ?>" required></div>
                <div class="form-group" style="flex: 1;"><label for="arrival_location">Varış Yeri:</label><input type="text" id="arrival_location" name="arrival_location" value="<?php echo htmlspecialchars($trip['arrival_location']); ?>" required></div>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;"><label for="trip_date">Tarih:</label><input type="date" id="trip_date" name="trip_date" value="<?php echo $trip_date; ?>" required></div>
                <div class="form-group" style="flex: 1;"><label for="trip_time">Saat:</label><input type="time" id="trip_time" name="trip_time" value="<?php echo $trip_time; ?>" required></div>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;"><label for="price">Fiyat (TL):</label><input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($trip['price']); ?>" required></div>
                <div class="form-group" style="flex: 1;"><label for="seat_count">Koltuk Sayısı:</label><input type="number" id="seat_count" name="seat_count" min="1" value="<?php echo htmlspecialchars($trip['seat_count']); ?>" required></div>
            </div>
            <button type="submit" class="search-button" style="margin-top: 15px; height: auto; padding: 12px; width: auto;">Seferi Güncelle</button>
            <a href="company_panel.php" style="margin-left: 10px; color: #6c757d;">İptal</a>
        </form>
    </main>
</body>
</html>