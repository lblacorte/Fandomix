<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $_SESSION["usuario_id"];
$foto_perfil = isset($_SESSION["foto"]) ? "uploads/" . $_SESSION["foto"] : "uploads/default.png";

include 'includes/header.php';
include 'includes/navbar.php';
?>

<h2 class="mb-4">Suas Avaliações</h2>

<form id="filtros-avaliacoes" class="row g-3 mb-4">
    <div class="col-md-3">
        <input type="text" name="titulo" class="form-control" placeholder="Título ou Gênero">
    </div>
    <div class="col-md-2">
        <select name="nota" class="form-select">
            <option value="">Nota mínima</option>
            <option value="1">1+</option>
            <option value="2">2+</option>
            <option value="3">3+</option>
            <option value="4">4+</option>
            <option value="5">5</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="data" class="form-control">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
    </div>
</form>

<div class="row row-cols-1 row-cols-md-2 g-4" id="lista-avaliacoes"></div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById("form-filtros").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("ajax/filtrar_avaliacoes.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById("resultado-avaliacoes").innerHTML = html;
        document.getElementById("mensagem").textContent = html.trim() === "" ? "Nenhuma avaliação encontrada." : "";
    })
    .catch(err => console.error("Erro ao buscar avaliações:", err));
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("filtros-avaliacoes");
    const container = document.getElementById("lista-avaliacoes");

    function buscarAvaliacoes() {
        const formData = new FormData(form);
        fetch("ajax/filtrar_avaliacoes.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = "<div class='col'><div class='alert alert-danger'>Erro ao buscar avalia&ccedil;&otilde;es.</div></div>";
        });
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        buscarAvaliacoes();
    });

    buscarAvaliacoes(); // inicial
});
</script>