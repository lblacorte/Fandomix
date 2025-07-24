<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$logado = true;
$usuario_id = $_SESSION['usuario_id'];
$foto_perfil = isset($_SESSION['foto']) ? "uploads/" . $_SESSION['foto'] : "uploads/default.png";

// Obter preferências e tags do usuário logado
$stmt = $conn->prepare("SELECT generos, tags FROM preferencias WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$preferencias_usuario = $stmt->fetch();

$generos_array = !empty($preferencias_usuario['generos']) ? explode(',', $preferencias_usuario['generos']) : [];
$tags_array = !empty($preferencias_usuario['tags']) ? explode(',', $preferencias_usuario['tags']) : [];

// Buscar todos os usuários com suas preferências (menos o atual)
$stmt = $conn->prepare("
    SELECT u.id, u.nome, u.usuario, u.foto, p.generos, p.tags
    FROM usuarios u
    JOIN preferencias p ON u.id = p.id_usuario
    WHERE u.id != ?
");
$stmt->execute([$usuario_id]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular pontuação de compatibilidade
$sugeridos = [];

foreach ($usuarios as $user) {
    $user_generos = !empty($user['generos']) ? explode(',', $user['generos']) : [];
    $user_tags = !empty($user['tags']) ? explode(',', $user['tags']) : [];

    $generos_comuns = array_intersect($generos_array, $user_generos);
    $tags_comuns = array_intersect($tags_array, $user_tags);

    $pontuacao = count($generos_comuns) + (count($tags_comuns) * 4);

    if ($pontuacao > 0) {
        $user['pontuacao'] = $pontuacao;
        $user['generos'] = implode(', ', $user_generos);
        $user['tags'] = implode(', ', $user_tags);
        $sugeridos[] = $user;
    }
}

// Ordenar por pontuação decrescente
usort($sugeridos, function ($a, $b) {
    return $b['pontuacao'] <=> $a['pontuacao'];
});

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Match por Gostos e Estilos</h2>
    <?php if (count($sugeridos) > 0): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($sugeridos as $user): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <a href="perfil.php?id=<?= $user['id'] ?>" class="text-decoration-none text-dark">
    <img src="uploads/<?= isset($user['foto']) && $user['foto'] ? $user['foto'] : 'default.png' ?>" class="rounded-circle mb-2" width="80" height="80" alt="<?= htmlspecialchars($user['nome']) ?>">
</a>
<h5 class="card-title">
    <a href="perfil.php?id=<?= $user['id'] ?>" class="text-decoration-none text-dark">@<?= htmlspecialchars($user['usuario']) ?></a>
</h5>
                            <p class="text-muted" style="font-size: 0.9rem;">
    <span class="text-primary fw-semibold">Gêneros:</span> <?= htmlspecialchars($user['generos']) ?><br>
    <span class="text-success fw-semibold">Tags:</span> <?= htmlspecialchars($user['tags']) ?>
</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="mensagem.php?para=<?= $user['id'] ?>" class="btn btn-outline-primary btn-sm mt-2">Enviar mensagem</a>
                                <a href="convidar.php?para=<?= $user['id'] ?>" class="btn btn-outline-success btn-sm mt-2">Convidar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Nenhum usuário com gostos semelhantes encontrado ainda. Atualize suas preferências para melhorar as recomendações.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
