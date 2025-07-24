<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: eventos.php");
    exit;
}

$id_evento = (int)$_GET['id'];
$logado = isset($_SESSION["usuario_id"]);
$foto_perfil = $logado ? "uploads/" . (isset($_SESSION["foto"]) ? $_SESSION["foto"] : "default.png") : null;
$id_usuario = $_SESSION['usuario_id'];
$erro = "";

// Verificar se o usuário é o criador do evento
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_evento, $id_usuario]);
$evento = $stmt->fetch();

if (!$evento) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Você não tem permissão para editar este evento.</div></div>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data_evento = $_POST['data_evento'];
    $local = trim($_POST['local']);

    if ($nome === '' || $descricao === '' || $data_evento === '' || $local === '') {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("UPDATE eventos SET nome = ?, descricao = ?, data_evento = ?, local = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $data_evento, $local, $id_evento]);

        header("Location: eventos.php");
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Editar Evento</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Evento</label>
            <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars($evento['nome']) ?>">
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="4" required><?= htmlspecialchars($evento['descricao']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="data_evento" class="form-label">Data do Evento</label>
            <input type="date" name="data_evento" id="data_evento" class="form-control" required value="<?= $evento['data_evento'] ?>">
        </div>
        <div class="mb-3">
            <label for="local" class="form-label">Local</label>
            <input type="text" name="local" id="local" class="form-control" required value="<?= htmlspecialchars($evento['local']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="eventos.php" class="btn btn-secondary">Cancelar</a>
        <button type="button" class="btn btn-danger float-end" onclick="confirmarExclusao()">Excluir Evento</button>
    </form>
</div>

<script>
function confirmarExclusao() {
    if (confirm("Tem certeza que deseja excluir este evento? Esta ação não poderá ser desfeita.")) {
        window.location.href = "excluir_evento.php?id=<?= $id_evento ?>";
    }
}
</script>

<?php include 'includes/footer.php'; ?>