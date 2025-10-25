<?php
include 'database.php';
require_role('user');

$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) { header("Location: account.php?error=" . urlencode("Geçersiz bilet ID.")); exit; }

$db->beginTransaction();
try {
    $sql_ticket = "SELECT T.paid_price, Tr.departure_time FROM \"Tickets\" T JOIN \"Trips\" Tr ON T.trip_id = Tr.id WHERE T.id = ? AND T.user_id = ?";
    $stmt = $db->prepare($sql_ticket); $stmt->execute([$ticket_id, $user_id]); $ticket = $stmt->fetch();
    if (!$ticket) { throw new Exception("Bilet bulunamadı veya bu bileti iptal etme yetkiniz yok."); }

    $cancellation_deadline = strtotime($ticket['departure_time']) - 3600; $current_time = time();
    if ($current_time >= $cancellation_deadline) { throw new Exception("Seferin kalkış saatine 1 saatten az kaldığı için bilet iptal edilemez."); }

    $db->prepare("DELETE FROM \"Tickets\" WHERE id = ?")->execute([$ticket_id]);
    $refund_amount = (float)$ticket['paid_price'];
    $sql_refund = "UPDATE \"User\" SET balance = balance + ? WHERE id = ?"; $db->prepare($sql_refund)->execute([$refund_amount, $user_id]);
    $db->commit();
    header("Location: account.php?success=ticket_cancelled"); exit;
} catch (Exception $e) {
    $db->rollBack(); header("Location: account.php?error=" . urlencode($e->getMessage())); exit;
}
?>