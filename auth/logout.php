<?php
session_start();
session_unset();
session_destroy();
header("Refresh:2; url=../index.php");
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Çıkış Yapıldı</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: Arial;
        }

        .box {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
        }
    </style>
</head>

<body>

    <div class="box">
        <h2>Çıkış Yapıldı</h2>
        <p>Oturumunuz güvenli şekilde kapatıldı.</p>
        <p>Anasayfaya yönlendiriliyorsunuz...</p>
    </div>

</body>

</html>