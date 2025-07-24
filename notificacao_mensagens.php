<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["novas" => 0]);
    exit;
}

$id = $_SESSION["usuario_id"];

// Última mensagem recebida
$stmt = $conn->prepare("
    SELECT u.id AS usuario_id, u.nome, u.usuario, u.foto, COUNT(*) as novas
    FROM mensagens m
    JOIN usuarios u ON u.id = m.id_remetente
    WHERE m.id_destinatario = ? AND (m.lida IS NULL OR m.lida = 0)
    GROUP BY u.id
    ORDER BY MAX(m.data_envio) DESC
    LIMIT 1
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode([
        "novas" => $data['novas'],
        "foto" => isset($data['foto']) ? $data['foto'] : 'default.png',
        "usuario" => $data['usuario'],
        "usuario_id" => $data['usuario_id']
    ]);
} else {
    echo json_encode(["novas" => 0]);
}
