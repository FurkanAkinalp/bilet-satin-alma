<?php
include 'database.php';

$departure = $_GET['departure_location'] ?? '';
$arrival = $_GET['arrival_location'] ?? '';
$date = $_GET['date'] ?? '';

$trips = [];
$error_message = '';

if (!empty($departure) && !empty($arrival) && !empty($date)) {
    try {
        $sql = "SELECT T.*, C.company_name FROM \"Trips\" T JOIN \"Company\" C ON T.company_id = C.id WHERE LOWER(T.departure_location) LIKE LOWER(?) AND LOWER(T.arrival_location) LIKE LOWER(?) AND date(T.departure_time) = ? ORDER BY T.departure_time ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['%' . $departure . '%', '%' . $arrival . '%', $date]);
        $trips = $stmt->fetchAll();
    } catch (PDOException $e) { $error_message = "Seferler aranırken bir hata oluştu: " . $e->getMessage(); }
} else { if (isset($_GET['departure_location'])) { $error_message = "Lütfen kalkış, varış ve tarih bilgilerini eksiksiz girin."; } }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Arama Sonuçları</title> <link rel="stylesheet" href="styles.css">
    <style> .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
</head>
<body>
    <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs</a></div>
        <nav class="nav">
             <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?> <a href="admin_panel.php">Admin Paneli</a>
                <?php elseif ($_SESSION['role'] == 'company_admin'): ?> <a href="company_panel.php">Firma Paneli</a>
                <?php else: ?> <a href="account.php">Hesabım / Biletlerim</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-button">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php">Giriş Yap</a> <a href="register.php" class="nav-button">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="content-area">
        <div class="search-widget" style="margin-bottom: 20px;">
            <form action="trip_list.php" method="GET" class="search-form">
                <div class="form-group"><label for="kalkis">Nereden?</label><input type="text" id="kalkis" name="departure_location" value="<?php echo htmlspecialchars($departure); ?>" required></div>
                <div class="form-group"><label for="varis">Nereye?</label><input type="text" id="varis" name="arrival_location" value="<?php echo htmlspecialchars($arrival); ?>" required></div>
                <div class="form-group"><label for="tarih">Tarih</label><input type="date" id="tarih" name="date" value="<?php echo htmlspecialchars($date ?: date('Y-m-d')); ?>" required></div>
                <button type="submit" class="search-button">Sefer Ara</button>
            </form>
        </div>
        <?php if (!empty($departure) && !empty($arrival)): ?> <h2>Arama Sonuçları: <?php echo htmlspecialchars($departure); ?> -> <?php echo htmlspecialchars($arrival); ?></h2> <?php endif; ?>
        <?php if ($error_message): ?> <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p> <?php endif; ?>
        <?php if (empty($trips) && !$error_message && !empty($departure)): ?> <p style="text-align: center; font-size: 18px;">Bu kriterlere uygun sefer bulunamadı.</p> <?php endif; ?>
        <div class="trip-list">
            <?php foreach ($trips as $trip): ?>
            <div class="trip-card">
                <div class="trip-company"><?php echo htmlspecialchars($trip['company_name']); ?></div>
                <div class="trip-time"><?php echo date('H:i', strtotime($trip['departure_time'])); ?></div>
                <div class="trip-path"><strong><?php echo htmlspecialchars($trip['departure_location']); ?></strong> -> <?php echo htmlspecialchars($trip['arrival_location']); ?></div>
                <div class="trip-price"><?php echo htmlspecialchars(number_format($trip['price'], 2)); ?> TL</div>
                <div class="trip-action"><a href="trip_detail.php?id=<?php echo $trip['id']; ?>" class="search-button" style="height: auto; padding: 10px 20px;">Koltuk Seç</a></div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>