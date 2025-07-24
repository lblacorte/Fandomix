<?php
session_start();
include 'config.php';

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;
$foto_perfil = $logado ? "uploads/" . ($_SESSION["foto"] ?? "default.png") : null;

$id_evento = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$stmt = $conn->prepare("SELECT e.*, u.nome AS criador_nome FROM eventos e JOIN usuarios u ON u.id = e.criado_por WHERE e.id = ?");
$stmt->execute([$id_evento]);
$evento = $stmt->fetch();

if (!$evento) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Evento não encontrado.</div></div>";
    exit;
}

// Verifica se o usuário já confirmou presença
$presente = false;
if ($logado) {
    $stmt = $conn->prepare("SELECT 1 FROM evento_participantes WHERE id_evento = ? AND id_usuario = ?");
    $stmt->execute([$id_evento, $usuario_id]);
    $presente = $stmt->fetchColumn() > 0;
}

// Lista de participantes
$stmt = $conn->prepare("SELECT u.nome, u.usuario, u.foto FROM evento_participantes ep JOIN usuarios u ON u.id = ep.id_usuario WHERE ep.id_evento = ?");
$stmt->execute([$id_evento]);
$participantes = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <a href="eventos.php" class="btn btn-outline-secondary mb-3">&larr; Voltar</a>

    <h2 class="mb-2"><?= htmlspecialchars($evento["nome"]) ?></h2>
    <p class="text-muted">Criado por <strong><?= htmlspecialchars($evento["criador_nome"]) ?></strong> em <?= date('d/m/Y', strtotime($evento["data_criacao"])) ?></p>
    <p><strong>Data do evento:</strong> <?= date('d/m/Y H:i', strtotime($evento["data_evento"])) ?></p>
    <p><?= nl2br(htmlspecialchars($evento["descricao"])) ?></p>

    <?php if ($logado && !$presente): ?>
        <form method="POST" action="confirmar_presenca_evento.php" class="mt-3">
            <input type="hidden" name="id_evento" value="<?= $id_evento ?>">
            <button class="btn btn-success">Confirmar Presença</button>
        </form>
    <?php elseif ($logado && $presente): ?>
        <div class="alert alert-success mt-3">Você já confirmou presença neste evento.</div>
    <?php else: ?>
        <div class="alert alert-info mt-3">Faça login para confirmar presença.</div>
    <?php endif; ?>

    <hr>

    <h5 class="mt-4">Participantes Confirmados</h5>
    <?php if (empty($participantes)): ?>
        <p class="text-muted">Nenhum participante ainda.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-4 g-3">
            <?php foreach ($participantes as $p): ?>
                <div class="col">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <img src="uploads/<?= $p['foto'] ?: 'default.png' ?>" class="rounded-circle mb-2" width="60" height="60">
                            <h6 class="mb-0"><?= htmlspecialchars($p['nome']) ?></h6>
                            <small class="text-muted">@<?= htmlspecialchars($p['usuario']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
