<?php
include 'database.php';
header('Content-Type: application/json');

$response = [ 'valid' => false, 'message' => 'Geçersiz istek.', 'discounted_price_formatted' => null ];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['code']) && isset($_POST['price'])) {
    $coupon_code = trim($_POST['code']);
    $original_price = (float)$_POST['price'];

    if (empty($coupon_code)) { $response['message'] = 'Kupon kodu boş olamaz.'; echo json_encode($response); exit; }

    try {
        $sql_coupon = "SELECT * FROM \"Coupons\" WHERE code = ? AND expiration_date >= date('now') AND usage_count < usage_limit";
        $stmt_coupon = $db->prepare($sql_coupon); $stmt_coupon->execute([$coupon_code]); $coupon = $stmt_coupon->fetch();
        if ($coupon) {
            $discount_rate = (float)$coupon['discount_rate']; $discounted_price = $original_price * (1 - $discount_rate);
            $response['valid'] = true; $response['message'] = 'Kupon geçerli.'; $response['discounted_price_formatted'] = number_format($discounted_price, 2);
        } else { $response['message'] = 'Geçersiz, süresi dolmuş veya limiti aşılmış kupon kodu.'; }
    } catch (PDOException $e) { $response['message'] = 'Veritabanı hatası: ' . $e->getMessage(); }
}
echo json_encode($response);
exit;
?>