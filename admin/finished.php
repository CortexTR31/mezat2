<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../config/db.php";

/* ADMIN KONTROL */
if (!isset($_SESSION["user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$q = $pdo->prepare("SELECT role FROM users WHERE id=?");
$q->execute([$_SESSION["user"]]);
$u = $q->fetch(PDO::FETCH_ASSOC);

if (!$u || $u["role"] !== "admin") {
    die("Yetkisiz erişim");
}

/* BİTMİŞ MEZATLAR + KAZANAN */
$auctions = $pdo->query("
    SELECT 
        a.*,
        (
            SELECT u.name
            FROM bids b
            JOIN users u ON u.id = b.user_id
            WHERE b.auction_id = a.id
            ORDER BY b.amount DESC, b.created_at ASC
            LIMIT 1
        ) AS winner_name,
        (
            SELECT u.email
            FROM bids b
            JOIN users u ON u.id = b.user_id
            WHERE b.auction_id = a.id
            ORDER BY b.amount DESC, b.created_at ASC
            LIMIT 1
        ) AS winner_email
    FROM auctions a
    WHERE a.end_time <= NOW()
    ORDER BY a.end_time DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Bitmiş Mezatlar</title>

    <style>
        body {
            background: #f4f6f8;
            font-family: Arial;
            margin: 0;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
        }

        .card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        th {
            background: #fafafa;
        }

        .status {
            color: red;
            font-weight: bold;
        }

        img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">

        <a href="index.php" class="back">← Admin Panel</a>

        <div class="card">
            <h2>⏹ Bitmiş Mezatlar</h2>

            <table>
                <tr>
                    <th>Resim</th>
                    <th>Ürün</th>
                    <th>Son Fiyat</th>
                    <th>Bitiş</th>
                    <th>Durum</th>
                    <th>Kazanan</th>
                </tr>

                <?php foreach ($auctions as $a): ?>
                    <tr>
                        <td>
                            <?php if ($a["image"]): ?>
                                <img src="../images/auctions/<?= htmlspecialchars($a["image"]) ?>">
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($a["title"]) ?>
                        </td>
                        <td>
                            <?= number_format($a["current_price"]) ?> TL
                        </td>
                        <td>
                            <?= date("d.m.Y H:i", strtotime($a["end_time"])) ?>
                        </td>
                        <td class="status">Bitti</td>

                        <td>
                            <?= $a["winner_name"] ?: "Teklif yok" ?><br>
                            <small>
                                <?= $a["winner_email"] ?: "-" ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </table>
        </div>

    </div>
</body>

</html>