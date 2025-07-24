<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$id_grupo = (int)$_GET['id'];
$id_usuario = $_SESSION['usuario_id'];
$erro = "";

// Verifica se o usuário é o criador ou moderador do grupo
$stmt = $conn->prepare("SELECT * FROM grupos WHERE id = ?");
$stmt->execute([$id_grupo]);
$grupo = $stmt->fetch();

if (!$grupo || ($grupo['criado_por'] != $id_usuario && !isModerador($conn, $id_grupo, $id_usuario))) {
    header("Location: grupos.php");
    exit;
}

// Atualizar grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $moderadores = isset($_POST['moderadores']) ? $_POST['moderadores'] : [];

    if ($nome === '' || $descricao === '') {
        $erro = "Preencha todos os campos.";
    } else {
        $conn->prepare("UPDATE grupos SET nome = ?, descricao = ? WHERE id = ?")
            ->execute([$nome, $descricao, $id_grupo]);

        // Atualiza moderadores
        $conn->prepare("UPDATE grupo_membros SET moderador = 0 WHERE id_grupo = ?")->execute([$id_grupo]);
        foreach ($moderadores as $id_mod) {
            $conn->prepare("UPDATE grupo_membros SET moderador = 1 WHERE id_grupo = ? AND id_usuario = ?")
                ->execute([$id_grupo, $id_mod]);
        }

        header("Location: grupos.php");
        exit;
    }
}

// Excluir grupo
if (isset($_POST['excluir'])) {
    $conn->prepare("DELETE FROM grupo_membros WHERE id_grupo = ?")->execute([$id_grupo]);
    $conn->prepare("DELETE FROM grupos WHERE id = ?")->execute([$id_grupo]);
    header("Location: grupos.php");
    exit;
}

// Buscar membros do grupo
$stmt = $conn->prepare("
    SELECT u.id, u.nome, u.usuario, gm.moderador
    FROM grupo_membros gm
    JOIN usuarios u ON u.id = gm.id_usuario
    WHERE gm.id_grupo = ?
    ORDER BY u.nome
");
$stmt->execute([$id_grupo]);
$membros = $stmt->fetchAll(PDO::FETCH_ASSOC);

function isModerador($conn, $id_grupo, $id_usuario) {
    $stmt = $conn->prepare("SELECT 1 FROM grupo_membros WHERE id_grupo = ? AND id_usuario = ? AND moderador = 1");
    $stmt->execute([$id_grupo, $id_usuario]);
    return $stmt->fetch() !== false;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Editar Grupo</h2>

    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Grupo</label>
            <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars($grupo['nome']) ?>">
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" class="form-control" rows="4" required><?= htmlspecialchars($grupo['descricao']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Moderadores</label>
            <div style="max-height: 250px; overflow-y: auto;" class="border p-2 rounded bg-light">
                <?php foreach ($membros as $m): ?>
                    <?php if ($m['id'] != $id_usuario): // Oculta o próprio usuário ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="moderadores[]" value="<?= $m['id'] ?>" id="mod-<?= $m['id'] ?>"
                                <?= $m['moderador'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mod-<?= $m['id'] ?>">
                                <?= htmlspecialchars($m['nome']) ?> (@<?= htmlspecialchars($m['usuario']) ?>)
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" name="salvar" class="btn btn-success">Salvar Alterações</button>
        <a href="grupos.php" class="btn btn-secondary">Cancelar</a>
        <?php if ($id_usuario == $grupo['criado_por']): ?>
            <button type="submit" name="excluir" class="btn btn-danger float-end" onclick="return confirm('Tem certeza que deseja excluir este grupo?');">
                Excluir Grupo
            </button>
        <?php endif; ?>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
