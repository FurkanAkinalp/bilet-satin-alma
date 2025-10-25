<?php
include 'database.php';
require_role('admin');

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$id = $_GET['id'] ?? $_POST['id'] ?? null;

try {
    if ($action == 'create_company' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $uuid = generate_uuid();
        $company_name = $_POST['company_name'];
        $stmt = $db->prepare("INSERT INTO \"Company\" (id, company_name) VALUES (?, ?)");
        $stmt->execute([$uuid, $company_name]);
    }
    elseif ($action == 'delete_company' && $id) {
        $stmt = $db->prepare("DELETE FROM \"Company\" WHERE id = ?");
        $stmt->execute([$id]);
    }
    elseif ($action == 'update_company' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $company_name = $_POST['company_name'];
        if (!$id) { throw new Exception("Güncellenecek firma ID'si eksik."); }
        $stmt = $db->prepare("UPDATE \"Company\" SET company_name = ? WHERE id = ?");
        $stmt->execute([$company_name, $id]);
    }
    elseif ($action == 'create_company_admin' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $uuid = generate_uuid();
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'company_admin';
        $company_id = $_POST['company_id'];
        $created_at = date('Y-m-d H:i:s');
        $stmt = $db->prepare("INSERT INTO \"User\" (id, full_name, email, password, role, company_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uuid, $full_name, $email, $hashed_password, $role, $company_id, $created_at]);
    }
    elseif ($action == 'delete_user' && $id) {
        $stmt = $db->prepare("DELETE FROM \"User\" WHERE id = ? AND role = 'company_admin'");
        $stmt->execute([$id]);
    }
    elseif ($action == 'create_coupon' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $uuid = generate_uuid();
        $code = $_POST['code'];
        $discount_rate = (float)$_POST['discount_rate'] / 100.0;
        $usage_limit = (int)$_POST['usage_limit'];
        $expiration_date = $_POST['expiration_date'];
        $stmt = $db->prepare("INSERT INTO \"Coupons\" (id, code, discount_rate, usage_limit, expiration_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$uuid, $code, $discount_rate, $usage_limit, $expiration_date]);
    }
    elseif ($action == 'delete_coupon' && $id) {
        $stmt = $db->prepare("DELETE FROM \"Coupons\" WHERE id = ?");
        $stmt->execute([$id]);
    }
    elseif ($action == 'update_coupon' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $code = $_POST['code'];
        $discount_rate = (float)$_POST['discount_rate'] / 100.0;
        $usage_limit = (int)$_POST['usage_limit'];
        $expiration_date = $_POST['expiration_date'];
        if (!$id) { throw new Exception("Güncellenecek kupon ID'si eksik."); }
        $stmt = $db->prepare("UPDATE \"Coupons\" SET code = ?, discount_rate = ?, usage_limit = ?, expiration_date = ? WHERE id = ?");
        $stmt->execute([$code, $discount_rate, $usage_limit, $expiration_date, $id]);
    }
    else { throw new Exception("Geçersiz işlem isteği veya eksik parametre."); }
} catch (PDOException | Exception $e) {
    header("Location: admin_panel.php?error=" . urlencode($e->getMessage()));
    exit;
}
header("Location: admin_panel.php?success=true");
exit;
?>