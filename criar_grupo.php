<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $id_criador = $_SESSION['usuario_id'];

    if ($nome === '' || $descricao === '') {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("INSERT INTO grupos (nome, descricao, criado_por) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $descricao, $id_criador]);

        $id_grupo = $conn->lastInsertId();

        // Adicionar criador como membro moderador
        $stmt = $conn->prepare("INSERT INTO grupo_membros (id_grupo, id_usuario, moderador) VALUES (?, ?, 1)");
        $stmt->execute([$id_grupo, $id_criador]);

        header("Location: grupo.php?id=" . $id_grupo);
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Criar Novo Grupo</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Grupo</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Criar Grupo</button>
        <a href="grupos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
