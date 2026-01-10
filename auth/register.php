<?php
session_start();
require "../config/db.php";

if ($_POST) {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $query = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $query->execute([$name, $email, $password]);

    $_SESSION["user"] = $email;
    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Üye Ol | Tespih Mezat</title>
    <link rel="stylesheet" href="auth.css">
</head>

<body>

    <div class="auth-box">
        <h2>Üye Ol</h2>

        <form method="POST">
            <input type="text" name="name" placeholder="Ad Soyad" required>
            <input type="email" name="email" placeholder="E-posta" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button>Hesap Oluştur</button>
        </form>

        <p>Zaten üye misin? <a href="login.php">Giriş Yap</a></p>
    </div>

</body>

</html>