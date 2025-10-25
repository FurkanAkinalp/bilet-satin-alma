<?php
include 'database.php';
require_role('company_admin');

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$id = $_GET['id'] ?? $_POST['id'] ?? null;
$company_id = $_SESSION['company_id'];

try {
    if ($action == 'create_trip' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $uuid = generate_uuid();
        $departure_location = $_POST['departure_location'];
        $arrival_location = $_POST['arrival_location'];
        $departure_time = $_POST['trip_date'] . ' ' . $_POST['trip_time'];
        $price = (float)$_POST['price'];
        $seat_count = (int)$_POST['seat_count'];
        $stmt = $db->prepare("INSERT INTO \"Trips\" (id, company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uuid, $company_id, $departure_location, $arrival_location, $departure_time, $price, $seat_count]);
    }
    elseif ($action == 'delete_trip' && $id) {
        $stmt = $db->prepare("DELETE FROM \"Trips\" WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $company_id]);
    }
    elseif ($action == 'update_trip' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        if (!$id) { throw new Exception("Güncellenecek sefer ID'si eksik."); }
        $departure_location = $_POST['departure_location'];
        $arrival_location = $_POST['arrival_location'];
        $departure_time = $_POST['trip_date'] . ' ' . $_POST['trip_time'];
        $price = (float)$_POST['price'];
        $seat_count = (int)$_POST['seat_count'];
        $stmt = $db->prepare("UPDATE \"Trips\" SET departure_location = ?, arrival_location = ?, departure_time = ?, price = ?, seat_count = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([$departure_location, $arrival_location, $departure_time, $price, $seat_count, $id, $company_id]);
    }
    else { throw new Exception("Geçersiz işlem isteği veya eksik parametre."); }
} catch (PDOException | Exception $e) {
    header("Location: company_panel.php?error=" . urlencode($e->getMessage()));
    exit;
}
header("Location: company_panel.php?success=true");
exit;
?>