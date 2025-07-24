<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id_grupo = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
$mensagem = trim($_POST['mensagem']);

if ($id_grupo && $mensagem !== '') {
    $stmt = $conn->prepare("INSERT INTO grupo_mensagens (id_grupo, id_usuario, mensagem) VALUES (?, ?, ?)");
    $stmt->execute([$id_grupo, $usuario_id, $mensagem]);
}
