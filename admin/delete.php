<?php
require "auth.php";

$id = (int) $_GET["id"];
$pdo->prepare("DELETE FROM auctions WHERE id = ?")->execute([$id]);

header("Location: index.php");
