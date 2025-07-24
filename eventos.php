<?php
session_start();
include 'config.php';

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;

// Buscar eventos criados ou marcados como "quero ir"
$meus_eventos = $logado ? $conn->prepare("
    SELECT e.*, u.nome AS criador_nome
    FROM eventos e
    JOIN usuarios u ON u.id = e.criado_por
    LEFT JOIN evento_participantes ep ON ep.id_evento = e.id AND ep.id_usuario = ?
    WHERE e.criado_por = ? OR ep.id_usuario = ?
    GROUP BY e.id
    ORDER BY e.data_evento DESC
") : null;

if ($logado) {
    $meus_eventos->execute([$usuario_id, $usuario_id, $usuario_id]);
    $meus = $meus_eventos->fetchAll(PDO::FETCH_ASSOC);

    // Buscar eventos que o usuário já confirmou presença
    $stmt = $conn->prepare("SELECT id_evento FROM evento_participantes WHERE id_usuario = ?");
    $stmt->execute([$usuario_id]);
    $eventos_confirmados = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_evento');
} else {
    $eventos_confirmados = [];
}

// Filtros GET
$termo = isset($_GET["termo"]) ? trim($_GET["termo"]) : '';
$data = isset($_GET["data"]) ? $_GET["data"] : '';
$local = isset($_GET["local"]) ? trim($_GET["local"]) : '';

$where = [];
$params = [];

if ($termo !== '') {
    $where[] = "(e.nome LIKE ? OR e.descricao LIKE ?)";
    $params[] = "%$termo%";
    $params[] = "%$termo%";
}
if ($data !== '') {
    $where[] = "e.data_evento = ?";
    $params[] = $data;
}
if ($local !== '') {
    $where[] = "e.local LIKE ?";
    $params[] = "%$local%";
}

$sql = "
    SELECT e.*, u.nome AS criador_nome,
        (SELECT COUNT(*) FROM evento_participantes WHERE id_evento = e.id) AS total_participantes
    FROM eventos e
    JOIN usuarios u ON e.criado_por = u.id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY e.data_criacao DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$descobrir = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Eventos</h2>
        <?php if ($logado): ?>
            <a href="criar_evento.php" class="btn btn-primary">Criar Evento</a>
        <?php endif; ?>
    </div>

    <?php if ($logado): ?>
        <h4>Seus Eventos</h4>
        <?php if (empty($meus)): ?>
            <div class="alert alert-info">Você ainda não criou ou marcou presença em nenhum evento.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5">
                <?php foreach ($meus as $evento): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($evento['nome']) ?></h5>
                                <p class="card-text">🗓 <?= date('d/m/Y', strtotime($evento['data_evento'])) ?> | 📍 <?= htmlspecialchars($evento['local']) ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="evento.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-outline-primary">Ver Evento</a>
                                <?php if ($evento['criado_por'] == $usuario_id): ?>
                                    <a href="editar_evento.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <h4>Descobrir Eventos</h4>
    <form id="filtro-eventos" class="row g-3 mb-4" method="get">
        <div class="col-md-4">
            <input type="text" name="termo" class="form-control" placeholder="Nome ou descrição..." value="<?= htmlspecialchars($termo) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($data) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="local" class="form-control" placeholder="Local" value="<?= htmlspecialchars($local) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
        </div>
    </form>

    <div id="resultados-eventos" class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($descobrir as $evento): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
    <a href="evento.php?id=<?= $evento['id'] ?>" class="text-decoration-none text-dark">
        <?= htmlspecialchars($evento['nome']) ?>
    </a>
</h5>
                        <p class="text-muted small">Organizado por <strong><?= htmlspecialchars($evento['criador_nome']) ?></strong> em <?= date('d/m/Y', strtotime($evento['data_evento'])) ?> – <?= htmlspecialchars($evento['local']) ?></p>
                        <p><?= nl2br(htmlspecialchars($evento['descricao'])) ?></p>
                        <p class="text-muted small">👥 <?= $evento['total_participantes'] ?> participante(s)</p>
                    </div>
                    <div class="card-footer text-end">
                        <?php if ($logado): ?>
                            <?php
                                $confirmado = in_array($evento['id'], $eventos_confirmados);
                                $classe_btn = $confirmado ? "btn-success" : "btn-outline-success";
                                $texto_btn = $confirmado ? "Você vai!" : "Quero ir";
                            ?>
                            <button onclick="marcarPresenca(<?= $evento['id'] ?>, this)" class="btn btn-sm <?= $classe_btn ?>"><?= $texto_btn ?></button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-sm btn-secondary">Entrar para participar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function marcarPresenca(idEvento, botao) {
    fetch("marcar_presenca.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id_evento=" + idEvento
    })
    .then(res => res.text())
    .then(resp => {
        if (resp === "confirmado") {
            botao.classList.remove("btn-outline-success");
            botao.classList.add("btn-success");
            botao.textContent = "Você vai!";
        } else if (resp === "removido") {
            botao.classList.remove("btn-success");
            botao.classList.add("btn-outline-success");
            botao.textContent = "Quero ir";
        }
    })
    .catch(err => {
        console.error("Erro ao marcar presença:", err);
    });
}
</script>
