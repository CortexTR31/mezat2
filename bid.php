<?php
session_start();
require "config/db.php";

if (!isset($_SESSION["user"])) {
    die("Giriş yapmalısınız");
}

$user_id = $_SESSION["user"];
$auction_id = (int) $_POST["auction_id"];
$amount = (int) $_POST["amount"];

/* MEZATI ÇEK */
$q = $pdo->prepare("SELECT * FROM auctions WHERE id=?");
$q->execute([$auction_id]);
$auction = $q->fetch(PDO::FETCH_ASSOC);

if (!$auction || $auction["status"] !== "active") {
    die("Mezat aktif değil");
}

/* BİTTİ Mİ */
if (strtotime($auction["end_time"]) <= time()) {
    die("Mezat sona erdi");
}

/* MİN ARTIŞ */
if ($amount < $auction["current_price"] + 100) {
    die("Minimum artış 100 TL");
}

/* SON TEKLİF SAHİBİ */
$lastBid = $pdo->prepare("
    SELECT user_id 
    FROM bids 
    WHERE auction_id=?
    ORDER BY amount DESC, created_at DESC
    LIMIT 1
");
$lastBid->execute([$auction_id]);
$last = $lastBid->fetch(PDO::FETCH_ASSOC);

/* AYNI KULLANICI ENGEL */
if ($last && $last["user_id"] == $user_id) {
    die("Arka arkaya teklif veremezsiniz");
}

/* TEKLİF EKLE */
$pdo->prepare("
    INSERT INTO bids (auction_id, user_id, amount, created_at)
    VALUES (?, ?, ?, NOW())
")->execute([$auction_id, $user_id, $amount]);

/* FİYATI GÜNCELLE */
$pdo->prepare("
    UPDATE auctions SET current_price=? WHERE id=?
")->execute([$amount, $auction_id]);

header("Location: auction.php?id=$auction_id");
exit;
