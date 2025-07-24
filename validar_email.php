<?php
include 'config.php';

$email = isset($_GET["email"]) ? $_GET["email"] : '';
$id = isset($_GET["id"]) ? $_GET["id"] : 0;

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
$stmt->execute([$email, $id]);
echo $stmt->rowCount() > 0 ? "existe" : "ok";
