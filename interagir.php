<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) exit;

$id_usuario = $_SESSION["usuario_id"];
$id_conteudo = $_POST["id_conteudo"];
$tipo_acao = $_POST["tipo"]; // "desejo" ou "consumo"

// Descobre se é jogo, filme ou série
$stmt = $conn->prepare("SELECT tipo FROM conteudos WHERE id = ?");
$stmt->execute([$id_conteudo]);
$conteudo = $stmt->fetch();

if (!$conteudo) exit;

$tipo_conteudo = $conteudo['tipo'];
$is_jogo = $tipo_conteudo === 'jogo';

// Verifica se já existe interação
$stmt = $conn->prepare("SELECT * FROM interacoes_usuario WHERE id_usuario = ? AND id_conteudo = ?");
$stmt->execute([$id_usuario, $id_conteudo]);
$existe = $stmt->fetch();

if ($existe) {
    // Alternar o campo certo
    if ($tipo_acao === "desejo") {
        $novo_valor = !$existe['lista_desejos'];
        $stmt = $conn->prepare("UPDATE interacoes_usuario SET lista_desejos = ? WHERE id_usuario = ? AND id_conteudo = ?");
        $stmt->execute([$novo_valor, $id_usuario, $id_conteudo]);
    } else {
        if ($is_jogo) {
            $novo_valor = !$existe['jogado'];
            $stmt = $conn->prepare("UPDATE interacoes_usuario SET jogado = ? WHERE id_usuario = ? AND id_conteudo = ?");
            $stmt->execute([$novo_valor, $id_usuario, $id_conteudo]);
        } else {
            $novo_valor = !$existe['assistido'];
            $stmt = $conn->prepare("UPDATE interacoes_usuario SET assistido = ? WHERE id_usuario = ? AND id_conteudo = ?");
            $stmt->execute([$novo_valor, $id_usuario, $id_conteudo]);
        }
    }
} else {
    // Primeira vez? Insere com o valor correto
    $stmt = $conn->prepare("INSERT INTO interacoes_usuario (
        id_usuario, id_conteudo, lista_desejos, assistido, jogado
    ) VALUES (?, ?, ?, ?, ?)");

    $stmt->execute([
        $id_usuario,
        $id_conteudo,
        $tipo_acao === 'desejo' ? 1 : 0,
        ($tipo_acao === 'consumo' && !$is_jogo) ? 1 : 0,
        ($tipo_acao === 'consumo' && $is_jogo) ? 1 : 0
    ]);
}
