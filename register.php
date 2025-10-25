<?php
include 'database.php';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT id FROM \"User\" WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error_message = "Bu e-posta adresi zaten kayıtlı.";
        } else {
            $id = generate_uuid();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $company_id = null;
            $created_at = date('Y-m-d H:i:s');

            $stmt = $db->prepare(
                "INSERT INTO \"User\" (id, full_name, email, password, role, company_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([$id, $full_name, $email, $hashed_password, $role, $company_id, $created_at]);

            header("Location: login.php?status=registered");
            exit;
        }
    } catch (PDOException $e) {
        $error_message = "Kayıt hatası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - YokMuOtobüs</title> <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="header">
        <div class="logo"><a href="index.php">YokMuOtobüs</a></div>
        <nav class="nav"> <a href="login.php">Giriş Yap</a> <a href="index.php" class="nav-button">Ana Sayfa</a> </nav>
    </header>
    <main class="search-hero">
        <div class="search-widget" style="max-width: 500px;">
            <h2>Kayıt Ol</h2>
            <?php if ($error_message): ?> <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p> <?php endif; ?>
            <form method="POST" style="text-align: left;">
                <div class="form-group"><label for="full_name">İsim Soyisim:</label><input type="text" id="full_name" name="full_name" required></div>
                <div class="form-group"><label for="email">E-posta:</label><input type="email" id="email" name="email" required></div>
                <div class="form-group"><label for="password">Şifre:</label><input type="password" id="password" name="password" required></div>
                <button type="submit" class="search-button" style="width: 100%; margin-top: 15px; height: auto; padding: 12px;">Kayıt Ol</button>
            </form>
            <p style="margin-top: 20px;">Hesabınız var mı? <a href="login.php">Giriş Yapın</a></p>
        </div>
    </main>
</body>
</html>