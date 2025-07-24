<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"]) || !isset($_POST["id_evento"])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION["usuario_id"];
$id_evento = (int) $_POST["id_evento"];

// Verifica se já está marcado
$stmt = $conn->prepare("SELECT 1 FROM evento_participantes WHERE id_evento = ? AND id_usuario = ?");
$stmt->execute([$id_evento, $id_usuario]);
if (!$stmt->fetch()) {
    $stmt = $conn->prepare("INSERT INTO evento_participantes (id_evento, id_usuario) VALUES (?, ?)");
    $stmt->execute([$id_evento, $id_usuario]);
}

header("Location: evento.php?id=$id_evento");
exit;
