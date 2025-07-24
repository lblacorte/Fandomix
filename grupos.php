<?php
session_start();
include 'config.php';

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $logado ? $_SESSION["usuario_id"] : null;

function isModerador($conn, $id_grupo, $id_usuario) {
    $stmt = $conn->prepare("SELECT 1 FROM grupo_membros WHERE id_grupo = ? AND id_usuario = ? AND moderador = 1");
    $stmt->execute([$id_grupo, $id_usuario]);
    return $stmt->fetch() !== false;
}

// Buscar grupos do usuário
$stmt = $conn->prepare("SELECT g.* FROM grupos g JOIN grupo_membros gm ON g.id = gm.id_grupo WHERE gm.id_usuario = ? ORDER BY g.data_criacao DESC");
$stmt->execute([$usuario_id]);
$meus_grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar grupos dos quais o usuário já participa ou solicitou entrada
$grupos_ocultos = [];
if ($logado) {
    $stmt = $conn->prepare("SELECT id_grupo FROM grupo_membros WHERE id_usuario = ? UNION SELECT id_grupo FROM grupo_solicitacoes WHERE id_usuario = ?");
    $stmt->execute([$usuario_id, $usuario_id]);
    $grupos_ocultos = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_grupo');
}

// Buscar todos os grupos para descoberta
$stmt = $conn->prepare("SELECT g.*, u.nome AS criador_nome, (SELECT COUNT(*) FROM grupo_membros WHERE id_grupo = g.id) AS total_membros FROM grupos g JOIN usuarios u ON g.criado_por = u.id ORDER BY g.data_criacao DESC");
$stmt->execute();
$todos_grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Grupos e Fóruns</h2>
        <?php if ($logado): ?>
            <a href="criar_grupo.php" class="btn btn-primary">Criar Novo Grupo</a>
        <?php endif; ?>
    </div>

    <?php if ($logado): ?>
    <h4 class="mb-3">Seus Grupos</h4>
    <?php if (empty($meus_grupos)): ?>
        <div class="alert alert-secondary">Você ainda não participa de nenhum grupo.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
            <?php foreach ($meus_grupos as $grupo): ?>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><?= htmlspecialchars($grupo['nome']) ?></h5>
                            <a href="grupo.php?id=<?= $grupo['id'] ?>" class="btn btn-outline-primary btn-sm">Acessar</a>
                            <?php if ($usuario_id == $grupo['criado_por'] || isModerador($conn, $grupo['id'], $usuario_id)): ?>
                                <a href="editar_grupo.php?id=<?= $grupo['id'] ?>" class="btn btn-outline-warning btn-sm ms-2">Editar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <h4 class="mb-3">Descobrir Grupos</h4>
    <form method="get" class="mb-4">
        <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou descrição do grupo" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
    </form>

    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php
        $busca = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
        foreach ($todos_grupos as $grupo):
            if (in_array($grupo['id'], $grupos_ocultos)) continue;
            if ($busca && !str_contains(strtolower($grupo['nome']), $busca) && !str_contains(strtolower($grupo['descricao']), $busca)) continue;

            $solicitado = isset($_GET['solicitado']) && $_GET['solicitado'] == $grupo['id'];
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($grupo['nome']) ?></h5>
                        <p class="text-muted small mb-1">
                            Criado por <strong><?= htmlspecialchars($grupo['criador_nome']) ?></strong>
                            em <?= date('d/m/Y', strtotime($grupo['data_criacao'])) ?>
                        </p>
                        <p><?= nl2br(htmlspecialchars($grupo['descricao'])) ?></p>
                        <p class="text-muted small">👥 <?= $grupo['total_membros'] ?> membro(s)</p>
                        <?php if ($solicitado): ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled>Solicitação enviada</button>
                        <?php else: ?>
                            <a href="pedir_entrar_grupo.php?id=<?= $grupo['id'] ?>" class="btn btn-sm btn-outline-success">Pedir para entrar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
