<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    http_response_code(403);
    exit("Acesso negado");
}

$id = $_SESSION["usuario_id"];
$nome = trim($_POST["nome"]);
$usuario = trim($_POST["usuario"]);
$email = trim($_POST["email"]);
$data_nasc = $_POST["data_nasc"];
$tags = trim($_POST["tags"]);
$generos = isset($_POST["generos"]) ? implode(",", $_POST["generos"]) : "";

$atualizarUsuario = $conn->prepare("UPDATE usuarios SET nome = ?, usuario = ?, email = ?, data_nasc = ? WHERE id = ?");
$atualizarUsuario->execute([$nome, $usuario, $email, $data_nasc, $id]);

// Atualiza ou insere em 'preferencias'
$verificaPref = $conn->prepare("SELECT id FROM preferencias WHERE id_usuario = ?");
$verificaPref->execute([$id]);

if ($verificaPref->fetch()) {
    $stmt = $conn->prepare("UPDATE preferencias SET generos = ?, tags = ? WHERE id_usuario = ?");
    $stmt->execute([$generos, $tags, $id]);
} else {
    $stmt = $conn->prepare("INSERT INTO preferencias (id_usuario, generos, tags) VALUES (?, ?, ?)");
    $stmt->execute([$id, $generos, $tags]);
}

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . "." . $ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $nome_arquivo);

    $stmt = $conn->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
    $stmt->execute([$nome_arquivo, $id]);
    $_SESSION['foto'] = $nome_arquivo;
}

$_SESSION["nome"] = $nome;
$_SESSION["usuario"] = $usuario;
$_SESSION["email"] = $email;
$_SESSION["data_nasc"] = $data_nasc;

?>
<?php include 'includes/header.php'; ?>
<div class="container py-5 text-center">
    <h2 class="mb-4 text-success">✅ Alterações salvas com sucesso!</h2>
    <a href="configuracoes.php" class="btn btn-primary">Voltar para Configurações</a>
</div>
<?php include 'includes/footer.php'; ?>
<?php

