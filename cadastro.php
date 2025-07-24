<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();
include 'config.php';

$erro = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $data_nasc = $_POST["data_nasc"];
    $usuario = $_POST["usuario"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $senha_confirma = $_POST["senha_confirma"];

    if ($senha !== $senha_confirma) {
        $erro = "As senhas não coincidem.";
    } else {
        // Verificar se já existe usuário com mesmo e-mail ou nome de usuário
        $sql = "SELECT id FROM usuarios WHERE email = ? OR usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email, $usuario]);

        if ($stmt->rowCount() > 0) {
            $erro = "E-mail ou nome de usuário já cadastrados.";
        } else {
            // Criptografar senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO usuarios (nome, data_nasc, usuario, email, senha) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nome, $data_nasc, $usuario, $email, $senha_hash]);
            
                $_SESSION["usuario_id"] = $conn->lastInsertId();
                header("Location: preferencias.php");
                exit;
            } catch (PDOException $e) {
                die("Erro ao cadastrar: " . $e->getMessage());
            }            
        }
    }
}

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
?>

<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow p-4">
                <div class="text-center mb-3">
                    <img src="assets/images/logo.png" alt="Fandomix" width="140">
                    <h3 class="mt-3">Criar conta na Fandomix</h3>
                    <p class="text-muted">Preencha os dados abaixo para começar</p>
                </div>

                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= $erro ?></div>
                <?php endif; ?>

                <form action="cadastro.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de nascimento</label>
                        <input type="date" class="form-control" name="data_nasc" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nome de usuário</label>
                        <input type="text" class="form-control" name="usuario" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" class="form-control" name="senha" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirme sua senha</label>
                        <input type="password" class="form-control" name="senha_confirma" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Criar conta</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p>Já tem conta? <a href="login.php">Entre aqui</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
