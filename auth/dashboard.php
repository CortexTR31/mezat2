<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
}
?>

<h1>Hoşgeldin 🎉</h1>
<a href="auth/logout.php">Çıkış Yap</a>