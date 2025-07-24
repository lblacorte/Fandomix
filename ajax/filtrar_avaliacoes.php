<?php
session_start();
include '../config.php';

if (!isset($_SESSION['usuario_id'])) {
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$nota = isset($_POST['nota']) ? (int)$_POST['nota'] : 0;
$data = isset($_POST['data']) ? $_POST['data'] : '';

$sql = "
    SELECT c.*, a.nota, a.resenha, a.data_avaliacao
    FROM avaliacoes a
    JOIN conteudos c ON c.id = a.id_conteudo
    WHERE a.id_usuario = :usuario_id
";

$params = ['usuario_id' => $usuario_id];

if (!empty($titulo)) {
    $sql .= " AND (LOWER(c.titulo) LIKE :busca OR LOWER(c.genero) LIKE :busca)";
    $params['busca'] = '%' . strtolower($titulo) . '%';
}

if (!empty($nota)) {
    $sql .= " AND a.nota >= :nota";
    $params['nota'] = $nota;
}

if (!empty($data)) {
    $sql .= " AND DATE(a.data_avaliacao) = :data";
    $params['data'] = $data;
}

$sql .= " ORDER BY a.data_avaliacao DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($avaliacoes as $item): ?>
    <div class="col">
        <div class="card h-100 shadow-sm">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="assets/images/posters/<?= $item['imagem'] ?>" class="img-fluid rounded-start" alt="<?= htmlspecialchars($item['titulo']) ?>" style="height: 100%; object-fit: cover;">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($item['titulo']) ?></h5>
                        <p class="mb-1 text-muted">
                            Nota: 
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= $i <= $item['nota'] ? 'bi-star-fill text-warning' : 'bi-star' ?>"></i>
                            <?php endfor; ?>
                            (<?= $item['nota'] ?>/5)
                        </p>
                        <p class="text-muted" style="font-size: 0.85rem;">
                            Avaliado em: <?= date("d/m/Y H:i", strtotime($item['data_avaliacao'])) ?>
                        </p>
                        <?php if (!empty(trim($item['resenha']))): ?>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($item['resenha'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted fst-italic">Sem resenha escrita.</p>
                        <?php endif; ?>
                        <a href="avaliacao.php?id=<?= $item['id'] ?>&nota=<?= $item['nota'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                            Editar Avaliação
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
