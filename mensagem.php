<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$id_remetente = $_SESSION["usuario_id"];
$id_destinatario = isset($_GET['para']) ? (int)$_GET['para'] : 0;
// Marcar como lidas as mensagens recebidas do outro usuário
$update = $conn->prepare("UPDATE mensagens SET lida = 1 WHERE id_remetente = ? AND id_destinatario = ? AND lida = 0");
$update->execute([$id_destinatario, $_SESSION["usuario_id"]]);

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;
$foto_perfil = $logado ? "../uploads/" . ($_SESSION["foto"] ?? "../default.png") : null;

if ($id_destinatario === $id_remetente || $id_destinatario <= 0) {
    echo "Usuário inválido.";
    exit;
}

// Obter nome do destinatário
$stmt = $conn->prepare("SELECT nome, usuario FROM usuarios WHERE id = ?");
$stmt->execute([$id_destinatario]);
$dest = $stmt->fetch();

if (!$dest) {
    echo "Usuário não encontrado.";
    exit;
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <a href="historico_mensagens.php" class="btn btn-secondary mb-3">← Histórico</a>
    <h4>Conversando com @<?= htmlspecialchars($dest['usuario']) ?></h4>

    <div id="mensagens" class="border p-3 mb-3" style="height: 400px; overflow-y: auto; background: #f9f9f9;">
        <!-- As mensagens vão ser carregadas via AJAX -->
    </div>

    <form id="form-mensagem" class="d-flex">
        <input type="hidden" name="destinatario" value="<?= $id_destinatario ?>">
        <input type="text" name="mensagem" class="form-control me-2" placeholder="Digite sua mensagem..." required>
        <button class="btn btn-primary">Enviar</button>
    </form>
</div>

<script>
function carregarMensagens() {
    fetch("ajax_carregar_mensagens.php?para=<?= $id_destinatario ?>")
        .then(res => res.text())
        .then(html => {
            const div = document.getElementById("mensagens");
            const estavaNoFim = div.scrollTop + div.clientHeight >= div.scrollHeight - 50;
            div.innerHTML = html;
            if (estavaNoFim) div.scrollTop = div.scrollHeight;
        });
}

carregarMensagens();
setInterval(carregarMensagens, 5000); // Atualiza a cada 5s

document.getElementById("form-mensagem").addEventListener("submit", function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch("ajax_enviar_mensagem.php", {
        method: "POST",
        body: fd
    }).then(() => {
        this.mensagem.value = "";
        carregarMensagens();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
