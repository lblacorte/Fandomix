<?php
// Requer: $item, $interacoes, $logado

$id = $item['id'];
$desejado = isset($interacoes[$id]) && $interacoes[$id]['lista_desejos'];
$marcado = isset($interacoes[$id]) && ($item['tipo'] === 'jogo'
    ? $interacoes[$id]['jogado']
    : $interacoes[$id]['assistido']);
$nota_usuario = isset($interacoes[$id]['nota']) ? (int)$interacoes[$id]['nota'] : 0;
$assistido_ou_jogado = $item['tipo'] === 'jogo'
    ? (isset($interacoes[$id]['jogado']) && $interacoes[$id]['jogado'])
    : (isset($interacoes[$id]['assistido']) && $interacoes[$id]['assistido']);
?>

<div class="col" data-card-id="<?= $id ?>">
    <div class="card h-100 shadow-sm d-flex flex-column justify-content-between">
        <?php if ($logado): ?>
            <div class="position-absolute top-0 end-0 m-2">
                <i class="bi <?= $interacoes[$id]['favorito'] ? 'bi-star-fill text-warning' : 'bi-star text-dark' ?>" 
                   onclick="toggleFavorito(<?= $id ?>, this)" 
                   style="font-size: 1.5rem; cursor: pointer;"></i>
            </div>
        <?php endif; ?>

        <a href="conteudo/index.php?id=<?= $id ?>" class="text-decoration-none text-dark">
            <img src="assets/images/posters/<?= $item['imagem'] ?>" class="card-img-top" alt="<?= $item['titulo'] ?>" style="height: 250px; object-fit: contain;">
            <div class="card-body text-center">
                <h5 class="card-title"><?= $item['titulo'] ?></h5>
                <p class="text-muted mb-1" style="font-size: 0.85rem;">
                    📅 <?= date("d/m/Y", strtotime($item['data_lancamento'])) ?>
                    <?php
                    $hoje = new DateTime();
                    $lancamento = new DateTime($item['data_lancamento']);
                    $dias = $hoje->diff($lancamento)->days;

                    if ($lancamento > $hoje): ?>
                        <span class="badge bg-primary text-light ms-1">Lançamento futuro</span>
                    <?php elseif ($dias <= 20): ?>
                        <span class="badge bg-warning text-dark ms-1">Novo</span>
                    <?php endif; ?>
                </p>
                <span class="badge bg-<?= $item['tipo'] === 'jogo' ? 'secondary' : ($item['tipo'] === 'filme' ? 'info' : 'success') ?>">
                    <?= ucfirst($item['tipo']) ?>
                </span>
            </div>
        </a>

        <?php if ($logado): ?>
            <div class="card-footer p-0">
                <form class="d-flex" onsubmit="event.preventDefault(); interagir(<?= $id ?>, this)">
                    <input type="hidden" name="id_conteudo" value="<?= $id ?>">

                    <button type="button" name="tipo" value="desejo"
                        class="btn btn-sm w-50 border-end <?= $desejado ? 'btn-success' : 'btn-outline-secondary' ?>"
                        data-tipo="desejo" data-id="<?= $id ?>"
                        onclick="interagir(<?= $id ?>, this)">
                        <?= $desejado ? 'Na lista de desejos' : 'Adicionar à lista' ?>
                    </button>

                    <button type="button" name="tipo" value="consumo"
                        class="btn btn-sm w-50 <?= $marcado ? 'btn-success' : 'btn-outline-secondary' ?>"
                        data-tipo="<?= $item['tipo'] === 'jogo' ? 'jogado' : 'assistido' ?>"
                        data-id="<?= $id ?>"
                        onclick="interagir(<?= $id ?>, this)">
                        <?= $marcado
                            ? ($item['tipo'] === 'jogo' ? 'Jogado' : 'Assistido')
                            : ($item['tipo'] === 'jogo' ? 'Joguei?' : 'Assisti?') ?>
                    </button>
                </form>

                <?php if ($assistido_ou_jogado): ?>
                    <!-- Estrelas interativas -->
                    <div class="avaliacao mt-2" data-id="<?= $id ?>" data-nota="<?= $nota_usuario ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star"
                               style="cursor: pointer;"
                               onmouseover="destacarEstrelas(this, <?= $i ?>)"
                               onmouseout="resetarEstrelas(this)"
                               onclick="avaliar(<?= $id ?>, <?= $i ?>)"></i>
                        <?php endfor; ?>
                    </div>

                    <!-- Estrelas fixas do usuário -->
                    <?php if ($nota_usuario > 0): ?>
                        <div class="text-center mt-1 text-warning" style="font-size: 0.95rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= $i <= $nota_usuario ? 'bi-star-fill' : 'bi-star' ?>"></i>
                            <?php endfor; ?>
                            <span class="text-muted" style="font-size: 0.8rem;">(sua nota)</span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Avaliação média da comunidade -->
                <div class="d-flex justify-content-end align-items-center px-2 py-1 text-muted" style="font-size: 0.85rem;">
                    <i class="bi bi-star-fill text-warning me-1"></i>
                    <?= number_format(isset($item['avaliacao_media']) ? $item['avaliacao_media'] : 0, 1) ?>
                    (<?= isset($item['numero_avaliacoes']) ? $item['numero_avaliacoes'] : 0 ?>)
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function destacarEstrelas(el, qtd) {
    const container = el.parentElement;
    const estrelas = container.querySelectorAll('i');
    estrelas.forEach((e, i) => {
        e.classList.remove('text-warning');
        if (i < qtd) e.classList.add('text-warning');
    });
}

function resetarEstrelas(el) {
    const container = el.parentElement;
    const nota = parseInt(container.dataset.nota);
    const estrelas = container.querySelectorAll('i');
    estrelas.forEach((e, i) => {
        e.classList.remove('text-warning');
        if (i < nota) e.classList.add('text-warning');
    });
}

function avaliar(id, nota) {
    fetch("salvar_nota.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_conteudo=${id}&nota=${nota}`
    })
    .then(res => res.text())
    .then(resp => {
        if (resp.trim() === "ok") {
            location.reload();
        }
    })
    .catch(err => console.error("Erro ao salvar nota:", err));
}

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
