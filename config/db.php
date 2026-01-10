<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set("Europe/Istanbul");

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
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $pdo->exec("SET time_zone = '+03:00'");

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
