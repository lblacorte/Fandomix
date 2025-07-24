
<?php
session_start();
include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['usuario'] = $usuario['usuario']; // <-- adiciona o @usuario
        $_SESSION['foto'] = isset($usuario['foto']) ? $usuario['foto'] : null; // opcional, se usar foto de perfil
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos!";
    }
}
include 'includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-4">
                <div class="text-center mb-4">
                    <img src="assets/images/logo.png" alt="Fandomix" width="140">
                    <h3 class="mt-3">Bem-vindo à Fandomix</h3>
                    <p class="text-muted">Entre ou crie sua conta para começar</p>
                </div>
                <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?= $erro ?></div>
                <?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
                <div class="text-center mb-2">
                    <a href="#">Esqueceu a senha?</a>
                </div>
                <hr>
                <div class="text-center">
                    <a href="#" class="btn btn-outline-danger mb-2 w-100">Entrar com Google</a>
                    <a href="#" class="btn btn-outline-primary w-100">Entrar com Facebook</a>
                </div>
                <div class="text-center mt-3">
                    <p>Não tem uma conta? <a href="cadastro.php">Crie aqui</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
