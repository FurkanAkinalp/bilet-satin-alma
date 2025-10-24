<?php
include 'database.php';
// Sadece 'user' (Yolcu) rolü bilet iptal edebilir
require_role('user'); 

$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) {
    header("Location: account.php?error=" . urlencode("Geçersiz bilet ID."));
    exit;
}

// Tutarlılık için Transaction başlat
$db->beginTransaction();

try {
    // 1. İptal edilecek bileti ve ait olduğu seferi bul
    // GÜVENLİK: Biletin bu kullanıcıya ait olduğundan (%user_id%) emin ol
    $sql_ticket = "SELECT T.paid_price, Tr.departure_time 
                   FROM \"Tickets\" T
                   JOIN \"Trips\" Tr ON T.trip_id = Tr.id
                   WHERE T.id = ? AND T.user_id = ?";
    
    $stmt = $db->prepare($sql_ticket);
    $stmt->execute([$ticket_id, $user_id]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        throw new Exception("Bilet bulunamadı veya bu bileti iptal etme yetkiniz yok.");
    }

    // 2. Döküman Kuralı: Son 1 saat kuralı kontrolü
    $cancellation_deadline = strtotime($ticket['departure_time']) - 3600; // 1 saat = 3600 saniye
    $current_time = time();

    if ($current_time >= $cancellation_deadline) {
        throw new Exception("Seferin kalkış saatine 1 saatten az kaldığı için bilet iptal edilemez."); [cite: 24]
    }

    // 3. İptal İşlemi
    
    // a) Bileti "Tickets" tablosundan sil
    $db->prepare("DELETE FROM \"Tickets\" WHERE id = ?")->execute([$ticket_id]);

    // b) Döküman Kuralı: Bilet ücretini kullanıcının hesabına (balance) iade et
    $refund_amount = (float)$ticket['paid_price'];
    $sql_refund = "UPDATE \"User\" SET balance = balance + ? WHERE id = ?";
    $db->prepare($sql_refund)->execute([$refund_amount, $user_id]);

    // 4. Tüm işlemler başarılı, Transaction'ı onayla
    $db->commit();

    // Başarı mesajıyla hes_abım sayfasına yönlendir
    header("Location: account.php?success=ticket_cancelled");
    exit;

} catch (Exception $e) {
    // 5. Hata oluştu, işlemleri geri al
    $db->rollBack();
    
    // Hata mesajıyla hesap sayfasına dön
    header("Location: account.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>