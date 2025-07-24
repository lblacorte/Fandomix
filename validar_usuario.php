<?php
include 'config.php';

$usuario = isset($_GET["usuario"]) ? $_GET["usuario"] : '';
$id = isset($_GET["id"]) ? $_GET["id"] : 0;

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
$stmt->execute([$usuario, $id]);
echo $stmt->rowCount() > 0 ? "existe" : "ok";
