<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(401); // Não autorizado
    exit("Usuário não logado.");
}

$id_usuario = $_SESSION["usuario_id"];
$id_conteudo = isset($_POST["id_conteudo"]) ? $_POST["id_conteudo"] : null;

if (!$id_conteudo || !is_numeric($id_conteudo)) {
    http_response_code(400); // Requisição inválida
    exit("ID de conteúdo inválido.");
}

// Verifica se já existe uma interação
$stmt = $conn->prepare("SELECT favorito FROM interacoes_usuario WHERE id_usuario = ? AND id_conteudo = ?");
$stmt->execute([$id_usuario, $id_conteudo]);
$existe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existe) {
    // Alterna o valor atual (1 -> 0, 0 -> 1)
    $novo_estado = $existe['favorito'] ? 0 : 1;
    $update = $conn->prepare("UPDATE interacoes_usuario SET favorito = ? WHERE id_usuario = ? AND id_conteudo = ?");
    $update->execute([$novo_estado, $id_usuario, $id_conteudo]);
} else {
    // Cria nova linha com favorito = 1
    $insert = $conn->prepare("INSERT INTO interacoes_usuario (id_usuario, id_conteudo, favorito) VALUES (?, ?, 1)");
    $insert->execute([$id_usuario, $id_conteudo]);
}

// Retorna sucesso
echo "OK";
