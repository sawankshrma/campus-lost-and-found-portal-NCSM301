<?php
$pdo = new PDO(
    "mysql:host=127.0.0.1;dbname=lost_and_found;charset=utf8mb4",
    "lnf_user",
    "lnf_pass123",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
?>
