<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id']) || !isset($_GET['para'])) {
    header("Location: login.php");
    exit;
}

$logado = true;
$usuario_id = $_SESSION["usuario_id"];
$id_para = (int) $_GET["para"];

// Buscar grupos em que o usuário logado é moderador
$stmt = $conn->prepare("SELECT g.id, g.nome FROM grupos g
    JOIN grupo_membros gm ON gm.id_grupo = g.id
    WHERE gm.id_usuario = ? AND gm.moderador = 1
    ORDER BY g.nome");
$stmt->execute([$usuario_id]);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar eventos criados pelo usuário logado
$stmt = $conn->prepare("SELECT id, nome FROM eventos WHERE criado_por = ? ORDER BY nome");
$stmt->execute([$usuario_id]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar envio do convite
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $id_destino = (int) $_POST['id_destino'];

    if ($tipo && $id_destino && in_array($tipo, ['grupo', 'evento'])) {
        // Verificar se já existe convite do mesmo tipo e destino para o mesmo usuário
        $stmt = $conn->prepare("SELECT COUNT(*) FROM convites WHERE id_de = ? AND id_para = ? AND tipo = ? AND id_destino = ?");
        $stmt->execute([$usuario_id, $id_para, $tipo, $id_destino]);
        $ja_enviado = $stmt->fetchColumn();

        if ($ja_enviado == 0) {
            $stmt = $conn->prepare("INSERT INTO convites (id_de, id_para, tipo, id_destino) VALUES (?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $id_para, $tipo, $id_destino]);

            header("Location: match.php?sucesso=1");
            exit;
        } else {
            $erro = "Convite já enviado anteriormente para este destino.";
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2>Convidar Usuário</h2>
    <p class="text-muted">Convide o usuário para participar de um grupo seu ou de um evento que você criou.</p>

    <?php if (!empty($erro)): ?>
    <div class="alert alert-warning"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>
    <?php if (!empty($grupos)): ?>
    <form method="POST" class="mb-4">
        <input type="hidden" name="id_destinatario" value="<?= $id_para ?>">
        <input type="hidden" name="tipo" value="grupo">
        <div class="mb-2">
            <label class="form-label">Convidar para Grupo:</label>
            <select name="id_destino" class="form-select">
                <?php foreach ($grupos as $g): ?>
                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-success">Enviar Convite</button>
    </form>
<?php endif; ?>

<?php if (!empty($eventos)): ?>
    <form method="POST" class="mb-4">
        <input type="hidden" name="id_destinatario" value="<?= $id_para ?>">
        <input type="hidden" name="tipo" value="evento">
        <div class="mb-2">
            <label class="form-label">Convidar para Evento:</label>
            <select name="id_destino" class="form-select">
                <?php foreach ($eventos as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary">Enviar Convite</button>
    </form>
<?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
