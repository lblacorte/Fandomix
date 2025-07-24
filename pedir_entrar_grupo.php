<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$id_grupo = (int) $_GET['id'];
$id_usuario = $_SESSION['usuario_id'];

// Verifica se o usuário já é membro
$stmt = $conn->prepare("SELECT 1 FROM grupo_membros WHERE id_grupo = ? AND id_usuario = ?");
$stmt->execute([$id_grupo, $id_usuario]);
if ($stmt->fetch()) {
    header("Location: grupos.php");
    exit;
}

// Verifica se já existe solicitação
$stmt = $conn->prepare("SELECT 1 FROM grupo_solicitacoes WHERE id_grupo = ? AND id_usuario = ?");
$stmt->execute([$id_grupo, $id_usuario]);
if (!$stmt->fetch()) {
    $stmt = $conn->prepare("INSERT INTO grupo_solicitacoes (id_grupo, id_usuario) VALUES (?, ?)");
    $stmt->execute([$id_grupo, $id_usuario]);
}

header("Location: grupos.php?solicitado=$id_grupo");
exit;
?>
