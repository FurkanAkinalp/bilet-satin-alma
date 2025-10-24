<?php
include 'database.php';
require_role('admin');

$action = $_GET['action'] ?? null;

try {
    if ($action == 'create_company' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = generate_uuid();
        $company_name = $_POST['company_name'];
        
        $stmt = $db->prepare("INSERT INTO \"Company\" (id, company_name) VALUES (?, ?)");
        $stmt->execute([$id, $company_name]);
    }

    if ($action == 'create_company_admin' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = generate_uuid();
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'company_admin'; 
        $company_id = $_POST['company_id'];
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = $db->prepare(
            "INSERT INTO \"User\" (id, full_name, email, password, role, company_id, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $full_name, $email, $hashed_password, $role, $company_id, $created_at]);
    }

    if ($action == 'create_coupon' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = generate_uuid();
        $code = $_POST['code'];
        $discount_rate = (float)$_POST['discount_rate'] / 100.0;
        $usage_limit = (int)$_POST['usage_limit'];
        $expiration_date = $_POST['expiration_date'];
        
        $stmt = $db->prepare(
            "INSERT INTO \"Coupons\" (id, code, discount_rate, usage_limit, expiration_date) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$id, $code, $discount_rate, $usage_limit, $expiration_date]);
    }

} catch (PDOException $e) {
    header("Location: admin_panel.php?error=" . urlencode($e->getMessage()));
    exit;
}

header("Location: admin_panel.php?success=true");
exit;
?>