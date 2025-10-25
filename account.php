<?php
include 'database.php';
require_role('user');

$user_id = $_SESSION['user_id'];
$error_message = $_GET['error'] ?? null;
$success_message_key = $_GET['success'] ?? null;

try {
    $stmt_user = $db->prepare("SELECT full_name, email, balance FROM \"User\" WHERE id = ?"); $stmt_user->execute([$user_id]); $user = $stmt_user->fetch();
    $sql_tickets = "SELECT T.id as ticket_id, T.seat_number, T.paid_price, T.purchase_date, Tr.departure_location, Tr.arrival_location, Tr.departure_time, C.company_name FROM \"Tickets\" T JOIN \"Trips\" Tr ON T.trip_id = Tr.id JOIN \"Company\" C ON Tr.company_id = C.id WHERE T.user_id = ? ORDER BY Tr.departure_time DESC";
    $stmt_tickets = $db->prepare($sql_tickets); $stmt_tickets->execute([$user_id]); $tickets = $stmt_tickets->fetchAll();
} catch (PDOException $e) { $error_message = "Veriler alınırken hata oluştu: " . $e->getMessage(); $tickets = []; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabım - YokMuOtobüs</title> <link rel="stylesheet" href="styles.css">
    <style> .success-message { color:green; background-color: #e0ffe0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } .error-message { color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px; margin-bottom: 15px; } </style>
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
    <main class="panel-container">
        <h1>Hesabım</h1>
        <?php if ($error_message): ?> <p class="error-message">Hata: <?php echo htmlspecialchars(urldecode($error_message)); ?></p> <?php endif; ?>
        <?php if ($success_message_key): ?>
            <?php $message = "İşlem başarılı."; if ($success_message_key == 'ticket_purchased') $message = "Biletiniz başarıyla satın alındı!"; if ($success_message_key == 'ticket_cancelled') $message = "Biletiniz iptal edildi ve ücret iadesi yapıldı."; ?>
            <p class="success-message"><?php echo $message; ?></p>
        <?php endif; ?>
        <section class="user-info-card">
            <div><strong>Ad Soyad:</strong><span><?php echo htmlspecialchars($user['full_name']); ?></span></div>
            <div><strong>E-posta:</strong><span><?php echo htmlspecialchars($user['email']); ?></span></div>
            <div class="user-balance"><strong>Bakiye (Sanal Kredi):</strong><span><?php echo number_format($user['balance'], 2); ?> TL</span></div>
        </section>
        <section class="form-section">
            <h2>Geçmiş Biletlerim</h2>
            <div class="ticket-list">
                <?php if (empty($tickets)): ?> <p style="text-align: center;">Henüz satın alınmış biletiniz bulunmamaktadır.</p> <?php endif; ?>
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="ticket-company"><?php echo htmlspecialchars($ticket['company_name']); ?></div>
                        <div class="ticket-details"><span class="ticket-path"><strong><?php echo htmlspecialchars($ticket['departure_location']); ?></strong> -> <?php echo htmlspecialchars($ticket['arrival_location']); ?></span><span class="ticket-time"><?php echo date('d.m.Y H:i', strtotime($ticket['departure_time'])); ?></span></div>
                        <div class="ticket-seat">Koltuk: <strong><?php echo $ticket['seat_number']; ?></strong></div>
                        <div class="ticket-actions">
                            <?php $cancellation_deadline = strtotime($ticket['departure_time']) - 3600; $current_time = time(); ?>
                            <?php if ($current_time < $cancellation_deadline): ?>
                                <a href="cancel_ticket.php?id=<?php echo $ticket['ticket_id']; ?>" class="cancel-button" onclick="return confirm('Bileti iptal etmek istediğinize emin misiniz? Ücret iadesi yapılacaktır.');">İptal Et</a>
                            <?php else: ?> <span class="disabled-button">İptal Edilemez</span> <?php endif; ?>
                            <a href="generate_pdf.php?id=<?php echo $ticket['ticket_id']; ?>" class="pdf-button" target="_blank">PDF İndir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>