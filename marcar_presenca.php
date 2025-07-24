<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_POST['id_evento'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_evento = (int)$_POST['id_evento'];

// Verifica se já está marcado como participante
$stmt = $conn->prepare("SELECT 1 FROM evento_participantes WHERE id_evento = ? AND id_usuario = ?");
$stmt->execute([$id_evento, $id_usuario]);

if ($stmt->fetch()) {
    // Já está marcado, então remove a presença (desmarcar)
    $delete = $conn->prepare("DELETE FROM evento_participantes WHERE id_evento = ? AND id_usuario = ?");
    $delete->execute([$id_evento, $id_usuario]);
    echo "removido";
} else {
    // Marca como participante
    $insert = $conn->prepare("INSERT INTO evento_participantes (id_evento, id_usuario) VALUES (?, ?)");
    $insert->execute([$id_evento, $id_usuario]);
    echo "confirmado";
}
?>
