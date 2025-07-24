<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: playlists.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id_playlist = (int) $_GET['id'];

// Verifica se o usuário é o criador da playlist
$stmt = $conn->prepare("SELECT * FROM playlists WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_playlist, $usuario_id]);
$playlist = $stmt->fetch();

if (!$playlist) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Playlist não encontrada ou acesso negado.</div></div>";
    exit;
}

// Exclui os conteúdos associados
$stmt = $conn->prepare("DELETE FROM playlist_conteudos WHERE id_playlist = ?");
$stmt->execute([$id_playlist]);

// Exclui a playlist
$stmt = $conn->prepare("DELETE FROM playlists WHERE id = ?");
$stmt->execute([$id_playlist]);

// Remove imagem de capa se existir
if (!empty($playlist['imagem_capa']) && file_exists("uploads/" . $playlist['imagem_capa'])) {
    unlink("uploads/" . $playlist['imagem_capa']);
}

header("Location: playlists.php");
exit;
