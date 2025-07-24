<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"], $_POST["id_playlist"], $_POST["conteudos"])) {
    header("Location: playlists.php");
    exit;
}

$id_playlist = (int)$_POST["id_playlist"];
$id_usuario = $_SESSION["usuario_id"];
$conteudos = $_POST["conteudos"];

// Verifica se é o criador
$stmt = $conn->prepare("SELECT id FROM playlists WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_playlist, $id_usuario]);
if (!$stmt->fetch()) {
    header("Location: playlists.php");
    exit;
}

// Adiciona os conteúdos
foreach ($conteudos as $id_conteudo) {
    $stmt = $conn->prepare("INSERT IGNORE INTO playlist_conteudos (id_playlist, id_conteudo) VALUES (?, ?)");
    $stmt->execute([$id_playlist, (int)$id_conteudo]);
}

header("Location: playlist.php?id=" . $id_playlist);
exit;
