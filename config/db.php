<?php
// 🇹🇷 PHP saatini Türkiye yap
date_default_timezone_set("Europe/Istanbul");

$host = "localhost";
$db = "gk_veritabani";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // 🇹🇷 MySQL saatini de TR yap (ÇOK KRİTİK)
    $pdo->exec("SET time_zone = '+03:00'");

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası");
}

/* =====================================================
   🔥 SÜRESİ BİTEN MEZATLARI KAPAT + KAZANANI BELİRLE
   (HER SAYFA YÜKLENİŞİNDE GÜVENLE ÇALIŞIR)
===================================================== */
$endedAuctions = $pdo->query("
    SELECT id
    FROM auctions
    WHERE status = 'active'
      AND end_time <= NOW()
")->fetchAll();

foreach ($endedAuctions as $a) {

    // 🔹 EN YÜKSEK TEKLİF
    $bid = $pdo->prepare("
        SELECT user_id, amount
        FROM bids
        WHERE auction_id = ?
        ORDER BY amount DESC, created_at ASC
        LIMIT 1
    ");
    $bid->execute([$a["id"]]);
    $winner = $bid->fetch();

    if ($winner) {
        // 🏆 KAZANANI YAZ
        $pdo->prepare("
            UPDATE auctions
            SET status = 'finished',
                winner_user_id = ?,
                finished_at = NOW()
            WHERE id = ?
        ")->execute([
                    $winner["user_id"],
                    $a["id"]
                ]);
    } else {
        // ❌ TEKLİF YOKSA SADECE KAPAT
        $pdo->prepare("
            UPDATE auctions
            SET status = 'finished',
                finished_at = NOW()
            WHERE id = ?
        ")->execute([$a["id"]]);
    }
}
