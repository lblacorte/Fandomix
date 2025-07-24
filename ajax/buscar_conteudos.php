<?php
include '../config.php';

$q = trim(isset($_POST['q']) ? $_POST['q'] : '');
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$genero = isset($_POST['genero']) ? $_POST['genero'] : '';
$data = isset($_POST['data']) ? $_POST['data'] : '';
$avaliacao = isset($_POST['avaliacao']) ? $_POST['avaliacao'] : '';

$condicoes = [];
$params = [];

if ($q !== '') {
    $condicoes[] = "titulo LIKE :q";
    $params[':q'] = "%$q%";
}

if ($tipo !== '') {
    $condicoes[] = "tipo = :tipo";
    $params[':tipo'] = $tipo;
}

if ($genero !== '') {
    $condicoes[] = "genero LIKE :genero";
    $params[':genero'] = "%$genero%";
}

if ($data !== '') {
    $condicoes[] = "YEAR(data_lancamento) = :ano";
    $params[':ano'] = $data;
}

if ($avaliacao !== '') {
    $condicoes[] = "avaliacao_media >= :avaliacao";
    $params[':avaliacao'] = (float)$avaliacao;
}

$query = "SELECT * FROM conteudos";
if (!empty($condicoes)) {
    $query .= " WHERE " . implode(" AND ", $condicoes);
}
$query .= " ORDER BY data_lancamento DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Render cards
session_start();
$logado = isset($_SESSION["usuario_id"]);
$interacoes = [];

if ($logado) {
    $stmtInter = $conn->prepare("SELECT iu.id_conteudo, iu.lista_desejos, iu.assistido, iu.jogado, iu.favorito, a.nota
FROM interacoes_usuario iu
LEFT JOIN avaliacoes a ON a.id_conteudo = iu.id_conteudo AND a.id_usuario = iu.id_usuario
WHERE iu.id_usuario = ?");
    $stmtInter->execute([$_SESSION["usuario_id"]]);
    foreach ($stmtInter->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }
}

foreach ($resultados as $item) {
    $id = $item['id'];
    include '../includes/card_conteudo.php';
}
