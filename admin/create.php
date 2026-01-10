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

$q = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$q->execute([$_SESSION["user"]]);
$u = $q->fetch(PDO::FETCH_ASSOC);

if (!$u || $u["role"] !== "admin") {
    die("Yetkisiz erişim");
}

/* --------------------
   FORM POST
-------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $desc = trim($_POST["description"]);
    $price = (int) $_POST["start_price"];

    /* 🔴 EN KRİTİK SATIR (ZAMAN SORUNUNU ÇÖZER) */
    $end = date("Y-m-d H:i:s", strtotime($_POST["end_time"]));

    /* KONTROLLER */
    if ($price % 100 !== 0) {
        die("Başlangıç fiyatı 100 TL katı olmalıdır.");
    }

    if (strtotime($end) <= time()) {
        die("Bitiş tarihi ileri bir zaman olmalıdır.");
    }

    /* --------------------
       RESİM YÜKLEME
    -------------------- */
    $image = null;

    if (!empty($_FILES["image"]["name"])) {
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($ext, $allowed)) {
            die("Sadece jpg, jpeg, png veya webp yüklenebilir.");
        }

        $image = uniqid("tespih_") . "." . $ext;
        move_uploaded_file(
            $_FILES["image"]["tmp_name"],
            "../images/auctions/" . $image
        );
    }

    /* --------------------
       INSERT (SADECE 1 KERE)
    -------------------- */
    $q = $pdo->prepare("
        INSERT INTO auctions
        (title, description, start_price, current_price, start_time, end_time, status, image)
        VALUES (?, ?, ?, ?, NOW(), ?, 'active', ?)
    ");

    $q->execute([
        $title,
        $desc,
        $price,
        $price,
        $end,
        $image
    ]);

    /* REDIRECT → ÇİFT EKLENME ENGEL */
    header("Location: index.php?ok=1");
    exit;
}
