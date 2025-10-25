<?php
include 'database.php';

protect_page();
require_role('user');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $trip_id = $_POST['trip_id'] ?? null;
    $seat_number = $_POST['seat_number'] ?? null;
    $coupon_code = trim($_POST['coupon_code'] ?? '');
    $original_price = (float)($_POST['price'] ?? 0);
    $passenger_gender = $_POST['passenger_gender'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$trip_id || !$seat_number || !$passenger_gender || !$original_price) {
        $error = "Eksik bilgi gönderildi.";
        if (!$seat_number) $error = "Lütfen bir koltuk seçin.";
        if (!$passenger_gender) $error = "Lütfen yolcu cinsiyetini seçin.";
        header("Location: trip_detail.php?id=" . ($trip_id ?? '') . "&error=" . urlencode($error));
        exit;
    }

    $db->beginTransaction();
    try {
        $stmt_check_trip = $db->prepare("SELECT departure_time FROM \"Trips\" WHERE id = ?");
        $stmt_check_trip->execute([$trip_id]);
        $trip_time_str = $stmt_check_trip->fetchColumn();
        if (!$trip_time_str) { throw new Exception("Bilet alınmaya çalışılan sefer bulunamadı."); }
        $trip_timestamp = strtotime($trip_time_str); $current_timestamp = time();
        if ($trip_timestamp <= $current_timestamp) { throw new Exception("Bu seferin tarihi geçtiği için bilet alamazsınız."); }

        $final_price = $original_price;
        if (!empty($coupon_code)) {
            $sql_coupon = "SELECT * FROM \"Coupons\" WHERE code = ? AND expiration_date >= date('now') AND usage_count < usage_limit";
            $stmt_coupon = $db->prepare($sql_coupon); $stmt_coupon->execute([$coupon_code]); $coupon = $stmt_coupon->fetch();
            if ($coupon) {
                $final_price = $original_price * (1 - (float)$coupon['discount_rate']);
                $sql_update_coupon = "UPDATE \"Coupons\" SET usage_count = usage_count + 1 WHERE id = ?";
                $db->prepare($sql_update_coupon)->execute([$coupon['id']]);
            } else { throw new Exception("Geçersiz veya süresi dolmuş kupon kodu."); }
        }

        $stmt_user = $db->prepare("SELECT balance FROM \"User\" WHERE id = ?"); $stmt_user->execute([$user_id]); $user_balance = (float)$stmt_user->fetchColumn();
        if ($user_balance < $final_price) { throw new Exception("Yetersiz bakiye. Hesabınızdaki kredi: " . number_format($user_balance, 2) . " TL"); }

        $sql_update_balance = "UPDATE \"User\" SET balance = balance - ? WHERE id = ?"; $db->prepare($sql_update_balance)->execute([$final_price, $user_id]);
        $sql_insert_ticket = "INSERT INTO \"Tickets\" (id, user_id, trip_id, seat_number, paid_price, passenger_gender) VALUES (?, ?, ?, ?, ?, ?)";
        $db->prepare($sql_insert_ticket)->execute([ generate_uuid(), $user_id, $trip_id, (int)$seat_number, $final_price, $passenger_gender ]);
        $db->commit();
        header("Location: account.php?success=ticket_purchased"); exit;
    } catch (Exception $e) {
        $db->rollBack(); $error_message = $e->getMessage();
        if (str_contains($error_message, 'UNIQUE constraint failed')) { $error_message = "Üzgünüz, siz seçerken bu koltuk satıldı. Lütfen başka bir koltuk seçin."; }
        header("Location: trip_detail.php?id=" . $trip_id . "&error=" . urlencode($error_message)); exit;
    }
} else { header("Location: index.php"); exit; }
?>