<?php
session_start();
include 'config.php';

if (!isset($_POST['id'])) {
    http_response_code(400);
    exit('ID não enviado');
}

$id_conteudo = (int)$_POST['id'];
$logado = isset($_SESSION['usuario_id']);
$usuario_id = $logado ? $_SESSION['usuario_id'] : null;

// Buscar o conteúdo
$stmt = $conn->prepare("SELECT * FROM conteudos WHERE id = ?");
$stmt->execute([$id_conteudo]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    http_response_code(404);
    exit("Conteúdo não encontrado");
}

// Buscar interações
$interacoes = [];
if ($logado) {
    $stmt = $conn->prepare("SELECT iu.lista_desejos, iu.assistido, iu.jogado, iu.favorito, a.nota
        FROM interacoes_usuario iu
        LEFT JOIN avaliacoes a ON a.id_conteudo = iu.id_conteudo AND a.id_usuario = iu.id_usuario
        WHERE iu.id_usuario = ? AND iu.id_conteudo = ?");
    $stmt->execute([$usuario_id, $id_conteudo]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $interacoes[$id_conteudo] = $row;
    }
}

$logado = $usuario_id !== null;
include 'includes/card_conteudo.php';
