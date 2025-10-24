<?php
include 'database.php'; 

// 1. URL'den seferin ID'sini al
$trip_id = $_GET['id'] ?? null;
if (!$trip_id) {
    header("Location: index.php");
    exit;
}

try {
    // 2. Sefer bilgilerini ve firma adını çek
    $sql_trip = "SELECT T.*, C.company_name 
                 FROM \"Trips\" T
                 JOIN \"Company\" C ON T.company_id = C.id
                 WHERE T.id = ?";
    $stmt_trip = $db->prepare($sql_trip);
    $stmt_trip->execute([$trip_id]);
    $trip = $stmt_trip->fetch();

    if (!$trip) {
        die("Sefer bulunamadı.");
    }

    // 3. Bu sefere ait satılmış koltuk numaralarını (Tickets tablosundan) çek
    $sql_tickets = "SELECT seat_number FROM \"Tickets\" WHERE trip_id = ?";
    $stmt_tickets = $db->prepare($sql_tickets);
    $stmt_tickets->execute([$trip_id]);
    
    // Satılmış koltukları [1, 5, 8] gibi bir diziye aktar
    $sold_seats = $stmt_tickets->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koltuk Seçimi</title>
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

    <main class="content-area">
        <div class="search-widget">
            <h1 style="margin-bottom: 10px;">Koltuk Seçimi</h1>
            <div class="trip-card" style="border: none; box-shadow: none; padding: 0; margin-bottom: 20px;">
                <div class="trip-company"><?php echo htmlspecialchars($trip['company_name']); ?></div>
                <div class="trip-path">
                    <strong><?php echo htmlspecialchars($trip['departure_location']); ?></strong> ->
                    <?php echo htmlspecialchars($trip['arrival_location']); ?>
                </div>
                <div class="trip-time"><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></div>
            </div>

            <hr style="border: 0; border-top: 1px solid #f0f0f0; margin-bottom: 20px;">

            <form action="buy_ticket.php" method="POST">
                <input type="hidden" name="trip_id" value="<?php echo htmlspecialchars($trip['id']); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($trip['price']); ?>">

                <h2>Koltuk Seçin:</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <p style="color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px;">
                        Hata: <?php echo htmlspecialchars($_GET['error']); ?>
                    </p>
                <?php endif; ?>

                <div class="seat-map">
                    <?php for ($i = 1; $i <= $trip['seat_count']; $i++): ?>
                        <?php
                            // Koltuğun satılıp satılmadığını kontrol et
                            $is_sold = in_array($i, $sold_seats);
                        ?>
                        <label class="seat <?php echo $is_sold ? 'sold' : ''; ?>">
                            <input type="radio" name="seat_number" value="<?php echo $i; ?>" <?php echo $is_sold ? 'disabled' : 'required'; ?>>
                            <span><?php echo $i; ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
                
                <div class="purchase-summary">
                    <div class="form-group">
                        <label for="coupon_code">İndirim Kuponu:</label>
                        <input type="text" name="coupon_code" id="coupon_code" placeholder="Varsa kupon kodunuz">
                    </div>
                    <div class="form-group" style="text-align: right;">
                        <span style="font-size: 18px; color: #555;">Sefer Fiyatı:</span>
                        <span style="font-size: 28px; font-weight: bold; color: #333; margin-left: 10px;">
                            <?php echo htmlspecialchars($trip['price']); ?> TL
                        </span>
                    </div>
                </div>

                <button type="submit" class="search-button" style="width: 100%; padding: 15px; font-size: 18px; margin-top: 20px;">
                    <?php echo isset($_SESSION['user_id']) ? 'Güvenli Ödeme Yap' : 'Satın Almak için Giriş Yapın'; ?>
                </T>
            </form>
        </div>
    </main>

</body>
</html>