<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../config/db.php";

/* --------------------
   ADMIN KONTROL
-------------------- */
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

/* --------------------
   YENİ MEZAT EKLE
-------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $desc = trim($_POST["description"]);
    $price = (int) $_POST["start_price"];

    $end = date("Y-m-d H:i:s", strtotime($_POST["end_time"]));

    if ($price % 100 !== 0) {
        die("Başlangıç fiyatı 100 TL katı olmalıdır.");
    }

    if (strtotime($end) <= time()) {
        die("Bitiş tarihi ileri olmalıdır.");
    }

    $imageName = null;
    if (!empty($_FILES["image"]["name"])) {
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($ext, $allowed)) {
            die("Sadece jpg, jpeg, png, webp yüklenebilir.");
        }

        $imageName = uniqid("tespih_") . "." . $ext;
        move_uploaded_file(
            $_FILES["image"]["tmp_name"],
            "../images/auctions/" . $imageName
        );
    }

    $q = $pdo->prepare("
        INSERT INTO auctions
        (title, description, start_price, current_price, start_time, end_time, status, image)
        VALUES (?, ?, ?, ?, NOW(), ?, 'active', ?)
    ");
    $q->execute([$title, $desc, $price, $price, $end, $imageName]);

    header("Location: index.php?ok=1");
    exit;
}

/* --------------------
   TÜM MEZATLAR
-------------------- */
$auctions = $pdo->query("
    SELECT 
        a.*,
        u.name  AS winner_name,
        u.email AS winner_email
    FROM auctions a
    LEFT JOIN users u ON u.id = a.winner_user_id
    ORDER BY a.start_time DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Mezat Yönetimi</title>

    <style>
        body {
            background: #f4f6f8;
            font-family: Arial;
            margin: 0
        }

        .container {
            max-width: 1100px;
            margin: 40px auto
        }

        .card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
            margin-bottom: 40px
        }

        .success {
            background: #eafaf1;
            border: 1px solid #2ecc71;
            color: #2e7d32;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd
        }

        button {
            margin-top: 15px;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 10px;
            background: #6c63ff;
            color: #fff;
            font-weight: bold
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px
        }

        th {
            background: #fafafa
        }

        .status-active {
            color: green;
            font-weight: bold
        }

        .status-ended {
            color: red;
            font-weight: bold
        }

        img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- ✅ BAŞARI MESAJI -->
        <?php if (isset($_GET["ok"])): ?>
            <div class="success">✅ Mezat başarıyla başlatıldı.</div>
        <?php endif; ?>

        <div class="card">
            <h2>➕ Yeni Mezat Oluştur</h2>
            <form method="POST" enctype="multipart/form-data">
                <label>Ürün Adı</label>
                <input name="title" required>

                <label>Açıklama</label>
                <textarea name="description"></textarea>

                <label>Başlangıç Fiyatı</label>
                <input type="number" name="start_price" step="100" min="100" required>

                <label>Bitiş Tarihi</label>
                <input type="datetime-local" name="end_time" required>

                <label>Fotoğraf</label>
                <input type="file" name="image" required>

                <button>Mezatı Başlat</button>
            </form>
        </div>

        <div class="card">
            <h2>📦 Tüm Mezatlar</h2>

            <table>
                <tr>
                    <th>Resim</th>
                    <th>Ürün</th>
                    <th>Fiyat</th>
                    <th>Bitiş</th>
                    <th>Durum</th>
                    <th>Kazanan</th>
                </tr>

                <?php foreach ($auctions as $a): ?>
                    <?php $isEnded = (strtotime($a["end_time"]) <= time()); ?>
                    <tr>
                        <td>
                            <?php if ($a["image"]): ?>
                                <img src="../images/auctions/<?= $a["image"] ?>">
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($a["title"]) ?></td>
                        <td><?= number_format($a["current_price"]) ?> TL</td>
                        <td><?= date("d.m.Y H:i", strtotime($a["end_time"])) ?></td>

                        <td class="<?= $isEnded ? "status-ended" : "status-active" ?>">
                            <?= $isEnded ? "Bitti" : "Aktif" ?>
                        </td>

                        <td>
                            <?php if ($isEnded): ?>
                                <?= $a["winner_name"] ?: "Teklif yok" ?><br>
                                <small><?= $a["winner_email"] ?: "-" ?></small>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</body>

</html>