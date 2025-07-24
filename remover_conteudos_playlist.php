<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'], $_POST['id_playlist']) || !isset($_POST['remover'])) {
    header("Location: playlists.php");
    exit;
}

$id_playlist = (int) $_POST['id_playlist'];
$usuario_id = $_SESSION['usuario_id'];

// Verifica se é o dono da playlist
$stmt = $conn->prepare("SELECT 1 FROM playlists WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_playlist, $usuario_id]);
if (!$stmt->fetch()) {
    exit("Acesso negado.");
}

$ids = $_POST['remover'];
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$params = $ids;
array_unshift($params, $id_playlist);

$stmt = $conn->prepare("DELETE FROM playlist_conteudos WHERE id_playlist = ? AND id_conteudo IN ($placeholders)");
$stmt->execute($params);

header("Location: editar_playlist.php?id=$id_playlist");
exit;
