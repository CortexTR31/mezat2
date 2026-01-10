<?php
session_start();
require "../config/db.php";

$error = "";

if ($_POST) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user"] = $user["id"];
        header("Location: ../index.php");
    } else {
        $error = "E-posta veya şifre hatalı";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Giriş Yap | Tespih Mezat</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>

    <div class="auth-box">
        <h2>Giriş Yap</h2>

        <?php if ($error): ?>
            <p style="color:red; text-align:center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="E-posta" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button>Giriş Yap</button>
        </form>

        <p>Hesabın yok mu? <a href="register.php">Üye Ol</a></p>
    </div>

</body>

</html>