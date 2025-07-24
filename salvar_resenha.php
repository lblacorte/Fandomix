<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"]) || !isset($_POST["id_conteudo"], $_POST["resenha"])) {
    header("Location: dashboard.php");
    exit;
}

$id_usuario = $_SESSION["usuario_id"];
$id_conteudo = (int)$_POST["id_conteudo"];
$resenha = trim($_POST["resenha"]);

// Verifica se já existe
$stmt = $conn->prepare("SELECT * FROM avaliacoes WHERE id_usuario = ? AND id_conteudo = ?");
$stmt->execute([$id_usuario, $id_conteudo]);
$existe = $stmt->fetch();

if ($existe) {
    $stmt = $conn->prepare("UPDATE avaliacoes SET resenha = ?, data_avaliacao = NOW() WHERE id_usuario = ? AND id_conteudo = ?");
    $stmt->execute([$resenha, $id_usuario, $id_conteudo]);
} else {
    $stmt = $conn->prepare("INSERT INTO avaliacoes (id_usuario, id_conteudo, resenha, nota, data_avaliacao) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$id_usuario, $id_conteudo, $resenha, 0]);
}

header("Location: avaliacao.php?id=$id_conteudo");
exit;
