<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) exit;

$id_remetente = $_SESSION["usuario_id"];
$id_destinatario = (int)$_POST["destinatario"];
$mensagem = trim($_POST["mensagem"]);

if ($id_remetente === $id_destinatario || $mensagem === '') exit;

$stmt = $conn->prepare("INSERT INTO mensagens (id_remetente, id_destinatario, mensagem) VALUES (?, ?, ?)");
$stmt->execute([$id_remetente, $id_destinatario, $mensagem]);
