<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$id_usuario = $_SESSION['usuario_id'];
$foto_perfil = isset($_SESSION['foto']) ? "uploads/" . $_SESSION['foto'] : "uploads/default.png";

function getConteudosPorInteracao($conn, $id_usuario, $campo) {
    $sql = "SELECT c.* FROM conteudos c
            JOIN interacoes_usuario iu ON iu.id_conteudo = c.id
            WHERE iu.id_usuario = ? AND iu.$campo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_usuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

$interacoes = getInteracoesUsuario($conn, $id_usuario);
$favoritos = getConteudosPorInteracao($conn, $id_usuario, 'favorito');
$desejos = getConteudosPorInteracao($conn, $id_usuario, 'lista_desejos');
$jogados_assistidos = getConteudosPorInteracao($conn, $id_usuario, 'jogado');
$jogados_assistidos = array_merge($jogados_assistidos, getConteudosPorInteracao($conn, $id_usuario, 'assistido'));

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2 class="mb-3">Seus Favoritos</h2>
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-4" id="secao-favoritos">
        <?php foreach ($favoritos as $item): ?>
            <?php include 'includes/card_conteudo.php'; ?>
        <?php endforeach; ?>
    </div>

    <h2 class="mb-3">Na Lista de Desejos</h2>
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-4" id="secao-desejos">
        <?php foreach ($desejos as $item): ?>
            <?php include 'includes/card_conteudo.php'; ?>
        <?php endforeach; ?>
    </div>

    <h2 class="mb-3">Assistidos / Jogados</h2>
    <div class="row row-cols-1 row-cols-md-4 g-4 mb-4" id="secao-consumidos">
        <?php foreach ($jogados_assistidos as $item): ?>
            <?php include 'includes/card_conteudo.php'; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function interagir(idConteudo, botao) {
    const tipo = botao.getAttribute("value");
    const tipoConteudo = botao.dataset.tipo;
    const ativo = botao.classList.contains("btn-success");

    const fd = new FormData();
    fd.append("id_conteudo", idConteudo);
    fd.append("tipo", tipo);

    fetch("interagir.php", {
        method: "POST",
        body: fd
    }).then(() => {
        location.reload();
    }).catch(err => {
        console.error("Erro ao interagir:", err);
    });
}

function toggleFavorito(id, el) {
    fetch('favoritar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_conteudo=' + id
    }).then(() => {
        location.reload();
    });
}
</script>

<script>
function avaliar(id, nota) {
    fetch("salvar_nota.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_conteudo=${id}&nota=${nota}`
    })
    .then(res => res.text())
    .then(resp => {
        if (resp.trim() === "ok") {
            // Atualiza visualmente as estrelas
            const estrelas = document.querySelectorAll("#estrelas-avaliacao i");
            estrelas.forEach((estrela, index) => {
                estrela.classList.remove("bi-star-fill", "text-warning");
                estrela.classList.add("bi-star");
                if (index < nota) {
                    estrela.classList.remove("bi-star");
                    estrela.classList.add("bi-star-fill", "text-warning");
                }
            });
            window.location.href = `avaliacao.php?id=${id}&nota=${nota}`
        } else {
            console.error("Erro ao salvar nota:", resp);
        }
    })
    .catch(err => console.error("Erro ao salvar nota:", err));
}
</script>