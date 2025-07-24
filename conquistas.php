<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);
$usuario_id = $_SESSION['usuario_id'];
$nome = $_SESSION['nome'];
$foto_perfil = "uploads/" . ($_SESSION["foto"] ?? "default.png");

// Carregar interações
$interacoes = [];
$stmt = $conn->prepare("
    SELECT c.tipo, c.genero, iu.*
    FROM interacoes_usuario iu
    JOIN conteudos c ON c.id = iu.id_conteudo
    WHERE iu.id_usuario = ?
");
$stmt->execute([$usuario_id]);
$interacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar avaliações
$stmt = $conn->prepare("SELECT c.tipo, c.genero FROM avaliacoes a JOIN conteudos c ON c.id = a.id_conteudo WHERE a.id_usuario = ?");
$stmt->execute([$usuario_id]);
$avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Resenhas
$stmt = $conn->prepare("SELECT COUNT(*) FROM avaliacoes WHERE id_usuario = ? AND resenha IS NOT NULL AND resenha != ''");
$stmt->execute([$usuario_id]);
$resenhas = $stmt->fetchColumn();

// Grupos
$stmt = $conn->prepare("SELECT COUNT(*) FROM grupo_membros WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$grupos = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM grupos WHERE criado_por = ?");
$stmt->execute([$usuario_id]);
$grupos_criados = $stmt->fetchColumn();

// Mensagens
$stmt = $conn->prepare("SELECT COUNT(*) FROM mensagens WHERE id_remetente = ?");
$stmt->execute([$usuario_id]);
$mensagens = $stmt->fetchColumn();

// Playlists
$stmt = $conn->prepare("SELECT COUNT(*) FROM playlists WHERE criado_por = ?");
$stmt->execute([$usuario_id]);
$playlists_criadas = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM playlist_conteudos pc JOIN playlists p ON p.id = pc.id_playlist WHERE p.criado_por = ?");
$stmt->execute([$usuario_id]);
$playlist_conteudos = $stmt->fetchColumn();

// Eventos
$stmt = $conn->prepare("SELECT COUNT(*) FROM evento_participantes WHERE id_usuario = ?");
$stmt->execute([$usuario_id]);
$eventos = $stmt->fetchColumn();

// Contadores
$contagem = [
    'filmes_assistidos' => 0,
    'jogos_jogados' => 0,
    'series_assistidas' => 0,
    'favoritos' => 0,
    'desejos_filmes' => 0,
    'avaliacoes' => count($avaliacoes),
    'interacoes_total' => count($interacoes),
    'genero_acao_filmes' => 0,
    'genero_terror_filmes' => 0,
    'genero_comedia_filmes' => 0,
    'genero_rpg_jogos' => 0,
    'genero_estrategia_jogos' => 0,
    'genero_acao_jogos' => 0,
    'desejos_jogos' => 0,
    'desejos_series' => 0,
    'desejos_total' => 0,
    'genero_romance_series' => 0,
    'genero_ficcao_series' => 0,
    'genero_drama_series' => 0,
    'genero_aventura_jogos' => 0,
    'genero_drama_filmes' => 0,
    'genero_suspense_series' => 0,
];

foreach ($interacoes as $i) {
    $tipo = $i['tipo'];
    $genero = strtolower($i['genero']);

    if ($i['assistido'] && $tipo === 'filme') $contagem['filmes_assistidos']++;
    if ($i['jogado'] && $tipo === 'jogo') $contagem['jogos_jogados']++;
    if ($i['assistido'] && $tipo === 'série') $contagem['series_assistidas']++;

    if ($i['favorito']) $contagem['favoritos']++;
    if ($i['lista_desejos'] && $tipo === 'filme') $contagem['desejos_filmes']++;

    if ($i['assistido'] && $tipo === 'filme' && $genero === 'ação') $contagem['genero_acao_filmes']++;
    if ($i['assistido'] && $tipo === 'filme' && $genero === 'terror') $contagem['genero_terror_filmes']++;
    if ($i['assistido'] && $tipo === 'filme' && $genero === 'comédia') $contagem['genero_comedia_filmes']++;
    if ($i['jogado'] && $tipo === 'jogo' && $genero === 'rpg') $contagem['genero_rpg_jogos']++;
    if ($i['jogado'] && $tipo === 'jogo' && $genero === 'estratégia') $contagem['genero_estrategia_jogos']++;
    if ($i['jogado'] && $tipo === 'jogo' && $genero === 'ação') $contagem['genero_acao_jogos']++;
    if ($i['lista_desejos'] && $tipo === 'jogo') $contagem['desejos_jogos']++;
    if ($i['lista_desejos']) $contagem['desejos_total']++;
    if ($i['lista_desejos'] && $tipo === 'série') $contagem['desejos_series']++;
    if ($i['assistido'] && $tipo === 'série' && $genero === 'romance') $contagem['genero_romance_series']++;
    if ($i['assistido'] && $tipo === 'série' && $genero === 'ficção') $contagem['genero_ficcao_series']++;
    if ($i['assistido'] && $tipo === 'série' && $genero === 'drama') $contagem['genero_drama_series']++;
    if ($i['assistido'] && $tipo === 'série' && $genero === 'aventura') $contagem['genero_aventura_series']++;
    if ($i['assistido'] && $tipo === 'série' && $genero === 'suspense') $contagem['genero_suspense_series']++;
    if ($i['jogado'] && $tipo === 'jogo' && $genero === 'aventura') $contagem['genero_aventura_jogos']++;
    if ($i['assistido'] && $tipo === 'filme' && $genero === 'drama') $contagem['genero_drama_filmes']++;
    if ($i['assistido'] && $tipo === 'série' && $genero === 'suspense') $contagem['genero_suspense_series']++;
}

// Definir conquistas
$conquistas = [
    ["Assistir 1 filme", $contagem['filmes_assistidos'] >= 1, $contagem['filmes_assistidos'], 1],
    ["Assistir 5 filmes", $contagem['filmes_assistidos'] >= 5, $contagem['filmes_assistidos'], 5],
    ["Assistir 10 filmes", $contagem['filmes_assistidos'] >= 10, $contagem['filmes_assistidos'], 10],
    ["Assistir 20 filmes", $contagem['filmes_assistidos'] >= 20, $contagem['filmes_assistidos'], 20],
    ["Assistir 50 filmes", $contagem['filmes_assistidos'] >= 50, $contagem['filmes_assistidos'], 50],
    ["Favoritar 5 filmes", $contagem['favoritos'] >= 5, $contagem['favoritos'], 5],
    ["Favoritar 10 filmes", $contagem['favoritos'] >= 10, $contagem['favoritos'], 10],
    ["Favoritar 15 filmes", $contagem['favoritos'] >= 15, $contagem['favoritos'], 15],
    ["Avaliar 5 filmes", $contagem['avaliacoes'] >= 5, $contagem['avaliacoes'], 5],
    ["Adicionar 5 filmes à lista de desejos", $contagem['desejos_filmes'] >= 5, $contagem['desejos_filmes'], 5],
    ["Assistir 5 filmes de Ação", $contagem['genero_acao_filmes'] >= 5, $contagem['genero_acao_filmes'], 5],
    ["Assistir 5 filmes de Drama", $contagem['genero_drama_filmes'] >= 5, $contagem['genero_drama_filmes'], 5],
    ["Assistir 5 filmes de Terror", $contagem['genero_terror_filmes'] >= 5, $contagem['genero_terror_filmes'], 5],
    ["Assistir 5 filmes de Comédia", $contagem['genero_comedia_filmes'] >= 5, $contagem['genero_comedia_filmes'], 5],
    ["Jogar 1 jogo", $contagem['jogos_jogados'] >= 1, $contagem['jogos_jogados'], 1],
    ["Jogar 5 jogos", $contagem['jogos_jogados'] >= 5, $contagem['jogos_jogados'], 5],
    ["Jogar 10 jogos", $contagem['jogos_jogados'] >= 10, $contagem['jogos_jogados'], 10],
    ["Jogar 20 jogos", $contagem['jogos_jogados'] >= 20, $contagem['jogos_jogados'], 20],
    ["Jogar 50 jogos", $contagem['jogos_jogados'] >= 50, $contagem['jogos_jogados'], 50],
    ["Favoritar 5 jogos", $contagem['favoritos'] >= 5, $contagem['favoritos'], 5],
    ["Favoritar 10 jogos", $contagem['favoritos'] >= 10, $contagem['favoritos'], 10],
    ["Avaliar 5 jogos", $contagem['avaliacoes'] >= 5, $contagem['avaliacoes'], 5],
    ["Adicionar 5 jogos à lista de desejos", $contagem['desejos_jogos'] >= 5, $contagem['desejos_jogos'], 5],
    ["Jogar 5 jogos de Aventura", $contagem['genero_aventura_jogos'] >= 5, $contagem['genero_aventura_jogos'], 5],
    ["Jogar 5 jogos de RPG", $contagem['genero_rpg_jogos'] >= 5, $contagem['genero_rpg_jogos'], 5],
    ["Jogar 5 jogos de Estratégia", $contagem['genero_estrategia_jogos'] >= 5, $contagem['genero_estrategia_jogos'], 5],
    ["Jogar 5 jogos de Ação", $contagem['genero_acao_jogos'] >= 5, $contagem['genero_acao_jogos'], 5],
    ["Assistir 1 série", $contagem['series_assistidas'] >= 1, $contagem['series_assistidas'], 1],
    ["Assistir 3 séries", $contagem['series_assistidas'] >= 3, $contagem['series_assistidas'], 3],
    ["Assistir 5 séries", $contagem['series_assistidas'] >= 5, $contagem['series_assistidas'], 5],
    ["Assistir 10 séries", $contagem['series_assistidas'] >= 10, $contagem['series_assistidas'], 10],
    ["Assistir 30 séries", $contagem['series_assistidas'] >= 30, $contagem['series_assistidas'], 30],
    ["Favoritar 3 séries", $contagem['favoritos'] >= 3, $contagem['favoritos'], 3],
    ["Avaliar 3 séries", $contagem['avaliacoes'] >= 3, $contagem['avaliacoes'], 3],
    ["Adicionar 3 séries à lista de desejos", $contagem['desejos_series'] >= 3, $contagem['desejos_series'], 3],
    ["Assistir 3 séries de Suspense", $contagem['genero_suspense_series'] >= 3, $contagem['genero_suspense_series'], 3],
    ["Assistir 3 séries de Romance", $contagem['genero_romance_series'] >= 3, $contagem['genero_romance_series'], 3],
    ["Assistir 3 séries de Ficção", $contagem['genero_ficcao_series'] >= 3, $contagem['genero_ficcao_series'], 3],
    ["Assistir 3 séries de Drama", $contagem['genero_drama_series'] >= 3, $contagem['genero_drama_series'], 3],
    ["Avaliar 1 conteúdo", $contagem['avaliacoes'] >= 1, $contagem['avaliacoes'], 1],
    ["Avaliar 10 conteúdos", $contagem['avaliacoes'] >= 10, $contagem['avaliacoes'], 10],
    ["Favoritar 1 conteúdo", $contagem['favoritos'] >= 1, $contagem['favoritos'], 1],
    ["Favoritar 10 conteúdos", $contagem['favoritos'] >= 10, $contagem['favoritos'], 10],
    ["Favoritar 30 conteúdos", $contagem['favoritos'] >= 30, $contagem['favoritos'], 30],
    ["Adicionar 10 conteúdos à lista de desejos", $contagem['desejos_total'] >= 10, $contagem['desejos_total'], 10],
    ["Assistir ou jogar 10 conteúdos", $contagem['interacoes_total'] >= 10, $contagem['interacoes_total'], 10],
    ["Interagir com 20 conteúdos", $contagem['interacoes_total'] >= 20, $contagem['interacoes_total'], 20],
    ["Interagir com 30 conteúdos", $contagem['interacoes_total'] >= 30, $contagem['interacoes_total'], 30],
    ["Interagir com 50 conteúdos", $contagem['interacoes_total'] >= 50, $contagem['interacoes_total'], 50],
    ["Interagir com 100 conteúdos", $contagem['interacoes_total'] >= 100, $contagem['interacoes_total'], 100],
    ["Participar de 1 grupo", $grupos >= 1, $grupos, 1],
    ["Participar de 5 grupos", $grupos >= 5, $grupos, 5],
    ["Criar 1 grupo", $grupos_criados >= 1, $grupos_criados, 1],
    ["Criar 3 grupos", $grupos_criados >= 3, $grupos_criados, 3],
    ["Enviar 5 mensagens privadas", $mensagens >= 5, $mensagens, 5],
    ["Postar 5 resenhas", $resenhas >= 5, $resenhas, 5],
    ["Criar 1 playlist", $playlists_criadas >= 1, $playlists_criadas, 1],
    ["Adicionar 5 conteúdos a uma playlist", $playlist_conteudos >= 5, $playlist_conteudos, 5],
    ["Marcar presença em 1 evento", $eventos >= 1, $eventos, 1],
    ["Marcar presença em 5 eventos", $eventos >= 5, $eventos, 5],
];

$total_conquistadas = count(array_filter($conquistas, fn($c) => $c[1]));

// Atualiza a coluna de conquistas no banco de dados
$update = $conn->prepare("UPDATE usuarios SET conquistas = ? WHERE id = ?");
$update->execute([$total_conquistadas, $usuario_id]);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <img src="<?= $foto_perfil ?>" class="rounded-circle me-3" width="60" height="60">
        <div>
            <h4 class="mb-0"><?= htmlspecialchars($nome) ?></h4>
            <p class="text-muted mb-0"><?= $total_conquistadas ?>/60 conquistas desbloqueadas</p>
        </div>
    </div>

    <div class="list-group">
    <?php foreach ($conquistas as [$titulo, $conquistada, $progresso, $meta]): ?>
        <?php
            $percent = $meta > 0 ? round(($progresso / $meta) * 100) : 0;
            if ($conquistada) {
                $status = '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> ' . $progresso . '/' . $meta . '</span>';
            } elseif ($progresso > 0) {
                $status = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> ' . $progresso . '/' . $meta . '</span>';
            } else {
                $status = '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> 0/' . $meta . '</span>';
            }
        ?>
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($titulo) ?>
            <?= $status ?>
        </div>
    <?php endforeach; ?>
</div>

</div>

<?php include 'includes/footer.php'; ?>
