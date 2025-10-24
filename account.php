<?php
include 'database.php';
// Sadece 'user' (Yolcu) rolü bu sayfayı görebilir
require_role('user'); 

$user_id = $_SESSION['user_id'];

try {
    // 1. Kullanıcı bilgilerini (özellikle bakiye) çek
    $stmt_user = $db->prepare("SELECT full_name, email, balance FROM \"User\" WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();

    // 2. Kullanıcının satın aldığı tüm biletleri çek
    // Bilet, Sefer ve Firma bilgilerini birleştir (JOIN)
    $sql_tickets = "SELECT 
                        T.id as ticket_id, T.seat_number, T.paid_price, T.purchase_date,
                        Tr.departure_location, Tr.arrival_location, Tr.departure_time,
                        C.company_name
                    FROM \"Tickets\" T
                    JOIN \"Trips\" Tr ON T.trip_id = Tr.id
                    JOIN \"Company\" C ON Tr.company_id = C.id
                    WHERE T.user_id = ?
                    ORDER BY Tr.departure_time DESC";
    
    $stmt_tickets = $db->prepare($sql_tickets);
    $stmt_tickets->execute([$user_id]);
    $tickets = $stmt_tickets->fetchAll();

} catch (PDOException $e) {
    $error_message = "Veriler alınırken hata oluştu: " . $e->getMessage();
    $tickets = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabım</title>
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

    <main class="panel-container">
        <h1>Hesabım</h1>

        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px;">
                Hata: <?php echo htmlspecialchars($_GET['error']); ?>
            </p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <?php 
                $message = "İşlem başarılı.";
                if ($_GET['success'] == 'ticket_purchased') $message = "Biletiniz başarıyla satın alındı!";
                if ($_GET['success'] == 'ticket_cancelled') $message = "Biletiniz iptal edildi ve ücret iadesi yapıldı.";
            ?>
            <p style="color:green; background-color: #e0ffe0; padding: 10px; border-radius: 6px;">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <section class="form-section user-info-card">
            <div>
                <strong>Ad Soyad:</strong>
                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
            </div>
            <div>
                <strong>E-posta:</strong>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <div class="user-balance">
                <strong>Bakiye (Sanal Kredi):</strong>
                <span><?php echo number_format($user['balance'], 2); ?> TL</span>
            </div>
        </section>

        <section class="form-section">
            <h2>Geçmiş Biletlerim</h2>
            <div class="ticket-list">
                <?php if (empty($tickets)): ?>
                    <p style="text-align: center;">Henüz satın alınmış biletiniz bulunmamaktadır.</p>
                <?php endif; ?>

                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="ticket-company"><?php echo htmlspecialchars($ticket['company_name']); ?></div>
                        <div class="ticket-details">
                            <span class="ticket-path">
                                <strong><?php echo htmlspecialchars($ticket['departure_location']); ?></strong> -> 
                                <?php echo htmlspecialchars($ticket['arrival_location']); ?>
                            </span>
                            <span class="ticket-time">
                                <?php echo date('d.m.Y H:i', strtotime($ticket['departure_time'])); ?>
                            </span>
                        </div>
                        <div class="ticket-seat">
                            Koltuk: <strong><?php echo $ticket['seat_number']; ?></strong>
                        </div>
                        <div class="ticket-actions">
                            <?php
                                // Kalkış zamanından 1 saat öncesinin timestamp'i
                                $cancellation_deadline = strtotime($ticket['departure_time']) - 3600; 
                                $current_time = time();
                            ?>

                            <?php if ($current_time < $cancellation_deadline): ?>
                                <a href="cancel_ticket.php?id=<?php echo $ticket['ticket_id']; ?>"
                                   class="cancel-button"
                                   onclick="return confirm('Bileti iptal etmek istediğinize emin misiniz? Ücret iadesi yapılacaktır.');">
                                   [cite_start]İptal Et [cite: 21]
                                </a>
                            <?php else: ?>
                                <span class="disabled-button">İptal Edilemez</span>
                            <?php endif; ?>
                            
                            <a href="generate_pdf.php?id=<?php echo $ticket['ticket_id']; ?>" class="pdf-button" target="_blank">
                                [cite_start]PDF İndir [cite: 23]
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        
    </main>

</body>
</html>