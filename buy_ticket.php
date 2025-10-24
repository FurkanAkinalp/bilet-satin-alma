<?php
include 'database.php';

// 1. Döküman kuralı: Giriş yapmamışsa (Ziyaretçi), login'e yolla [cite: 18]
// protect_page() fonksiyonu bu yönlendirmeyi otomatik yapar.
protect_page();

// 2. Döküman kuralı: Sadece 'user' (Yolcu) rolü bilet alabilir 
require_role('user'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Formdan gelen veriler
    $trip_id = $_POST['trip_id'];
    $seat_number = $_POST['seat_number'] ?? null;
    $coupon_code = trim($_POST['coupon_code']);
    $original_price = (float)$_POST['price'];
    
    // Session'dan kullanıcı bilgileri
    $user_id = $_SESSION['user_id'];
    
    // Koltuk seçilmemişse hata ver
    if (!$seat_number) {
        header("Location: trip_detail.php?id=" . $trip_id . "&error=" . urlencode("Lütfen bir koltuk seçin."));
        exit;
    }

    // Veritabanı işlemlerinde tutarlılık için Transaction başlat
    $db->beginTransaction();
    
    try {
        $final_price = $original_price;

        // 3. Kupon Kodu Kontrolü 
        if (!empty($coupon_code)) {
            $sql_coupon = "SELECT * FROM \"Coupons\" 
                           WHERE code = ? 
                             AND expiration_date >= date('now') 
                             AND usage_count < usage_limit";
            $stmt_coupon = $db->prepare($sql_coupon);
            $stmt_coupon->execute([$coupon_code]);
            $coupon = $stmt_coupon->fetch();

            if ($coupon) {
                // Kupon bulundu, indirimi uygula
                $final_price = $original_price * (1 - (float)$coupon['discount_rate']);
                
                // Kupon kullanım sayısını 1 artır
                $sql_update_coupon = "UPDATE \"Coupons\" SET usage_count = usage_count + 1 WHERE id = ?";
                $db->prepare($sql_update_coupon)->execute([$coupon['id']]);
            } else {
                throw new Exception("Geçersiz veya süresi dolmuş kupon kodu.");
            }
        }

        // 4. Kullanıcının Bakiyesini (Sanal Kredi) Kontrol Et 
        $stmt_user = $db->prepare("SELECT balance FROM \"User\" WHERE id = ?");
        $stmt_user->execute([$user_id]);
        $user_balance = (float)$stmt_user->fetchColumn();

        if ($user_balance < $final_price) {
            throw new Exception("Yetersiz bakiye. Hesabınızdaki kredi: " . $user_balance . " TL");
        }

        // 5. Satın Alma İşlemi
        
        // a) Kullanıcının bakiyesini (kredi) düşür 
        $sql_update_balance = "UPDATE \"User\" SET balance = balance - ? WHERE id = ?";
        $db->prepare($sql_update_balance)->execute([$final_price, $user_id]);

        // b) Bileti "Tickets" tablosuna kaydet
        // (Aynı koltuk tekrar satılırsa UNIQUE kısıtlaması sayesinde hata verecek)
        $sql_insert_ticket = "INSERT INTO \"Tickets\" (id, user_id, trip_id, seat_number, paid_price) 
                              VALUES (?, ?, ?, ?, ?)";
        $db->prepare($sql_insert_ticket)->execute([
            generate_uuid(),
            $user_id,
            $trip_id,
            (int)$seat_number,
            $final_price
        ]);

        // 6. Tüm işlemler başarılı, Transaction'ı onayla
        $db->commit();
        
        // Kullanıcıyı "Hesabım" sayfasına yönlendir
        header("Location: account.php?success=ticket_purchased");
        exit;

    } catch (Exception $e) {
        // 7. Hata oluştu (örn: koltuk dolmuş, bakiye yetersiz), tüm işlemleri geri al
        $db->rollBack();
        
        // Hata mesajıyla birlikte detay sayfasına geri dön
        $error_message = $e->getMessage();
        if (str_contains($error_message, 'UNIQUE constraint failed')) {
            $error_message = "Üzgünüz, siz seçerken bu koltuk satıldı. Lütfen başka bir koltuk seçin.";
        }
        
        header("Location: trip_detail.php?id=" . $trip_id . "&error=" . urlencode($error_message));
        exit;
    }
} else {
    // POST değilse ana sayfaya
    header("Location: index.php");
    exit;
}
?>