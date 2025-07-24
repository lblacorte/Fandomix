<?php
session_start();
include 'config.php';

$id_perfil = isset($_GET['id']) ? (int)$_GET['id'] : ($_SESSION["usuario_id"] ?? 0);

if (!$id_perfil) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$nome_usuario = $logado ? $_SESSION["nome"] : null;
$foto_perfil = $logado ? "uploads/" . ($_SESSION["foto"] ?? "default.png") : "uploads/default.png";
$usuario_logado = $logado ? $_SESSION["usuario_id"] : null;
$eh_proprio_perfil = $usuario_logado == $id_perfil;

// Dados do usuário
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_perfil]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Usuário não encontrado.</div></div>";
    exit;
}

$foto_de_perfil = "uploads/" . ($usuario["foto"] ?? "default.png");

// Contadores
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN assistido = 1 OR jogado = 1 THEN 1 ELSE 0 END) AS interacoes,
        SUM(CASE WHEN favorito = 1 THEN 1 ELSE 0 END) AS favoritos
    FROM interacoes_usuario
    WHERE id_usuario = ?
");
$stmt->execute([$id_perfil]);
$interacoes = $stmt->fetch();

$stmt = $conn->prepare("SELECT COUNT(*) FROM avaliacoes WHERE id_usuario = ?");
$stmt->execute([$id_perfil]);
$total_avaliacoes = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM avaliacoes a
    LEFT JOIN interacoes_usuario i ON a.id_usuario = i.id_usuario AND a.id_conteudo = i.id_conteudo
    WHERE a.id_usuario = ?");
$stmt->execute([$id_perfil]);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-3 text-center">
            <img src="<?= $foto_de_perfil ?>" class="rounded-circle shadow" style="width: 150px; height: 150px; object-fit: cover;">
        </div>
        <div class="col-md-6">
            <h3><?= htmlspecialchars($usuario['nome']) ?> <small class="text-muted">@<?= htmlspecialchars($usuario['usuario']) ?></small></h3>
            <p class="text-muted">🎂 <?= date('d/m/Y', strtotime($usuario['data_nasc'])) ?></p>
        </div>
        <div class="col-md-3 text-end">
            <?php if ($eh_proprio_perfil): ?>
                <a href="configuracoes.php" class="btn btn-outline-primary">Editar Perfil</a>
            <?php endif; ?>
        </div>
    </div>

    <hr>

    <div class="row text-center mb-4">
        <div class="col-md-3">
            <h5><?= $interacoes['interacoes'] ?></h5>
            <p class="text-muted small">Conteúdos assistidos/jogados</p>
        </div>
        <div class="col-md-3">
            <h5><?= $interacoes['favoritos'] ?></h5>
            <p class="text-muted small">Favoritos</p>
        </div>
        <div class="col-md-3">
            <h5><?= $total_avaliacoes ?></h5>
            <p class="text-muted small">Avaliações</p>
        </div>
        <div class="col-md-3">
            <h5><?= $usuario['conquistas'] ?></h5>
            <p class="text-muted small">Conquistas</p>
        </div>
    </div>

    <hr>
</div>

<?php include 'includes/footer.php'; ?>
