<?php
session_start();
include 'config.php';

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;
$foto_perfil = $logado ? "uploads/" . ($_SESSION["foto"] ?? "default.png") : null;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT p.*, u.nome AS criador_nome
    FROM playlists p
    JOIN usuarios u ON u.id = p.criado_por
    WHERE p.id = ?
");
$stmt->execute([$id]);
$playlist = $stmt->fetch();

// Carregar interações do usuário com os conteúdos, se logado
$interacoes = [];
if ($logado) {
    $stmt = $conn->prepare("
        SELECT iu.id_conteudo, iu.lista_desejos, iu.assistido, iu.jogado, iu.favorito, a.nota
        FROM interacoes_usuario iu
        LEFT JOIN avaliacoes a ON a.id_conteudo = iu.id_conteudo AND a.id_usuario = iu.id_usuario
        WHERE iu.id_usuario = ?
    ");
    $stmt->execute([$usuario_id]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dados as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }
}

if (!$playlist) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Playlist não encontrada.</div></div>";
    exit;
}

// Buscar conteúdos da playlist
$stmt = $conn->prepare("
    SELECT c.*
    FROM playlist_conteudos pc
    JOIN conteudos c ON c.id = pc.id_conteudo
    WHERE pc.id_playlist = ?
");
$stmt->execute([$id]);
$conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <img src="uploads/<?= $playlist['imagem_capa'] ?? 'default.png' ?>" class="img-fluid rounded shadow" alt="Capa da Playlist">
        </div>
        <div class="col-md-8">
            <h2><?= htmlspecialchars($playlist['nome']) ?></h2>
            <p class="text-muted mb-1"><?= htmlspecialchars($playlist['descricao']) ?></p>
            <p class="text-muted small">Criada por <strong><?= htmlspecialchars($playlist['criador_nome']) ?></strong> em <?= date('d/m/Y', strtotime($playlist['data_criacao'])) ?></p>

            <?php if ($logado && $usuario_id == $playlist['criado_por']): ?>
                <a href="editar_playlist.php?id=<?= $playlist['id'] ?>" class="btn btn-sm btn-warning mt-2">Editar Playlist</a>
                <a href="adicionar_conteudo_playlist.php?id=<?= $playlist['id'] ?>" class="btn btn-sm btn-primary mt-2">Adicionar Conteúdos</a>
            <?php endif; ?>
        </div>
    </div>

    <hr>
    <h4>Conteúdos da Playlist</h4>

    <?php if (empty($conteudos)): ?>
        <div class="alert alert-info">Nenhum conteúdo adicionado ainda.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-4 g-4">
            <?php foreach ($conteudos as $item): ?>
                <?php include 'includes/card_conteudo.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
