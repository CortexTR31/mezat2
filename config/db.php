<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO(
        "mysql:host=ballast.proxy.rlwy.net;port=20344;dbname=railway;charset=utf8mb4",
        "root",
        "TVyuCkNwmZBcGHLWXOUnDhRzdnBeOddI",
        [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
    );
    echo "DB OK";
} catch (PDOException $e) {
    echo $e->getMessage();
}
