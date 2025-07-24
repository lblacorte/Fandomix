<?php
session_start();
include 'config.php';

$logado = isset($_SESSION["usuario_id"]);
$foto_perfil = $logado ? "uploads/" . (isset($_SESSION["foto"]) ? $_SESSION["foto"] : "default.png") : null;

$preferencias = [];
if ($logado) {
    $stmt = $conn->prepare("SELECT generos FROM preferencias WHERE id_usuario = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    $result = $stmt->fetch();
    if ($result && !empty($result["generos"])) {
        $preferencias = explode(",", $result["generos"]);
    }
}

function buscarDescobertas($conn, $preferencias, $offset = 0, $limit = 16) {
    if (count($preferencias) < 10 && !empty($preferencias)) {
        $placeholders = implode(',', array_fill(0, count($preferencias), '?'));
        $sql = "SELECT * FROM conteudos WHERE genero NOT IN ($placeholders) ORDER BY acessos DESC LIMIT $limit OFFSET $offset";
        $stmt = $conn->prepare($sql);
        $stmt->execute($preferencias);
    } else {
        $sql = "SELECT * FROM conteudos ORDER BY acessos DESC LIMIT $limit OFFSET $offset";
        $stmt = $conn->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$conteudos = buscarDescobertas($conn, $preferencias);
$interacoes = $logado ? getInteracoesUsuario($conn, $_SESSION["usuario_id"]) : [];

function getInteracoesUsuario($conn, $id_usuario) {
    $sql = "SELECT iu.id_conteudo, iu.lista_desejos, iu.assistido, iu.jogado, iu.favorito, a.nota
        FROM interacoes_usuario iu
        LEFT JOIN avaliacoes a ON a.id_conteudo = iu.id_conteudo AND a.id_usuario = iu.id_usuario
        WHERE iu.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_usuario]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $interacoes = [];
    foreach ($dados as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }
    return $interacoes;
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-3">Descubra Novos Conteúdos</h2>
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-3" id="conteudos-descobrir">
        <?php foreach ($conteudos as $item): ?>
            <?php include 'includes/card_conteudo.php'; ?>
        <?php endforeach; ?>
    </div>
    <div class="text-center mb-5">
        <button id="btn-ver-mais-descobrir" class="btn btn-outline-secondary">Ver mais</button>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let offsetDescobrir = 16;
document.getElementById("btn-ver-mais-descobrir").addEventListener("click", function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = "Carregando...";

    fetch("carregar_descobertas.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "offset=" + offsetDescobrir
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById("conteudos-descobrir").insertAdjacentHTML("beforeend", html);
        btn.disabled = false;
        btn.textContent = "Ver mais";
        offsetDescobrir += 16;
    })
    .catch(err => console.error("Erro ao carregar mais descobertas:", err));
});
</script>
