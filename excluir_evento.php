<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: eventos.php");
    exit;
}

$id_evento = (int)$_GET['id'];
$id_usuario = $_SESSION['usuario_id'];

// Verificar se o evento é do usuário
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_evento, $id_usuario]);
$evento = $stmt->fetch();

if (!$evento) {
    header("Location: eventos.php");
    exit;
}

// Excluir participantes primeiro (se houver), depois o evento
$conn->prepare("DELETE FROM evento_participantes WHERE id_evento = ?")->execute([$id_evento]);
$conn->prepare("DELETE FROM eventos WHERE id = ?")->execute([$id_evento]);

header("Location: eventos.php");
exit;
?>
