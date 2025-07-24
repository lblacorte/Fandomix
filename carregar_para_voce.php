<?php
session_start();
include 'config.php';

$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$limit = 8;

$preferencias = [];
$interacoes = [];
$logado = isset($_SESSION["usuario_id"]);

if ($logado) {
    // Buscar preferências
    $stmt = $conn->prepare("SELECT generos FROM preferencias WHERE id_usuario = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    $result = $stmt->fetch();
    if ($result) $preferencias = explode(",", $result["generos"]);

    // Buscar interações + nota
    $stmt = $conn->prepare("SELECT i.id_conteudo, i.lista_desejos, i.assistido, i.jogado, i.favorito, a.nota
                            FROM interacoes_usuario i
                            LEFT JOIN avaliacoes a ON a.id_conteudo = i.id_conteudo AND a.id_usuario = i.id_usuario
                            WHERE i.id_usuario = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }
}

$sql = empty($preferencias)
    ? "SELECT * FROM conteudos ORDER BY RAND() LIMIT $limit OFFSET $offset"
    : "SELECT * FROM conteudos WHERE genero IN (" . implode(',', array_fill(0, count($preferencias), '?')) . ") ORDER BY RAND() LIMIT $limit OFFSET $offset";

$stmt = empty($preferencias) ? $conn->query($sql) : $conn->prepare($sql);
if (!empty($preferencias)) $stmt->execute($preferencias);
$conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($conteudos as $item) {
    include 'includes/card_conteudo.php';
}
?>
