<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;
$foto_perfil = $logado ? "../uploads/" . ($_SESSION["foto"] ?? "../default.png") : null;

// Buscar histórico de conversas com última mensagem
$stmt = $conn->prepare("
    SELECT 
        u.id AS usuario_id,
        u.nome,
        u.usuario,
        u.foto,
        m.mensagem,
        m.data_envio,
        m.id_remetente,
        m.id_destinatario
    FROM mensagens m
    JOIN (
        SELECT 
            CASE 
                WHEN id_remetente = :id THEN id_destinatario
                ELSE id_remetente
            END AS parceiro_id,
            MAX(data_envio) AS ultima_data
        FROM mensagens
        WHERE id_remetente = :id OR id_destinatario = :id
        GROUP BY parceiro_id
    ) ult ON (m.data_envio = ult.ultima_data AND ((m.id_remetente = :id AND m.id_destinatario = ult.parceiro_id) OR (m.id_destinatario = :id AND m.id_remetente = ult.parceiro_id)))
    JOIN usuarios u ON u.id = ult.parceiro_id
    ORDER BY m.data_envio DESC
");
$stmt->execute(['id' => $usuario_id]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Histórico de Conversas</h2>

    <?php if (empty($historico)): ?>
        <div class="alert alert-info">
            Você ainda não iniciou conversas com outros usuários.
        </div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($historico as $u): ?>
                <li class="list-group-item d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="perfil.php?id=<?= $u['usuario_id'] ?>" class="d-flex align-items-center text-decoration-none text-dark">
    <img src="uploads/<?= isset($u['foto']) && $u['foto'] ? $u['foto'] : 'default.png' ?>" class="rounded-circle me-3" width="50" height="50" alt="Foto de perfil">
    <div>
        <strong><?= htmlspecialchars($u['nome']) ?></strong>
<span class="text-muted small ms-2">@<?= htmlspecialchars($u['usuario']) ?></span>
                            <div class="text-muted small">
                                <?= date('d/m/Y H:i', strtotime($u['data_envio'])) ?> • 
                                <?= htmlspecialchars(substr($u['mensagem'], 0, 50)) ?><?= strlen($u['mensagem']) > 50 ? '...' : '' ?>
                                </a>
                                <?php
// Verifica se a última mensagem foi enviada POR OUTRO e NÃO ESTÁ lida
$nova = ($u['id_remetente'] != $usuario_id);
if ($nova):
    $verifica = $conn->prepare("SELECT COUNT(*) FROM mensagens WHERE id_remetente = ? AND id_destinatario = ? AND lida = 0");
    $verifica->execute([$u['id_remetente'], $usuario_id]);
    if ($verifica->fetchColumn() > 0):
?>
    <span class="badge bg-success ms-2">Nova</span>
<?php
    endif;
endif;
?>

                            </div>
                        </div>
                    </div>
                    <a href="mensagem.php?para=<?= $u['usuario_id'] ?>" class="btn btn-sm btn-outline-primary">
                        Abrir
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>