<?php
session_start();
require "config/db.php";

/* KULLANICI */
$isLogin = false;
$isAdmin = false;
$initials = "";

if (isset($_SESSION["user"])) {
    $q = $pdo->prepare("SELECT name, role FROM users WHERE id=?");
    $q->execute([$_SESSION["user"]]);
    $user = $q->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $isLogin = true;
        $isAdmin = ($user["role"] === "admin");
        foreach (explode(" ", $user["name"]) as $n) {
            $initials .= mb_substr($n, 0, 1);
        }
        $initials = strtoupper($initials);
    }
}

/* AKTİF MEZATLAR */
$auctions = $pdo->query("
    SELECT a.*,
        (
            SELECT u.name
            FROM bids b
            JOIN users u ON u.id = b.user_id
            WHERE b.auction_id = a.id
            ORDER BY b.amount DESC, b.created_at ASC
            LIMIT 1
        ) AS last_bidder
    FROM auctions a
    WHERE a.status = 'active'
      AND a.end_time > NOW()
    ORDER BY a.end_time ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>GÖRKEM KAYMAZ TESPİH MEZAT</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #f4f6f8;
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
        }

        /* HEADER */
        header {
            background: linear-gradient(135deg, #111, #2c2c2c);
            color: #fff;
        }

        .header {
            max-width: 1200px;
            margin: auto;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #fff;
            margin-left: 16px;
            text-decoration: none;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .avatar {
            width: 38px;
            height: 38px;
            background: #27ae60;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 12px;
        }

        /* GRID */
        .auctions {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* CARD */
        .card {
            background: #fff;
            padding: 16px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            transition: transform .2s, box-shadow .2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 35px rgba(0, 0, 0, .12);
        }

        /* IMAGE */
        .image-box {
            width: 100%;
            height: 240px;
            border-radius: 10px;
            overflow: hidden;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
            margin-bottom: 12px;
        }

        .image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* TEXT */
        .card h3 {
            margin: 8px 0;
            font-size: 18px;
        }

        .desc {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        .price {
            color: #27ae60;
            font-weight: bold;
        }

        .meta {
            font-size: 14px;
            margin: 4px 0;
        }

        /* BUTTON */
        .btn {
            display: block;
            margin-top: 14px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
        }

        .btn:hover {
            opacity: .9;
        }
    </style>
</head>

<body>

    <header>
        <div class="header">
            <strong>GÖRKEM KAYMAZ TESPİH MEZAT</strong>
            <nav>
                <a href="index.php">Anasayfa</a>

                <?php if ($isLogin): ?>
                    <?php if ($isAdmin): ?>
                        <a href="admin/index.php">Admin</a>
                    <?php endif; ?>
                    <span class="avatar"><?= $initials ?></span>
                    <a href="/auth/logout.php">Çıkış</a>
                <?php else: ?>
                    <a href="auth/login.php">Giriş</a>
                    <a href="auth/register.php">Üye Ol</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <section class="auctions">
        <?php foreach ($auctions as $a): ?>
            <div class="card">

                <div class="image-box">
                    <?php if (!empty($a["image"]) && file_exists("images/auctions/" . $a["image"])): ?>
                        <img src="images/auctions/<?= htmlspecialchars($a["image"]) ?>">
                    <?php else: ?>
                        <span>Resim yok</span>
                    <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($a["title"]) ?></h3>

                <div class="desc"><?= nl2br(htmlspecialchars($a["description"])) ?></div>

                <div class="meta">Başlangıç: <?= number_format($a["start_price"]) ?> TL</div>
                <div class="meta">En Yüksek: <span class="price"><?= number_format($a["current_price"]) ?> TL</span></div>
                <div class="meta">Son Teklif: <strong><?= $a["last_bidder"] ?? "Yok" ?></strong></div>
                <div class="meta">⏳ <?= date("d.m.Y H:i", strtotime($a["end_time"])) ?></div>

                <a href="auction.php?id=<?= $a["id"] ?>" class="btn">Teklif Ver</a>
            </div>
        <?php endforeach; ?>
    </section>

</body>

</html>