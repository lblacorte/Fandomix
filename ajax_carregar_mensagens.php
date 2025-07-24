<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) exit;

$id_remetente = $_SESSION["usuario_id"];
$id_destinatario = (int)$_GET["para"];

$stmt = $conn->prepare("
    SELECT * FROM mensagens 
    WHERE (id_remetente = ? AND id_destinatario = ?) 
       OR (id_remetente = ? AND id_destinatario = ?) 
    ORDER BY data_envio
");
$stmt->execute([$id_remetente, $id_destinatario, $id_destinatario, $id_remetente]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($mensagens as $msg) {
    $classe = $msg["id_remetente"] == $id_remetente ? "text-end" : "text-start";
    $bg = $msg["id_remetente"] == $id_remetente ? "bg-primary text-white" : "bg-light";
    echo "<div class='$classe mb-2'><span class='d-inline-block p-2 rounded $bg'>" . 
         nl2br(htmlspecialchars($msg["mensagem"])) . "</span><br><small class='text-muted'>" . 
         date("d/m H:i", strtotime($msg["data_envio"])) . "</small></div>";
}

// Opcional: Marcar como lida
$conn->prepare("UPDATE mensagens SET lida = 1 WHERE id_remetente = ? AND id_destinatario = ?")
     ->execute([$id_destinatario, $id_remetente]);
