<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $_SESSION['usuario_id'];
$foto_perfil = "uploads/" . ($_SESSION["foto"] ?? "default.png");

include 'includes/header.php';
include 'includes/navbar.php';

// Buscar convites recebidos
$stmt = $conn->prepare("
    SELECT c.*, u.nome AS nome_remetente,
        CASE 
            WHEN c.tipo = 'grupo' THEN (SELECT nome FROM grupos WHERE id = c.id_destino)
            WHEN c.tipo = 'evento' THEN (SELECT nome FROM eventos WHERE id = c.id_destino)
            ELSE NULL
        END AS nome_destino
    FROM convites c
    JOIN usuarios u ON u.id = c.id_de
    WHERE c.id_para = ?
    ORDER BY c.data_envio DESC
");
$stmt->execute([$usuario_id]);
$convites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h2 class="mb-4">Convites Recebidos</h2>

    <?php if (empty($convites)): ?>
        <div class="alert alert-info">Você ainda não recebeu nenhum convite.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($convites as $c): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($c['nome_remetente']) ?></strong> te convidou para 
                        <?= $c['tipo'] === 'grupo' ? 'o grupo' : 'o evento' ?>
                        <strong><?= htmlspecialchars($c['nome_destino']) ?></strong>.
                        <br>
                        <small class="text-muted">Enviado em <?= date('d/m/Y H:i', strtotime($c['data_envio'])) ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($c['status'] === 'pendente'): ?>
                            <button class="btn btn-sm btn-success" onclick="responderConvite(<?= $c['id'] ?>, 'aceitar', this)">Aceitar</button>
                            <button class="btn btn-sm btn-danger" onclick="responderConvite(<?= $c['id'] ?>, 'rejeitar', this)">Rejeitar</button>
                        <?php else: ?>
                            <span class="badge bg-<?= $c['status'] === 'aceito' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        <?php endif; ?>
                        <a href="<?= $c['tipo'] === 'grupo' ? "grupo.php?id={$c['id_destino']}" : "evento.php?id={$c['id_destino']}" ?>"
                           class="btn btn-outline-primary btn-sm">
                            Ver <?= $c['tipo'] === 'grupo' ? "Grupo" : "Evento" ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function responderConvite(id, acao, btn) {
    const formData = new FormData();
    formData.append("id_convite", id);
    formData.append("acao", acao);

    fetch("responder_convite.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(resp => {
        if (resp === "aceito" || resp === "rejeitado") {
            const container = btn.closest(".d-flex");
            container.innerHTML = `<span class="badge bg-${resp === 'aceito' ? 'success' : 'secondary'}">${resp.charAt(0).toUpperCase() + resp.slice(1)}</span>`;
        }
    })
    .catch(err => {
        console.error("Erro ao responder convite:", err);
        alert("Erro ao processar convite.");
    });
}
</script>

<?php include 'includes/footer.php'; ?>
