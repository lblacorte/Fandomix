<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$logado = true;
$usuario_id = $_SESSION["usuario_id"];
$id_playlist = (int) $_GET['id'];

// Verifica se o usuário é o criador da playlist
$stmt = $conn->prepare("SELECT * FROM playlists WHERE id = ? AND criado_por = ?");
$stmt->execute([$id_playlist, $usuario_id]);
$playlist = $stmt->fetch();

if (!$playlist) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Playlist não encontrada ou acesso não autorizado.</div></div>";
    exit;
}

$erro = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    if ($nome === '' || $descricao === '') {
        $erro = "Preencha todos os campos.";
    } else {
        // Atualiza capa se fornecida
        $nova_capa = $playlist['imagem_capa'];
        if (!empty($_FILES['capa']['name'])) {
            $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
            $nome_arquivo = uniqid("playlist_") . '.' . $ext;
            move_uploaded_file($_FILES['capa']['tmp_name'], "uploads/" . $nome_arquivo);
            $nova_capa = $nome_arquivo;
        }

        $stmt = $conn->prepare("UPDATE playlists SET nome = ?, descricao = ?, imagem_capa = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $nova_capa, $id_playlist]);

        header("Location: playlist.php?id=$id_playlist");
        exit;
    }
}

// Carregar conteúdos da playlist
$stmt = $conn->prepare("
    SELECT c.id, c.titulo, c.imagem
    FROM playlist_conteudos pc
    JOIN conteudos c ON c.id = pc.id_conteudo
    WHERE pc.id_playlist = ?
    ORDER BY c.data_lancamento DESC
");
$stmt->execute([$id_playlist]);
$conteudos_playlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-2">
    <h2 class="mb-4">Editar Playlist</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da Playlist</label>
            <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars($playlist['nome']) ?>">
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="4" required><?= htmlspecialchars($playlist['descricao']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="capa" class="form-label">Imagem de Capa (opcional)</label><br>
            <?php if (!empty($playlist['imagem_capa']) && file_exists(__DIR__ . "/uploads/" . $playlist['imagem_capa'])): ?>
                <img src="uploads/<?= htmlspecialchars($playlist['imagem_capa']) ?>" alt="Capa atual" class="mb-2 rounded" style="max-height: 150px;">
            <?php else: ?>
                <p class="text-muted fst-italic">Sem capa definida.</p>
            <?php endif; ?>
            <input type="file" name="capa" id="capa" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        <a href="playlist.php?id=<?= $playlist['id'] ?>" class="btn btn-secondary">Cancelar</a>

        <a href="excluir_playlist.php?id=<?= $playlist['id'] ?>" class="btn btn-danger float-end"
           onclick="return confirm('Tem certeza que deseja excluir esta playlist? Esta ação não pode ser desfeita.')">
            Excluir Playlist
        </a>
    </form>

    <?php if ($conteudos_playlist): ?>
        <h4>Conteúdos na Playlist</h4>
        <form method="POST" action="remover_conteudos_playlist.php" onsubmit="return confirm('Deseja realmente remover os conteúdos selecionados?');">
            <input type="hidden" name="id_playlist" value="<?= $id_playlist ?>">
            <div class="row row-cols-1 row-cols-md-4 g-4 mb-3">
                <?php foreach ($conteudos_playlist as $c): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="assets/images/posters/<?= htmlspecialchars($c['imagem']) ?>" class="card-img-top" style="object-fit: cover; height: 180px;">
                            <div class="card-body text-center">
                                <h6 class="card-title"><?= htmlspecialchars($c['titulo']) ?></h6>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remover[]" value="<?= $c['id'] ?>" id="chk<?= $c['id'] ?>" style="transform: scale(1.5);">
                                    <label class="form-check-label" for="chk<?= $c['id'] ?>">Remover</label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-danger">Remover Conteúdos Selecionados</button>
        </form>
    <?php else: ?>
        <p class="text-muted fst-italic">Esta playlist ainda não possui conteúdos.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
