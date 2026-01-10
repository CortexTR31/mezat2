<?php
// 🇹🇷 PHP saatini Türkiye yap
date_default_timezone_set("Europe/Istanbul");

/*
|--------------------------------------------------------------------------
| Railway MySQL Bağlantı Bilgileri
|--------------------------------------------------------------------------
*/
$host = "ballast.proxy.rlwy.net";
$port = 20344;
$db = "railway";
$user = "root";
$pass = "TVyuCkNwmZBcGHLWXOUnDhRzdnBeOddI";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+03:00'"
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

/* =====================================================
   🔥 SÜRESİ BİTEN MEZATLARI KAPAT + KAZANANI BELİRLE
   (Railway + MySQL 8 uyumlu)
===================================================== */

$endedAuctions = $pdo->query("
    SELECT id
    FROM auctions
    WHERE status = 'active'
      AND end_time <= NOW()
")->fetchAll();

foreach ($endedAuctions as $a) {

    // 🔹 EN YÜKSEK TEKLİF
    $stmt = $pdo->prepare("
        SELECT user_id, amount
        FROM bids
        WHERE auction_id = ?
        ORDER BY amount DESC, created_at ASC
        LIMIT 1
    ");
    $stmt->execute([$a["id"]]);
    $winner = $stmt->fetch();

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
