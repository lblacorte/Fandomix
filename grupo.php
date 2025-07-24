// grupo.php
<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"]) || !isset($_GET["id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$id_usuario = $_SESSION['usuario_id'];
$foto_perfil = isset($_SESSION['foto']) ? "uploads/" . $_SESSION['foto'] : "uploads/default.png";
$usuario_id = $_SESSION["usuario_id"];
$id_grupo = (int)$_GET["id"];

// Verifica se o grupo existe e se o usuário é membro
$stmt = $conn->prepare("SELECT g.*, gm.moderador FROM grupos g JOIN grupo_membros gm ON g.id = gm.id_grupo WHERE g.id = ? AND gm.id_usuario = ?");
$stmt->execute([$id_grupo, $usuario_id]);
$grupo = $stmt->fetch();

if (!$grupo) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Grupo não encontrado ou acesso negado.</div></div>";
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-2">
    <div class="mb-3">
        <a href="grupos.php" class="btn btn-outline-secondary">&larr; Voltar para grupos</a>
    </div>

    <h2 class="mb-4"><?= htmlspecialchars($grupo['nome']) ?></h2>

    <div id="chat-box" class="border rounded p-3 bg-light mb-4" style="height: 400px; overflow-y: auto;"></div>

    <form id="form-mensagem">
        <input type="hidden" name="id_grupo" value="<?= $id_grupo ?>">
        <div class="input-group">
            <input type="text" name="mensagem" class="form-control" placeholder="Digite sua mensagem..." required>
            <button class="btn btn-primary" type="submit">Enviar</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function carregarMensagens() {
    fetch("carregar_mensagens_grupo.php?id_grupo=<?= $id_grupo ?>")
        .then(res => res.text())
        .then(html => {
            const box = document.getElementById("chat-box");
            box.innerHTML = html;
            box.scrollTop = box.scrollHeight;
        });
}

carregarMensagens();
setInterval(carregarMensagens, 3000);

document.getElementById("form-mensagem").addEventListener("submit", function(e) {
    e.preventDefault();
    const form = new FormData(this);
    fetch("enviar_mensagem_grupo.php", {
        method: "POST",
        body: form
    }).then(() => {
        this.reset();
        carregarMensagens();
    });
});
</script>
