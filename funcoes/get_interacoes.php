<?php
function getInteracoesUsuario($conn, $id_usuario) {
    $sql = "
        SELECT iu.id_conteudo, 
               iu.lista_desejos, 
               iu.assistido, 
               iu.jogado, 
               iu.favorito, 
               a.nota
        FROM interacoes_usuario iu
        LEFT JOIN avaliacoes a 
            ON a.id_conteudo = iu.id_conteudo 
            AND a.id_usuario = iu.id_usuario
        WHERE iu.id_usuario = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_usuario]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $interacoes = [];
    foreach ($dados as $row) {
        $interacoes[$row['id_conteudo']] = $row;
    }

    return $interacoes;
}
