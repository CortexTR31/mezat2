<?php
session_start();
require "config/db.php";

/* GİRİŞ KONTROLÜ */
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit;
}

$userId = $_SESSION["user"];
$error = "";

/* MEZAT ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Geçersiz mezat.");
}
$auctionId = (int) $_GET["id"];

/* MEZATI ÇEK */
$q = $pdo->prepare("
    SELECT * FROM auctions
    WHERE id = ?
");
$q->execute([$auctionId]);
$auction = $q->fetch(PDO::FETCH_ASSOC);

if (!$auction) {
    die("Mezat bulunamadı.");
}

/* SÜRE KONTROL */
if ($auction["status"] !== "active" || strtotime($auction["end_time"]) <= time()) {
    die("Bu mezat sona ermiştir.");
}

/* TEKLİF VERME */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amount = (int) $_POST["amount"];

    if ($amount % 100 !== 0) {
        $error = "Teklif 100 TL ve katları olmalıdır.";
    } elseif ($amount < $auction["current_price"] + 100) {
        $error = "Minimum artış 100 TL olmalıdır.";
    } else {

        /* SON TEKLİF SAHİBİ */
        $lastBid = $pdo->prepare("
            SELECT user_id
            FROM bids
            WHERE auction_id = ?
            ORDER BY amount DESC, created_at DESC
            LIMIT 1
        ");
        $lastBid->execute([$auctionId]);
        $last = $lastBid->fetch(PDO::FETCH_ASSOC);

        if ($last && $last["user_id"] == $userId) {
            $error = "Aynı kullanıcı arka arkaya teklif veremez.";
        } else {

            /* TEKLİF EKLE */
            $pdo->prepare("
                INSERT INTO bids (auction_id, user_id, amount, created_at)
                VALUES (?, ?, ?, NOW())
            ")->execute([$auctionId, $userId, $amount]);

            /* FİYATI GÜNCELLE */
            $pdo->prepare("
                UPDATE auctions
                SET current_price = ?
                WHERE id = ?
            ")->execute([$amount, $auctionId]);

            header("Location: auction.php?id=" . $auctionId);
            exit;
        }
    }
}

/* TEKLİF GEÇMİŞİ */
$bids = $pdo->prepare("
    SELECT b.amount, u.name, b.created_at
    FROM bids b
    JOIN users u ON u.id = b.user_id
    WHERE b.auction_id = ?
    ORDER BY b.amount DESC, b.created_at ASC
");
$bids->execute([$auctionId]);
$bidList = $bids->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($auction["title"]) ?> | Mezat</title>

    <style>
        body {
            background: #f4f6f8;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        header {
            background: #111;
            color: #fff;
        }

        .header {
            max-width: 1100px;
            margin: auto;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: #fff;
            margin-left: 15px;
            text-decoration: none;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
        }

        .image-box {
            height: 320px;
            background: #fafafa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .price {
            font-size: 26px;
            color: #27ae60;
            font-weight: bold;
        }

        .small {
            color: #666;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 10px;
            padding: 14px;
            background: #3498db;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .bid {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        @media(max-width:900px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="header">
            <strong>Tespih Mezat</strong>
            <nav>
                <a href="index.php">Anasayfa</a>
                <a href="/auth/logout.php">Çıkış</a>
            </nav>
        </div>
    </header>

    <div class="container">

        <!-- SOL -->
        <div class="card">
            <div class="image-box">
                <?php if ($auction["image"]): ?>
                    <img src="images/auctions/<?= htmlspecialchars($auction["image"]) ?>">
                <?php else: ?>
                    <span>Resim Yok</span>
                <?php endif; ?>
            </div>

            <h2><?= htmlspecialchars($auction["title"]) ?></h2>
            <p class="small"><?= nl2br(htmlspecialchars($auction["description"])) ?></p>
            <p class="small">Bitiş: <?= date("d.m.Y H:i", strtotime($auction["end_time"])) ?></p>
        </div>

        <!-- SAĞ -->
        <div class="card">
            <p>Başlangıç: <?= number_format($auction["start_price"]) ?> TL</p>
            <div class="price"><?= number_format($auction["current_price"]) ?> TL</div>

            <hr>

            <h3>💰 Teklif Ver</h3>

            <?php if ($error): ?>
                <p style="color:red"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="number" name="amount" min="<?= $auction["current_price"] + 100 ?>" step="100" required>
                <button>Teklif Ver</button>
            </form>

            <hr>

            <h3>📜 Teklif Geçmişi</h3>

            <?php if (!$bidList): ?>
                <p class="small">Henüz teklif yok.</p>
            <?php endif; ?>

            <?php foreach ($bidList as $b): ?>
                <div class="bid">
                    <strong><?= htmlspecialchars($b["name"]) ?></strong><br>
                    <?= number_format($b["amount"]) ?> TL
                    <div class="small"><?= date("d.m H:i:s", strtotime($b["created_at"])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

</body>

</html>