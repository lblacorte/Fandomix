<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: cadastro.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$_SESSION["usuario"] = isset($_SESSION["usuario"]) ? $_SESSION["usuario"] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $generos = isset($_POST["generos"]) ? $_POST["generos"] : [];
    $tags = isset($_POST["tags"]) ? $_POST["tags"] : "";

    $generos_serializados = implode(",", $generos);

    $sql = "INSERT INTO preferencias (id_usuario, generos, tags) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario_id, $generos_serializados, $tags]);

    header("Location: confirmacao.php");
    exit;
}
?>

<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow p-4">
                <div class="text-center mb-4">
                    <img src="assets/images/logo.png" alt="Fandomix" width="140">
                    <h3 class="mt-3">Personalize sua experiência</h3>
                    <p class="text-muted">Escolha seus gêneros e interesses favoritos</p>
                </div>

                <form action="preferencias.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Gêneros favoritos</label>
                        <div class="row">
                            <?php
                            $generos = ['Ação', 'Aventura', 'Comédia', 'Drama', 'Fantasia', 'Terror', 'Ficção Científica', 'Romance', 'Suspense', 'Animação'];
                            foreach ($generos as $genero) {
                                echo "<div class='col-md-4'><div class='form-check'>
                                    <input class='form-check-input' type='checkbox' name='generos[]' value='{$genero}' id='{$genero}'>
                                    <label class='form-check-label' for='{$genero}'>{$genero}</label>
                                    </div></div>";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Tags de interesse</label>
                        <input type="text" class="form-control" name="tags" placeholder="Ex: zumbis, heróis, anime...">
                        <small class="text-muted">Separe por vírgulas</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Finalizar Cadastro</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="cadastro.php">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
