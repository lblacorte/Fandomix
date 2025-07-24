<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'], $_POST['id_convite'], $_POST['acao'])) {
    http_response_code(400);
    echo "Parâmetros inválidos.";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id_convite = (int)$_POST['id_convite'];
$acao = $_POST['acao'];

if (!in_array($acao, ['aceitar', 'rejeitar'])) {
    http_response_code(400);
    echo "Ação inválida.";
    exit;
}

// Buscar o convite
$stmt = $conn->prepare("SELECT * FROM convites WHERE id = ? AND id_para = ?");
$stmt->execute([$id_convite, $usuario_id]);
$convite = $stmt->fetch();

if (!$convite) {
    http_response_code(404);
    echo "Convite não encontrado.";
    exit;
}

if ($acao === 'aceitar') {
    if ($convite['tipo'] === 'grupo') {
        $stmt = $conn->prepare("INSERT IGNORE INTO grupo_membros (id_grupo, id_usuario, moderador) VALUES (?, ?, 0)");
        $stmt->execute([$convite['id_destino'], $usuario_id]);
    } elseif ($convite['tipo'] === 'evento') {
        $stmt = $conn->prepare("INSERT IGNORE INTO evento_participantes (id_evento, id_usuario) VALUES (?, ?)");
        $stmt->execute([$convite['id_destino'], $usuario_id]);
    }

    $stmt = $conn->prepare("UPDATE convites SET status = 'aceito' WHERE id = ?");
    $stmt->execute([$id_convite]);

    echo "aceito";
} else {
    $stmt = $conn->prepare("UPDATE convites SET status = 'rejeitado' WHERE id = ?");
    $stmt->execute([$id_convite]);

    echo "rejeitado";
}
