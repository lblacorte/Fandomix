<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;
$foto_perfil = $logado ? "uploads/" . ($_SESSION["foto"] ?? "default.png") : null;
$id_playlist = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifica se o usuário é o criador da playlist
$stmt = $conn->prepare("SELECT * FROM playlists WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_playlist, $usuario_id]);
$playlist = $stmt->fetch();

if (!$playlist) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Playlist não encontrada ou acesso negado.</div></div>";
    exit;
}

// Lógica de filtros
$termo = $_GET['termo'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$genero = $_GET['genero'] ?? '';

$where = [];
$params = [];

if (!empty($termo)) {
    $where[] = "(titulo LIKE ?)";
    $params[] = "%" . $termo . "%";
}
if (!empty($tipo)) {
    $where[] = "tipo = ?";
    $params[] = $tipo;
}
if (!empty($genero)) {
    $where[] = "genero = ?";
    $params[] = $genero;
}

// Adiciona o ID da playlist como primeiro parâmetro
array_unshift($params, $id_playlist);

// Consulta atualizada: apenas conteúdos que ainda não estão na playlist
$sql = "
    SELECT * FROM conteudos 
    WHERE id NOT IN (
        SELECT id_conteudo FROM playlist_conteudos WHERE id_playlist = ?
    )" 
    . ($where ? " AND " . implode(" AND ", $where) : "") . 
    " ORDER BY data_lancamento DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2 class="mb-4">Adicionar Conteúdos à Playlist: <?= htmlspecialchars($playlist["nome"]) ?></h2>

    <form method="GET" class="row g-3 mb-4">
        <input type="hidden" name="id" value="<?= $id_playlist ?>">
        <div class="col-md-4">
            <input type="text" name="termo" class="form-control" placeholder="Buscar por título..." value="<?= htmlspecialchars($termo) ?>">
        </div>
        <div class="col-md-3">
            <select name="tipo" class="form-select">
                <option value="">Tipo</option>
                <option value="filme" <?= $tipo === "filme" ? "selected" : "" ?>>Filme</option>
                <option value="série" <?= $tipo === "série" ? "selected" : "" ?>>Série</option>
                <option value="jogo" <?= $tipo === "jogo" ? "selected" : "" ?>>Jogo</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="genero" class="form-control" placeholder="Gênero" value="<?= htmlspecialchars($genero) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Filtrar</button>
        </div>
    </form>

    <form method="POST" action="salvar_conteudos_playlist.php">
        <input type="hidden" name="id_playlist" value="<?= $id_playlist ?>">

        <div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
            <?php foreach ($conteudos as $conteudo): ?>
                <div class="col">
                    <div class="card h-100 text-center shadow-sm">
                        <img src="assets/images/posters/<?= $conteudo['imagem'] ?>" class="card-img-top" alt="<?= $conteudo['titulo'] ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($conteudo['titulo']) ?></h6>
                            <div class="form-check mt-2 d-flex justify-content-center align-items-center gap-2">
    <input class="form-check-input larger-checkbox" type="checkbox" name="conteudos[]" value="<?= $conteudo['id'] ?>" id="conteudo-<?= $conteudo['id'] ?>">
    <label class="form-check-label fw-semibold" for="conteudo-<?= $conteudo['id'] ?>">Selecionar</label>
</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($conteudos)): ?>
            <div class="text-end">
                <button type="submit" class="btn btn-success">Adicionar Selecionados</button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Nenhum conteúdo encontrado com os filtros aplicados.</div>
        <?php endif; ?>
    </form>
</div>

<style>
.larger-checkbox {
    width: 22px;
    height: 22px;
    border: 2px solid #0d6efd;
    background-color: #fff;
    margin-top: 2px;
}
.larger-checkbox:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

<?php include 'includes/footer.php'; ?>