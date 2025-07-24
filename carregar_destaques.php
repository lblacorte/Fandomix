<?php
session_start();
include 'config.php';

$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$limit = 8;

$logado = isset($_SESSION["usuario_id"]);
$interacoes = [];

if ($logado) {
    $stmt = $conn->prepare("SELECT iu.id_conteudo, iu.lista_desejos, iu.assistido, iu.jogado, iu.favorito, a.nota
FROM interacoes_usuario iu
LEFT JOIN avaliacoes a ON a.id_conteudo = iu.id_conteudo AND a.id_usuario = iu.id_usuario
WHERE iu.id_usuario = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }
}

$sql = "SELECT * FROM conteudos ORDER BY acessos DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute();
$conteudos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($conteudos as $item) {
    include 'includes/card_conteudo.php';
}
?>
