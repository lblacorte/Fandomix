<?php
session_start();
include 'config.php';

$id_grupo = isset($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : 0;

$stmt = $conn->prepare("
    SELECT gm.*, u.nome, u.foto
    FROM grupo_mensagens gm
    JOIN usuarios u ON u.id = gm.id_usuario
    WHERE gm.id_grupo = ?
    ORDER BY gm.data_envio ASC
");
$stmt->execute([$id_grupo]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($mensagens as $msg): ?>
    <div class="d-flex mb-3">
        <img src="uploads/<?= $msg['foto'] ?: 'default.png' ?>" class="rounded-circle me-2" width="40" height="40">
        <div>
            <strong><?= htmlspecialchars($msg['nome']) ?></strong>
            <div class="bg-white border rounded p-2 mt-1"><?= nl2br(htmlspecialchars($msg['mensagem'])) ?></div>
            <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($msg['data_envio'])) ?></div>
        </div>
    </div>
<?php endforeach; ?>
