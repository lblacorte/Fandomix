<?php
session_start();
include 'config.php';

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;

// Suas playlists
$stmt = $conn->prepare("SELECT * FROM playlists WHERE criado_por = ? ORDER BY data_criacao DESC");
$stmt->execute([$usuario_id]);
$suas_playlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtro da comunidade
$termo = isset($_GET['termo']) ? trim($_GET['termo']) : '';
$params = [];

$sql = "
    SELECT p.*, u.nome AS criador_nome
    FROM playlists p
    JOIN usuarios u ON u.id = p.criado_por
";

// Aplica filtro se houver
if (!empty($termo)) {
    $sql .= " WHERE p.nome LIKE ? OR p.descricao LIKE ?";
    $params[] = '%' . $termo . '%';
    $params[] = '%' . $termo . '%';
}

$sql .= " ORDER BY p.data_criacao DESC";

$stmt2 = $conn->prepare($sql);
$stmt2->execute($params);
$comunidade = $stmt2->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Playlists</h2>
        <?php if ($logado): ?>
            <a href="criar_playlist.php" class="btn btn-primary">Criar Playlist</a>
        <?php endif; ?>
    </div>

    <?php if ($logado): ?>
        <h4>Suas Playlists</h4>
        <?php if (empty($suas_playlists)): ?>
            <div class="alert alert-info">Você ainda não criou nenhuma playlist.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
                <?php foreach ($suas_playlists as $pl): ?>
                    <div class="col">
                        <a href="playlist.php?id=<?= $pl['id'] ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm">
                                <img src="uploads/<?= htmlspecialchars($pl['imagem_capa'] ?: 'default.jpg') ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($pl['nome']) ?></h5>
                                    <p class="card-text text-muted small">Criada em <?= date('d/m/Y', strtotime($pl['data_criacao'])) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <h4>Playlists da Comunidade</h4>

    <form id="filtro-playlists" class="row g-3 mb-4">
        <div class="col-md-10">
            <input type="text" name="termo" class="form-control" placeholder="Buscar por título ou descrição..." value="<?= htmlspecialchars($termo) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
        </div>
    </form>

    <div id="resultados-playlists" class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($comunidade as $pl): ?>
            <div class="col">
                <a href="playlist.php?id=<?= $pl['id'] ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm">
                        <img src="uploads/<?= htmlspecialchars($pl['imagem_capa'] ?: 'default.jpg') ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($pl['nome']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($pl['descricao'])) ?></p>
                            <p class="card-text text-muted small">Criada por <strong><?= htmlspecialchars($pl['criador_nome']) ?></strong> em <?= date('d/m/Y', strtotime($pl['data_criacao'])) ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
