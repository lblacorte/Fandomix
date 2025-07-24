<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $_SESSION['usuario_id'];
$foto_perfil = "uploads/" . ($_SESSION["foto"] ?? "default.png");
include 'includes/header.php';
include 'includes/navbar.php';

// Buscar grupos moderados
$stmt = $conn->prepare("
    SELECT id_grupo FROM grupo_membros 
    WHERE id_usuario = ? AND moderador = 1
");
$stmt->execute([$usuario_id]);
$grupos_moderados = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_grupo');

if (empty($grupos_moderados)) {
    echo '<div class="container py-4"><div class="alert alert-info">Você não modera nenhum grupo.</div></div>';
    include 'includes/footer.php';
    exit;
}

// Buscar solicitações para esses grupos
$placeholders = implode(',', array_fill(0, count($grupos_moderados), '?'));
$sql = "
    SELECT s.*, u.nome AS nome_usuario, u.usuario, u.foto, g.nome AS nome_grupo
    FROM grupo_solicitacoes s
    JOIN usuarios u ON u.id = s.id_usuario
    JOIN grupos g ON g.id = s.id_grupo
    WHERE s.id_grupo IN ($placeholders)
    ORDER BY s.data_solicitacao DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute($grupos_moderados);
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h2 class="mb-4">Solicitações de Entrada em Grupos</h2>

    <?php if (empty($solicitacoes)): ?>
        <div class="alert alert-secondary">Nenhuma solicitação pendente.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($solicitacoes as $s): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="uploads/<?= $s['foto'] ?: 'default.png' ?>" width="40" height="40" class="rounded-circle me-3" alt="foto">
                        <div>
                            <strong>
                                <a href="perfil.php?id=<?= $s['id_usuario'] ?>" class="text-decoration-none">@<?= htmlspecialchars($s['usuario']) ?></a>
                            </strong>
                            solicitou entrada no grupo 
                            <strong><a href="grupo.php?id=<?= $s['id_grupo'] ?>"><?= htmlspecialchars($s['nome_grupo']) ?></a></strong><br>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($s['data_solicitacao'])) ?></small>
                        </div>
                    </div>
                    <div class="btn-group">
                        <form action="responder_solicitacao.php" method="POST">
                            <input type="hidden" name="id_usuario" value="<?= $s['id_usuario'] ?>">
                            <input type="hidden" name="id_grupo" value="<?= $s['id_grupo'] ?>">
                            <button type="submit" name="acao" value="aceitar" class="btn btn-sm btn-success">Aceitar</button>
                            <button type="submit" name="acao" value="recusar" class="btn btn-sm btn-outline-danger">Recusar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
