<?php
include 'database.php';
// Bu dosyayı sadece 'company_admin' rolü çalıştırabilir
require_role('company_admin');

$action = $_GET['action'] ?? null;
$company_id = $_SESSION['company_id']; // Giriş yapan adminin firma ID'si

try {
    // 1. Yeni Sefer Oluşturma İşlemi
    if ($action == 'create_trip' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = generate_uuid();
        $departure_location = $_POST['departure_location'];
        $arrival_location = $_POST['arrival_location'];
        
        // Tarih ve Saati birleştirip DATETIME formatına getir
        $departure_time = $_POST['trip_date'] . ' ' . $_POST['trip_time'];
        
        $price = (float)$_POST['price'];
        $seat_count = (int)$_POST['seat_count'];

        $stmt = $db->prepare(
            "INSERT INTO \"Trips\" (id, company_id, departure_location, arrival_location, departure_time, price, seat_count) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $company_id, $departure_location, $arrival_location, $departure_time, $price, $seat_count]);
    }

    // 2. Sefer Silme İşlemi
    if ($action == 'delete_trip' && isset($_GET['id'])) {
        $trip_id = $_GET['id'];
        
        // GÜVENLİK: Adminin sadece kendi firmasının  seferini silebildiğinden emin ol
        $stmt = $db->prepare("DELETE FROM \"Trips\" WHERE id = ? AND company_id = ?");
        $stmt->execute([$trip_id, $company_id]);
    }

} catch (PDOException $e) {
    header("Location: company_panel.php?error=" . urlencode($e->getMessage()));
    exit;
}

header("Location: company_panel.php?success=true");
exit;
?>