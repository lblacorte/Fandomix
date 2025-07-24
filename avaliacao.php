<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION['usuario_id']);
$foto_perfil = $logado ? "uploads/" . (isset($_SESSION["foto"]) ? $_SESSION["foto"] : "default.png") : null;

include 'includes/header.php';
include 'includes/navbar.php';

$id_conteudo = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nota_atual = isset($_GET['nota']) ? (int)$_GET['nota'] : 0;

$stmt = $conn->prepare("SELECT * FROM conteudos WHERE id = ?");
$stmt->execute([$id_conteudo]);
$conteudo = $stmt->fetch();

if (!$conteudo) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Conteúdo não encontrado.</div></div>";
    include 'includes/footer.php';
    exit;
}

// Busca nota e resenha existentes
$stmt = $conn->prepare("SELECT nota, resenha FROM avaliacoes WHERE id_usuario = ? AND id_conteudo = ?");
$stmt->execute([$_SESSION["usuario_id"], $id_conteudo]);
$avaliacao = $stmt->fetch();

$nota_atual = $avaliacao ? $avaliacao['nota'] : $nota_atual;
$resenha = $avaliacao ? $avaliacao['resenha'] : '';
?>

<div class="container mt-2 mb-5">
    <h2>Avaliação</h2>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php
                $item = $conteudo;
                $id = $item['id'];
                $tipo = $item['tipo'];
                $desejado = false;
                $marcado = false;
                // Busca interação real para o conteúdo atual
$stmt = $conn->prepare("SELECT lista_desejos, assistido, jogado, favorito FROM interacoes_usuario WHERE id_usuario = ? AND id_conteudo = ?");
$stmt->execute([$_SESSION["usuario_id"], $id_conteudo]);
$dados_interacao = $stmt->fetch(PDO::FETCH_ASSOC);

$interacoes = [
    $id => [
        'lista_desejos' => $dados_interacao['lista_desejos'] ?? 0,
        'assistido' => $dados_interacao['assistido'] ?? 0,
        'jogado' => $dados_interacao['jogado'] ?? 0,
        'favorito' => $dados_interacao['favorito'] ?? 0,
        'nota' => $nota_atual
    ]
];
                include 'includes/card_conteudo.php';
            ?>

            <div class="text-center mt-3">
                <h5 class="mb-2">Sua Nota</h5>
                <div id="estrelas-avaliacao">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi <?= $i <= $nota_atual ? 'bi-star-fill text-warning' : 'bi-star' ?>" 
                           style="font-size: 1.8rem; cursor: pointer;"
                           onclick="enviarAvaliacao(<?= $id_conteudo ?>, <?= $i ?>)"></i>
                    <?php endfor; ?>
                </div>
                <div id="msg-avaliacao" class="text-success mt-2" style="display:none;">Nota salva!</div>
            </div>

            <form id="form-resenha" class="mt-4">
                <input type="hidden" name="id_conteudo" value="<?= $id_conteudo ?>">
                <div class="mb-3">
                    <label for="resenha" class="form-label">Sua resenha</label>
                    <textarea name="resenha" id="resenha" class="form-control" rows="5" required><?= htmlspecialchars($resenha) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Resenha</button>
                <div id="mensagem-resenha" class="text-success mt-2" style="display:none;">Resenha salva com sucesso!</div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function enviarAvaliacao(idConteudo, nota) {
    const formData = new FormData();
    formData.append("id_conteudo", idConteudo);
    formData.append("nota", nota);

    fetch("salvar_nota.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        const estrelas = document.querySelectorAll("#estrelas-avaliacao i");
        estrelas.forEach((estrela, index) => {
            estrela.classList.remove("bi-star-fill", "text-warning");
            estrela.classList.add("bi-star");
            if (index < nota) {
                estrela.classList.remove("bi-star");
                estrela.classList.add("bi-star-fill", "text-warning");
            }
        });

        const msg = document.getElementById("msg-avaliacao");
        msg.style.display = "block";
        msg.textContent = "Nota salva!";
        setTimeout(() => { msg.style.display = "none"; }, 1500);
    })
    .catch(() => alert("Erro ao salvar nota"));
}

// AJAX para salvar resenha
const formResenha = document.getElementById("form-resenha");
formResenha.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("salvar_resenha.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        const msg = document.getElementById("mensagem-resenha");
        msg.style.display = "block";
        setTimeout(() => { msg.style.display = "none"; }, 2000);
    })
    .catch(() => alert("Erro ao salvar resenha"));
});
</script>
