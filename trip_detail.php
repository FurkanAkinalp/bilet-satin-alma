<?php
include 'database.php';

$trip_id = $_GET['id'] ?? null;
if (!$trip_id) { header("Location: index.php"); exit; }

try {
    $sql_trip = "SELECT T.*, C.company_name FROM \"Trips\" T JOIN \"Company\" C ON T.company_id = C.id WHERE T.id = ?";
    $stmt_trip = $db->prepare($sql_trip);
    $stmt_trip->execute([$trip_id]);
    $trip = $stmt_trip->fetch();
    if (!$trip) { die("Sefer bulunamadı."); }

    $sql_tickets = "SELECT seat_number, passenger_gender FROM \"Tickets\" WHERE trip_id = ?";
    $stmt_tickets = $db->prepare($sql_tickets);
    $stmt_tickets->execute([$trip_id]);
    $sold_seats = $stmt_tickets->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) { $db_error = $e->getMessage(); }

function render_seat($seat_num, $sold_seats) {
    $status = 'available'; $gender_class = '';
    if (isset($sold_seats[$seat_num])) {
        $status = 'sold';
        $gender = $sold_seats[$seat_num];
        if ($gender == 'male') { $gender_class = 'male'; }
        elseif ($gender == 'female') { $gender_class = 'female'; }
    }
    echo "<div class='seat-wrapper' style='grid-area: s{$seat_num}'>";
    echo "  <label class=\"seat {$status} {$gender_class}\">";
    echo "      <input type=\"radio\" name=\"seat_number\" value=\"{$seat_num}\" ".($status == 'sold' ? 'disabled' : 'required').">";
    echo "      <span class='seat-number'>{$seat_num}</span>";
    echo "  </label>";
    echo "</div>";
}
$original_price_formatted = number_format($trip['price'], 2);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koltuk Seçimi - YokMuOtobüs</title> <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <form id="buy-ticket-form" action="buy_ticket.php" method="POST">
            <div class="search-widget" style="padding: 0; background: none; box-shadow: none;">
                <div class="trip-detail-layout">
                    <div class="bus-column">
                        <div class="search-widget" style="padding: 20px;">
                             <h1>Koltuk Seçimi</h1>
                             <p class="trip-info">Sefer Bilgisi: ARACIMIZ ANADOLU YAKASINA UĞRAMAYIP DOĞRUDAN ÇEVREYOLU ÜZERİNDEN DEVAM ETMEKTEDİR.</p>
                             <div class="trip-card" style="border: none; box-shadow: none; padding: 0; margin-bottom: 20px; justify-content: space-between;"><div><?php echo htmlspecialchars($trip['company_name']); ?></div><div><?php echo htmlspecialchars($trip['departure_location']); ?> -> <?php echo htmlspecialchars($trip['arrival_location']); ?></div><div style="color: #007bff; font-weight: bold;"><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></div></div>
                            <div class="bus-top-view">
                                <div class="bus-front"><div class="driver-seat"></div></div>
                                <div class="bus-layout">
                                    <?php render_seat(1, $sold_seats); ?> <?php render_seat(2, $sold_seats); ?> <?php render_seat(3, $sold_seats); ?> <?php render_seat(4, $sold_seats); ?> <?php render_seat(5, $sold_seats); ?> <?php render_seat(6, $sold_seats); ?> <?php render_seat(7, $sold_seats); ?> <?php render_seat(8, $sold_seats); ?> <?php render_seat(9, $sold_seats); ?> <?php render_seat(10, $sold_seats); ?> <?php render_seat(11, $sold_seats); ?> <?php render_seat(12, $sold_seats); ?> <?php render_seat(13, $sold_seats); ?> <?php render_seat(14, $sold_seats); ?> <?php render_seat(15, $sold_seats); ?> <?php render_seat(16, $sold_seats); ?> <?php render_seat(17, $sold_seats); ?> <?php render_seat(18, $sold_seats); ?> <?php render_seat(19, $sold_seats); ?> <?php render_seat(20, $sold_seats); ?> <?php render_seat(21, $sold_seats); ?> <?php render_seat(22, $sold_seats); ?> <?php render_seat(23, $sold_seats); ?> <?php render_seat(24, $sold_seats); ?> <?php render_seat(25, $sold_seats); ?> <?php render_seat(26, $sold_seats); ?> <?php render_seat(27, $sold_seats); ?> <?php render_seat(28, $sold_seats); ?> <?php render_seat(29, $sold_seats); ?> <?php render_seat(30, $sold_seats); ?> <?php render_seat(31, $sold_seats); ?> <?php render_seat(32, $sold_seats); ?> <?php render_seat(33, $sold_seats); ?> <?php render_seat(34, $sold_seats); ?> <?php render_seat(35, $sold_seats); ?> <?php render_seat(36, $sold_seats); ?> <?php render_seat(37, $sold_seats); ?> <?php render_seat(38, $sold_seats); ?> <?php render_seat(39, $sold_seats); ?> <?php render_seat(40, $sold_seats); ?> <?php render_seat(41, $sold_seats); ?>
                                </div>
                            </div>
                            <div class="seat-legend">
                                <div class="legend-item"><span class="seat-example female"></span> Dolu Koltuk - Kadın</div> <div class="legend-item"><span class="seat-example male"></span> Dolu Koltuk - Erkek</div> <div class="legend-item"><span class="seat-example selected"></span> Seçilen Koltuk</div> <div class="legend-item"><span class="seat-example available"></span> Boş Koltuk</div>
                            </div>
                        </div>
                    </div>
                    <div class="sidebar-column">
                        <div class="search-widget">
                             <?php if (isset($_GET['error'])): ?> <p class="error-message">Hata: <?php echo htmlspecialchars($_GET['error']); ?></p> <?php endif; ?>
                             <?php if (isset($db_error)): ?> <p class="error-message">DB Hatası: <?php echo htmlspecialchars($db_error); ?></p> <?php endif; ?>
                            <p class="sidebar-title">Lütfen soldan koltuk seçin.</p>
                            <hr style="border: 0; border-top: 1px solid #f0f0f0; margin: 20px 0;">
                            <div class="sidebar-trip"><span><?php echo htmlspecialchars($trip['company_name']); ?></span><span><?php echo htmlspecialchars($trip['departure_location']); ?> -> <?php echo htmlspecialchars($trip['arrival_location']); ?></span><span><?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></span></div>
                            <hr style="border: 0; border-top: 1px solid #f0f0f0; margin: 20px 0;">
                            <input type="hidden" name="trip_id" value="<?php echo htmlspecialchars($trip['id']); ?>">
                            <input type="hidden" id="original_price_value" name="price" value="<?php echo htmlspecialchars($trip['price']); ?>">
                            <div class="form-group">
                                <label>Yolcu Cinsiyeti:</label>
                                <div class="gender-options"> <label><input type="radio" name="passenger_gender" value="female" required> Kadın</label> <label><input type="radio" name="passenger_gender" value="male" required> Erkek</label> </div>
                            </div>
                            <div class="form-group"> <label for="coupon_code">İndirim Kuponu:</label> <input type="text" name="coupon_code" id="coupon_code" placeholder="Varsa kupon kodunuz"> <span id="coupon-feedback" style="font-size: 13px; margin-top: 5px; display: block;"></span> </div>
                            <div class="sidebar-price"> <span>Toplam Tutar:</span> <strong id="price-display"> <span id="original-price"><?php echo $original_price_formatted; ?> TL</span> <span id="discounted-price" style="display: none; color: green;"></span> </strong> </div>
                            <button type="submit" class="sidebar-button">ONAYLA VE DEVAM ET</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
    <script>
        $(document).ready(function() {
            $('#coupon_code').on('blur', function() {
                var couponCode = $(this).val().trim(); var originalPrice = parseFloat($('#original_price_value').val()); var feedbackSpan = $('#coupon-feedback'); var originalPriceSpan = $('#original-price'); var discountedPriceSpan = $('#discounted-price');
                feedbackSpan.text('').removeClass('success error');
                if (couponCode === '') { originalPriceSpan.removeClass('strikethrough').show(); discountedPriceSpan.hide().text(''); return; }
                feedbackSpan.text('Kupon kontrol ediliyor...');
                $.ajax({ url: 'check_coupon.php', method: 'POST', data: { code: couponCode, price: originalPrice }, dataType: 'json',
                    success: function(response) {
                        if (response.valid) { feedbackSpan.text('Kupon uygulandı!').addClass('success'); originalPriceSpan.addClass('strikethrough'); discountedPriceSpan.text(response.discounted_price_formatted + ' TL').show(); }
                        else { feedbackSpan.text(response.message).addClass('error'); originalPriceSpan.removeClass('strikethrough').show(); discountedPriceSpan.hide().text(''); }
                    },
                    error: function() { feedbackSpan.text('Kupon kontrolü sırasında hata oluştu.').addClass('error'); originalPriceSpan.removeClass('strikethrough').show(); discountedPriceSpan.hide().text(''); }
                });
            });
        });
    </script>
</body>
</html>