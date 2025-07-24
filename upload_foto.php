<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto = $_FILES['foto'];

    $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . '.' . $ext;
    $destino = "uploads/" . $nome_arquivo;

    if (move_uploaded_file($foto['tmp_name'], $destino)) {
        // Atualiza o campo foto no banco de dados
        $sql = "UPDATE usuarios SET foto = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome_arquivo, $_SESSION['usuario_id']]);

        // Atualiza também a sessão
        $_SESSION['foto'] = $nome_arquivo;

        header("Location: login.php");
        exit;
    } else {
        echo "Erro ao mover o arquivo.";
    }
} else {
    echo "Arquivo inválido.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['usuario'] = $usuario['usuario']; // <-- adiciona o @usuario
        $_SESSION['foto'] = isset($usuario['foto']) ? $usuario['foto'] : null; // opcional, se usar foto de perfil
        header("Location: login.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos!";
    }
}
?>