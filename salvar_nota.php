<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"], $_POST["id_conteudo"], $_POST["nota"])) {
    http_response_code(400);
    exit("Requisição inválida");
}

$id_usuario = $_SESSION["usuario_id"];
$id_conteudo = (int)$_POST["id_conteudo"];
$nota_nova = max(1, min(5, (int)$_POST["nota"])); // Garante de 1 a 5

// Verifica se já existe avaliação anterior
$stmt = $conn->prepare("SELECT nota FROM avaliacoes WHERE id_usuario = ? AND id_conteudo = ?");
$stmt->execute([$id_usuario, $id_conteudo]);
$avaliacao_existente = $stmt->fetchColumn();

// Busca dados atuais da média e número de avaliações
$stmt = $conn->prepare("SELECT avaliacao_media, numero_avaliacoes FROM conteudos WHERE id = ?");
$stmt->execute([$id_conteudo]);
$conteudo = $stmt->fetch(PDO::FETCH_ASSOC);

$media_atual = (float)$conteudo['avaliacao_media'];
$total_avaliacoes = (int)$conteudo['numero_avaliacoes'];

if ($avaliacao_existente !== false) {
    // Atualização de nota existente
    $nota_antiga = (int)$avaliacao_existente;
    $media_ajustada = $media_atual + ($nota_nova - $nota_antiga) / max($total_avaliacoes, 1);

    $stmt = $conn->prepare("UPDATE avaliacoes SET nota = ? WHERE id_usuario = ? AND id_conteudo = ?");
    $stmt->execute([$nota_nova, $id_usuario, $id_conteudo]);

} else {
    // Nova avaliação
    $total_avaliacoes += 1;
    $media_ajustada = (($media_atual * ($total_avaliacoes - 1)) + $nota_nova) / $total_avaliacoes;

    $stmt = $conn->prepare("INSERT INTO avaliacoes (id_usuario, id_conteudo, nota, resenha) VALUES (?, ?, ?, '')");
    $stmt->execute([$id_usuario, $id_conteudo, $nota_nova]);
}

// Atualiza os dados na tabela de conteúdos
$stmt = $conn->prepare("UPDATE conteudos SET avaliacao_media = ?, numero_avaliacoes = ? WHERE id = ?");
$stmt->execute([$media_ajustada, $total_avaliacoes, $id_conteudo]);

echo "ok";
