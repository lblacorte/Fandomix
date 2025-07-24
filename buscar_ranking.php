<?php
session_start();
include 'config.php';

$logado = isset($_SESSION['usuario_id']);
$usuario_id = $logado ? $_SESSION['usuario_id'] : null;
$foto_perfil = $logado ? "uploads/" . ($_SESSION["foto"] ?? "default.png") : null;
$tipo = $_POST['tipo'] ?? '';
$genero = $_POST['genero'] ?? '';
$criterio = $_POST['criterio'] ?? 'avaliacao';

$where = [];
$params = [];

if (!empty($tipo)) {
    $where[] = "c.tipo = ?";
    $params[] = $tipo;
}

if (!empty($genero)) {
    $where[] = "c.genero = ?";
    $params[] = $genero;
}

$whereSQL = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$orderBy = ($criterio === 'acessos') ? 'c.acessos DESC' : 'media DESC';

$sql = "
    SELECT c.*, AVG(a.nota) as media, COUNT(a.nota) as total
    FROM conteudos c
    LEFT JOIN avaliacoes a ON c.id = a.id_conteudo
    $whereSQL
    GROUP BY c.id
    ORDER BY $orderBy
    LIMIT 30
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Interações
$interacoes = [];
if (isset($_SESSION["usuario_id"])) {
    include 'funcoes/get_interacoes.php';
    $interacoes = getInteracoesUsuario($conn, $_SESSION["usuario_id"]);
}

$posicao = 1;
foreach ($conteudos as $item) {
    echo '<div class="col">';

    // Estilo e ícone especial para top 3
    $classe = 'text-primary';
    $icone = '';

    if ($posicao == 1) {
        $classe = 'text-warning';
        $icone = '🥇';
    } elseif ($posicao == 2) {
        $classe = 'text-secondary';
        $icone = '🥈';
    } elseif ($posicao == 3) {
        $classe = 'text-orange';
        $icone = '🥉';
    }

    echo '<div class="text-center fw-bold ' . $classe . '" style="font-size: 1.3rem; margin-bottom: -0.5rem;">' .
        $icone . ' #' . $posicao .
        '</div>';

    include 'includes/card_conteudo.php';

    echo '</div>';
    $posicao++;
}

?>