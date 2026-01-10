<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

$query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$_SESSION["user"]]);
$user = $query->fetch();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Profilim | Tespih Mezat</title>
    <link rel="stylesheet" href="auth/auth.css">
    <style>
        .profile-box {
            background: #fff;
            max-width: 500px;
            width: 100%;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .profile-box h2 {
            margin-bottom: 10px;
        }

        .profile-info {
            margin: 15px 0;
            text-align: left;
        }

        .profile-info p {
            margin-bottom: 10px;
            font-size: 15px;
        }

        .profile-actions a {
            display: block;
            margin-top: 10px;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
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

    <div class="profile-box">
        <h2>👤 Profilim</h2>
        <p>Hoş geldin, <strong>
                <?= htmlspecialchars($user["name"]) ?>
            </strong></p>

        <div class="profile-info">
            <p><strong>E-posta:</strong>
                <?= htmlspecialchars($user["email"]) ?>
            </p>
            <p><strong>Üyelik Tarihi:</strong>
                <?= $user["created_at"] ?>
            </p>
        </div>

        <div class="profile-actions">
            <a href="index.html" class="home">Anasayfa</a>
            <a href="auth/logout.php" class="logout">Çıkış Yap</a>
        </div>
    </div>

</body>

</html>