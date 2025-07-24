<?php
session_start();
include 'config.php';

// Verifica se o usuário está logado
$logado = isset($_SESSION["usuario_id"]);
$foto_perfil = $logado ? "uploads/" . (isset($_SESSION["foto"]) ? $_SESSION["foto"] : "default.png") : null;

// Buscar gêneros preferidos (simulação por enquanto)
$preferencias = [];
if ($logado) {
    $stmt = $conn->prepare("SELECT generos FROM preferencias WHERE id_usuario = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    $result = $stmt->fetch();
    
    if ($result && !empty($result["generos"])) {
        $preferencias = explode(",", $result["generos"]);
    }
}

// Se estiver logado mas não houver preferências, usa aleatórios
if ($logado && empty($preferencias)) {
    $stmt = $conn->query("SELECT genero FROM conteudos GROUP BY genero");
    $preferencias = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Buscar conteúdos dinâmicos
function buscarParaVoce($conn, $preferencias) {
    if (!empty($preferencias)) {
        $placeholders = implode(',', array_fill(0, count($preferencias), '?'));
        $sql = "SELECT * FROM conteudos WHERE genero IN ($placeholders) ORDER BY RAND() LIMIT 8";
        $stmt = $conn->prepare($sql);
        $stmt->execute($preferencias);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Sem preferências? Mostra aleatórios
        $sql = "SELECT * FROM conteudos ORDER BY RAND() LIMIT 8";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function buscarNovidades($conn) {
    $sql = "SELECT * FROM conteudos 
        WHERE data_lancamento BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
        ORDER BY data_lancamento DESC 
        LIMIT 8";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarDestaques($conn) {
    $sql = "SELECT * FROM conteudos ORDER BY acessos DESC LIMIT 8";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$para_voce = buscarParaVoce($conn, $preferencias);
$novidades = buscarNovidades($conn);
$destaques = buscarDestaques($conn);
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

<?php
$interacoes = $logado ? getInteracoesUsuario($conn, $_SESSION["usuario_id"]) : [];
?>

<h2 class="mb-3">Para Você</h2>
<div class="row row-cols-1 row-cols-md-4 g-4 mb-3" id="conteudos-para-voce">
    <?php foreach ($para_voce as $item): ?>
        <?php include 'includes/card_conteudo.php'; ?>
    <?php endforeach; ?>
</div>
<div class="text-center mb-5">
    <button id="btn-ver-mais" class="btn btn-outline-secondary">Ver mais</button>
</div>

    <h2 class="mb-3">Novidades</h2>
<div class="row row-cols-1 row-cols-md-4 g-4 mb-3" id="conteudos-novidades">
    <?php foreach ($novidades as $item): ?>
        <?php include 'includes/card_conteudo.php'; ?>
    <?php endforeach; ?>
</div>
<div class="text-center mb-5">
    <button id="btn-ver-mais-novidades" class="btn btn-outline-secondary">Ver mais</button>
</div>

    <h2 class="mb-3">Destaques da Comunidade</h2>
<div class="row row-cols-1 row-cols-md-4 g-4 mb-3" id="conteudos-destaques">
    <?php foreach ($destaques as $item): ?>
        <?php include 'includes/card_conteudo.php'; ?>
    <?php endforeach; ?>
</div>
<div class="text-center mb-5">
    <button id="btn-ver-mais-destaques" class="btn btn-outline-secondary">Ver mais</button>
</div>

<script>
let offsetParaVoce = 8;
let offsetNovidades = 8;
let offsetDestaques = 8;

function carregarMais(botaoId, url, containerId, offsetVar) {
    const btn = document.getElementById(botaoId);
    btn.disabled = true;
    btn.innerText = "Carregando...";

    fetch(url, {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "offset=" + offsetVar
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById(containerId).insertAdjacentHTML("beforeend", html);
        btn.disabled = false;
        btn.innerText = "Ver mais";
        if (url.includes("para_voce")) offsetParaVoce += 8;
        if (url.includes("novidades")) offsetNovidades += 8;
        if (url.includes("destaques")) offsetDestaques += 8;
    });
}

document.getElementById("btn-ver-mais").addEventListener("click", function() {
    carregarMais("btn-ver-mais", "carregar_para_voce.php", "conteudos-para-voce", offsetParaVoce);
});
document.getElementById("btn-ver-mais-novidades").addEventListener("click", function() {
    carregarMais("btn-ver-mais-novidades", "carregar_novidades.php", "conteudos-novidades", offsetNovidades);
});
document.getElementById("btn-ver-mais-destaques").addEventListener("click", function() {
    carregarMais("btn-ver-mais-destaques", "carregar_destaques.php", "conteudos-destaques", offsetDestaques);
});
</script>

<?php include 'includes/footer.php'; ?>

<?php if ($logado): ?>
<div id="floating-msg" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none; cursor: pointer;"
     onclick="window.location.href='historico_mensagens.php'">
    <div class="bg-white border shadow-lg rounded-circle position-relative" style="width: 60px; height: 60px;">
        <img id="msg-foto" src="uploads/default.png" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
        <span id="msg-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle badge-pill">
            0
        </span>
    </div>
</div>
<?php endif; ?>

<audio id="notif-audio" src="assets/sounds/notify.mp3" preload="auto"></audio>

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
<?php if ($logado): ?>
let ultimaQtdMensagens = 0;

function atualizarNotificacoes() {
    fetch("notificacao_mensagens.php")
        .then(res => res.json())
        .then(data => {
            const box = document.getElementById("floating-msg");
            const audio = document.getElementById("notif-audio");

            if (data.novas > 0) {
                box.style.display = "block";
                document.getElementById("msg-count").textContent = data.novas;
                document.getElementById("msg-foto").src = "uploads/" + data.foto;

                if (data.novas > ultimaQtdMensagens) {
                    audio.play();
                }
            } else {
                box.style.display = "none";
            }

            ultimaQtdMensagens = data.novas;
        });
}

setInterval(atualizarNotificacoes, 1000);
atualizarNotificacoes();
<?php endif; ?>
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