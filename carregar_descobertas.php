<?php
session_start();
include 'config.php';

$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$limit = 16;

$logado = isset($_SESSION["usuario_id"]);
$preferencias = [];

if ($logado) {
    $stmt = $conn->prepare("SELECT generos FROM preferencias WHERE id_usuario = ?");
    $stmt->execute([$_SESSION["usuario_id"]]);
    $result = $stmt->fetch();
    if ($result && !empty($result["generos"])) {
        $preferencias = explode(",", $result["generos"]);
    }
}

function buscarDescobertas($conn, $preferencias, $offset, $limit) {
    if (count($preferencias) < 10 && !empty($preferencias)) {
        $placeholders = implode(',', array_fill(0, count($preferencias), '?'));
        $sql = "SELECT * FROM conteudos WHERE genero NOT IN ($placeholders) ORDER BY acessos DESC LIMIT $limit OFFSET $offset";
        $stmt = $conn->prepare($sql);
        $stmt->execute($preferencias);
    } else {
        $sql = "SELECT * FROM conteudos ORDER BY acessos DESC LIMIT $limit OFFSET $offset";
        $stmt = $conn->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$interacoes = $logado ? getInteracoesUsuario($conn, $_SESSION["usuario_id"]) : [];
$conteudos = buscarDescobertas($conn, $preferencias, $offset, $limit);

function getInteracoesUsuario($conn, $id_usuario) {
    $sql = "SELECT iu.id_conteudo, iu.lista_desejos, iu.assistido, iu.jogado, iu.favorito, a.nota
        FROM interacoes_usuario iu
        LEFT JOIN avaliacoes a ON a.id_conteudo = iu.id_conteudo AND a.id_usuario = iu.id_usuario
        WHERE iu.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_usuario]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $interacoes = [];
    foreach ($dados as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }
    return $interacoes;
}

foreach ($conteudos as $item) {
    include 'includes/card_conteudo.php';
}
