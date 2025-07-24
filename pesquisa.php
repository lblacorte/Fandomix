<?php
session_start();
include 'config.php';

$logado = isset($_SESSION['usuario_id']);
$usuario_id = $logado ? $_SESSION['usuario_id'] : null;
$foto_perfil = $logado ? "uploads/" . (isset($_SESSION["foto"]) ? $_SESSION["foto"] : "default.png") : null;

?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4">Pesquisar Conteúdos</h2>

    <form id="form-pesquisa" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control" placeholder="Buscar por título">
        </div>
        <div class="col-md-2">
            <select name="tipo" class="form-select">
                <option value="">Tipo</option>
                <option value="filme">Filme</option>
                <option value="série">Série</option>
                <option value="jogo">Jogo</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="genero" class="form-control" placeholder="Gênero">
        </div>
        <div class="col-md-2">
            <input type="number" name="data" class="form-control" placeholder="Ano">
        </div>
        <div class="col-md-2">
            <select name="avaliacao" class="form-select">
                <option value="">Avaliação</option>
                <option value="3">3+</option>
                <option value="4">4+</option>
                <option value="5">5</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Pesquisar</button>
        </div>
    </form>

    <hr>

    <div id="resultados" class="row row-cols-1 row-cols-md-4 g-4"></div>
    <div id="mensagem" class="text-muted mt-3"></div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById("form-pesquisa").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = new FormData(this);

    fetch("ajax/buscar_conteudos.php", {
        method: "POST",
        body: form
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById("resultados").innerHTML = html;
        document.getElementById("mensagem").textContent = html.trim() === "" ? "Nenhum conteúdo encontrado." : "";
    })
    .catch(err => {
        document.getElementById("mensagem").textContent = "Erro na pesquisa.";
        console.error(err);
    });
});
</script>

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
        botao.classList.toggle("btn-success", !ativo);
        botao.classList.toggle("btn-outline-secondary", ativo);

        if (tipo === "desejo") {
            botao.textContent = ativo ? "Adicionar à lista" : "Na lista de desejos";
        } else if (tipo === "consumo") {
            botao.textContent = ativo
                ? (tipoConteudo === "jogado" ? "Joguei?" : "Assisti?")
                : (tipoConteudo === "jogado" ? "Jogado" : "Assistido");

            // Atualizar o card via AJAX
            atualizarCard(idConteudo);
        }
    }).catch(err => {
        console.error("Erro ao interagir:", err);
    });
}

function atualizarCard(id) {
    fetch("atualizar_card.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
    })
    .then(res => res.text())
    .then(html => {
        const card = document.querySelector(`[data-card-id='${id}']`);
        if (card) {
            const wrapper = document.createElement("div");
            wrapper.innerHTML = html.trim();
            card.replaceWith(wrapper.firstChild);
        }
    })
    .catch(err => {
        console.error("Erro ao atualizar card:", err);
    });
}
</script>

<script>
function toggleFavorito(id, el) {
    fetch('favoritar.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_conteudo=' + id
    }).then(() => {
        el.classList.toggle('bi-star');
        el.classList.toggle('bi-star-fill');
        el.classList.toggle('text-warning');
        el.classList.toggle('text-dark');
    });
}

function destacarEstrelas(el, qtd) {
    const container = el.parentElement;
    const estrelas = container.querySelectorAll('i');
    estrelas.forEach((e, i) => {
        e.classList.remove('text-warning');
        if (i < qtd) e.classList.add('text-warning');
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