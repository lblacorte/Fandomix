<?php
session_start();
include '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;
$foto_perfil = $logado ? "../uploads/" . ($_SESSION["foto"] ?? "../default.png") : null;

// Buscar informações do conteúdo
$stmt = $conn->prepare("SELECT * FROM conteudos WHERE id = ?");
$stmt->execute([$id]);
$conteudo = $stmt->fetch();

if (!$conteudo) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Conteúdo não encontrado.</div></div>";
    exit;
}

// Buscar média de avaliações e número de avaliações
$stmt = $conn->prepare("SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE id_conteudo = ?");
$stmt->execute([$id]);
$avaliacoes = $stmt->fetch();

// Buscar resenhas
$stmt = $conn->prepare("
    SELECT a.resenha, a.nota, u.nome, u.nome, u.foto
    FROM avaliacoes a
    JOIN usuarios u ON u.id = a.id_usuario
    WHERE a.id_conteudo = ? AND a.resenha IS NOT NULL AND a.resenha != ''
    ORDER BY a.data_avaliacao DESC
    LIMIT 10
");
$stmt->execute([$id]);
$resenhas = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <img src="../assets/images/posters/<?= htmlspecialchars($conteudo['imagem']) ?>" class="img-fluid rounded shadow" alt="<?= htmlspecialchars($conteudo['titulo']) ?>">
        </div>
        <div class="col-md-8">
            <h2><?= htmlspecialchars($conteudo['titulo']) ?></h2>
            <p class="text-muted">Tipo: <strong><?= ucfirst($conteudo['tipo']) ?></strong> | Gênero: <?= htmlspecialchars($conteudo['genero']) ?> | Lançamento: <?= htmlspecialchars($conteudo['data_lancamento']) ?></p>

            <div class="mt-3">
                <h5>⭐ Avaliação da comunidade</h5>
                <p class="fs-5">
                    <?= number_format($avaliacoes['media'] ?? 0, 1) ?> / 5 (<?= $avaliacoes['total'] ?> avaliações)
                </p>
                <?php if ($logado): ?>
                    <a href="../avaliacao.php?id=<?= $conteudo['id'] ?>" class="btn btn-outline-primary">Avaliar este conteúdo</a>
                <?php else: ?>
                    <a href="../login.php" class="btn btn-outline-secondary">Entre para avaliar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <hr>

    <h4 class="mt-4">Resenhas da comunidade</h4>

    <?php if (empty($resenhas)): ?>
        <p class="text-muted">Nenhuma resenha escrita ainda.</p>
    <?php else: ?>
        <?php foreach ($resenhas as $r): ?>
            <div class="border p-3 rounded mb-3">
                <div class="d-flex align-items-center mb-2">
                    <img src="../uploads/<?= $r['foto'] ?: 'default.png' ?>" class="rounded-circle me-2" width="40" height="40">
                    <div>
                        <strong><?= htmlspecialchars($r['nome']) ?></strong> <span class="text-muted">@<?= htmlspecialchars($r['nome']) ?></span>
                        <br>
                        <span class="text-warning">Nota: <?= $r['nota'] ?>/5</span>
                    </div>
                </div>
                <p class="mb-0"><?= nl2br(htmlspecialchars($r['resenha'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
