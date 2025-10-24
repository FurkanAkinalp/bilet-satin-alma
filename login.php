<?php
include 'database.php'; 
$error_message = '';
$info_message = '';

if (isset($_GET['status']) && $_GET['status'] == 'registered') {
    $info_message = "Kayıt başarılı! Lütfen giriş yapın.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM \"User\" WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            // GÜNCELLEME: Firma Admini ise, firma ID'sini session'a kaydet
            if ($user['role'] == 'company_admin') {
                $_SESSION['company_id'] = $user['company_id'];
            }

            header("Location: index.php");
            exit;
        } else {
            $error_message = "E-posta veya şifre hatalı.";
        }
    } catch (PDOException $e) {
        $error_message = "Giriş hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="header">
        <div class="logo">BiletGO</div>
        <nav class="nav">
            <a href="login.php">Giriş Yap</a>
            <a href="register.php" class="nav-button">Kayıt Ol</a>
        </nav>
    </header>

    <main class="search-hero">
        <div class="search-widget" style="max-width: 500px;">
            <h2>Giriş Yap</h2>

            <?php if ($error_message): ?>
                <p style="color:red; background-color: #ffe0e0; padding: 10px; border-radius: 6px;">
                    <?php echo htmlspecialchars($error_message); ?>
                </p>
            <?php endif; ?>
            <?php if ($info_message): ?>
                <p style="color:green; background-color: #e0ffe0; padding: 10px; border-radius: 6px;">
                    <?php echo htmlspecialchars($info_message); ?>
                </p>
            <?php endif; ?>

            <form method="POST" style="text-align: left;">
                <div class="form-group">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Şifre:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="search-button" style="width: 100%; margin-top: 15px;">Giriş Yap</button>
            </form>
            <p style="margin-top: 20px;">Hesabınız yok mu? <a href="register.php">Kayıt Olun</a></p>
        </div>
    </main>

</body>
</html>