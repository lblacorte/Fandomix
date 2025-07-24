<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"]) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$id_usuario = (int) $_POST['id_usuario'];
$id_grupo = (int) $_POST['id_grupo'];
$acao = $_POST['acao'];

if (!in_array($acao, ['aceitar', 'recusar'])) {
    header("Location: solicitacoes.php");
    exit;
}

// Verifica se o usuário logado é moderador do grupo
$stmt = $conn->prepare("SELECT 1 FROM grupo_membros WHERE id_grupo = ? AND id_usuario = ? AND moderador = 1");
$stmt->execute([$id_grupo, $_SESSION['usuario_id']]);
if (!$stmt->fetch()) {
    header("Location: solicitacoes.php");
    exit;
}

if ($acao === 'aceitar') {
    $conn->prepare("INSERT INTO grupo_membros (id_grupo, id_usuario, moderador) VALUES (?, ?, 0)")
         ->execute([$id_grupo, $id_usuario]);
}

$conn->prepare("DELETE FROM grupo_solicitacoes WHERE id_grupo = ? AND id_usuario = ?")
     ->execute([$id_grupo, $id_usuario]);

header("Location: solicitacoes.php");
exit;
