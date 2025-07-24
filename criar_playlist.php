<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $descricao = trim($_POST["descricao"]);
    $id_usuario = $_SESSION["usuario_id"];

    // Verifica campos
    if (empty($nome) || empty($descricao)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Upload de imagem de capa
        $nomeArquivo = null;
        if (!empty($_FILES["imagem"]["name"])) {
            $ext = pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid("playlist_") . "." . $ext;
            move_uploaded_file($_FILES["imagem"]["tmp_name"], "uploads/" . $nomeArquivo);
        }

        // Inserir playlist
        $stmt = $conn->prepare("INSERT INTO playlists (nome, descricao, imagem_capa, criado_por) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $nomeArquivo, $id_usuario]);
        $id_playlist = $conn->lastInsertId();

        header("Location: playlists.php");
        exit;
    }
}
?>

<?php include 'includes/header.php'; ?>
<div class="container py-5">
    <h2 class="mb-4">Criar Nova Playlist</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da Playlist</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="imagem" class="form-label">Imagem de Capa</label>
            <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Criar Playlist</button>
        <a href="playlists.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
