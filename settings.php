<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

$q = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$q->execute([$_SESSION["user"]]);
$user = $q->fetch();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Ayarlar | Tespih Mezat</title>
    <link rel="stylesheet" href="auth/auth.css">
    <style>
        .settings-box {
            background: #fff;
            max-width: 500px;
            width: 100%;
            padding: 30px;
            border-radius: 10px;
        }

        .settings-box h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .settings-box p {
            margin-bottom: 10px;
        }

        .settings-actions a {
            display: block;
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
        }

        .logout {
            background: #111;
            color: #fff;
        }

        .home {
            background: #f4c430;
            color: #000;
        }
    </style>
</head>

<body>

    <div class="settings-box">
        <h2>⚙️ Hesap Ayarları</h2>

        <p><strong>Ad Soyad:</strong>
            <?= htmlspecialchars($user["name"]) ?>
        </p>
        <p><strong>E-posta:</strong>
            <?= htmlspecialchars($user["email"]) ?>
        </p>
        <p><strong>Üyelik Tarihi:</strong>
            <?= $user["created_at"] ?>
        </p>

        <div class="settings-actions">
            <a href="index.php" class="home">Anasayfa</a>
            <a href="auth/logout.php" class="logout">Çıkış Yap</a>
        </div>
    </div>

</body>

</html>