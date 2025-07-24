<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$foto_perfil = $logado ? "uploads/" . (isset($_SESSION["foto"]) ? $_SESSION["foto"] : "default.png") : null;
$usuario_id = $_SESSION['usuario_id'];
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data_evento = $_POST['data_evento'];
    $local = trim($_POST['local']);
    $id_criador = $_SESSION['usuario_id'];

    if ($nome === '' || $descricao === '' || $data_evento === '' || $local === '') {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("INSERT INTO eventos (nome, descricao, data_evento, local, criado_por) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $data_evento, $local, $id_criador]);

        header("Location: eventos.php");
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Criar Novo Evento</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Evento</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="data_evento" class="form-label">Data do Evento</label>
            <input type="date" name="data_evento" id="data_evento" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="local" class="form-label">Local</label>
            <input type="text" name="local" id="local" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Criar Evento</button>
        <a href="eventos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
