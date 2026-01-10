<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../auth/login.php");
    exit;
}

$q = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$q->execute([$_SESSION["user"]]);
$user = $q->fetch();

if ($user["role"] !== "admin") {
    die("Yetkisiz erişim");
}
