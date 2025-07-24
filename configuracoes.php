<?php
session_start();
include 'config.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$logado = isset($_SESSION["usuario_id"]);

$id_usuario = $_SESSION["usuario_id"];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM preferencias WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$preferencias = $stmt->fetch(PDO::FETCH_ASSOC);

$generos_atuais = isset($preferencias['generos']) ? explode(',', $preferencias['generos']) : [];
$tags = isset($preferencias['tags']) ? $preferencias['tags'] : '';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-4">
    <h2>Configurações da Conta</h2>
    <form id="form-config" method="post" enctype="multipart/form-data" action="salvar_configuracoes.php" class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($usuario['nome']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Nome de Usuário</label>
            <input type="text" name="usuario" class="form-control" required value="<?= htmlspecialchars($usuario['usuario']) ?>">
            <div class="form-text text-danger" id="erro-usuario"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($usuario['email']) ?>">
            <div class="form-text text-danger" id="erro-email"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Data de Nascimento</label>
            <input type="date" name="data_nasc" class="form-control" value="<?= $usuario['data_nasc'] ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Tags de Interesse (separadas por vírgula)</label>
            <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($tags) ?>">
        </div>
        <div class="col-md-6">
    <label class="form-label">Gêneros de Interesse</label>
    <div class="d-flex flex-wrap gap-2">
        <?php
        $opcoes = ['Ação', 'Aventura', 'Comédia', 'Drama', 'Fantasia', 'Terror', 'Ficção Científica', 'Suspense', 'Romance', 'Animação'];
        foreach ($opcoes as $g):
            $checked = in_array($g, $generos_atuais) ? 'checked' : '';
        ?>
            <div class="form-check me-2">
                <input class="form-check-input" type="checkbox" name="generos[]" value="<?= $g ?>" id="gen-<?= $g ?>" <?= $checked ?>>
                <label class="form-check-label" for="gen-<?= $g ?>"><?= $g ?></label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
        <div class="col-md-6">
            <label class="form-label">Foto de Perfil</label><br>
            <img src="uploads/<?= isset($_SESSION['foto']) ? $_SESSION['foto'] : 'default.png' ?>" alt="Atual" style="height:80px;" class="mb-2 d-block">
            <input type="file" name="foto" class="form-control">
        </div>
        <div class="col-12">
            <button id="btn-salvar" class="btn btn-primary" type="submit">Salvar Alterações</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const idUsuario = <?= json_encode($_SESSION['usuario_id']) ?>;

    const inputUsuario = document.querySelector('input[name="usuario"]');
    const inputEmail = document.querySelector('input[name="email"]');
    const btnSalvar = document.getElementById('btn-salvar');

    const spanUsuario = document.createElement("small");
    const spanEmail = document.createElement("small");

    spanUsuario.classList.add("form-text");
    spanEmail.classList.add("form-text");

    inputUsuario.parentNode.appendChild(spanUsuario);
    inputEmail.parentNode.appendChild(spanEmail);

    let usuarioValido = true;
    let emailValido = true;

    function atualizarBotao() {
        btnSalvar.disabled = !(usuarioValido && emailValido);
    }

    inputUsuario.addEventListener("input", () => {
        const valor = inputUsuario.value.trim();
        if (valor.length < 3) {
            spanUsuario.textContent = "Nome de usuário muito curto.";
            spanUsuario.className = "form-text text-danger";
            usuarioValido = false;
            atualizarBotao();
            return;
        }

        fetch(`validar_usuario.php?usuario=${encodeURIComponent(valor)}&id=${idUsuario}`)
            .then(res => res.text())
            .then(resp => {
                if (resp === "existe") {
                    spanUsuario.textContent = "Este nome de usuário já está em uso.";
                    spanUsuario.className = "form-text text-danger";
                    usuarioValido = false;
                } else {
                    spanUsuario.textContent = "Disponível!";
                    spanUsuario.className = "form-text text-success";
                    usuarioValido = true;
                }
                atualizarBotao();
            });
    });

    inputEmail.addEventListener("input", () => {
        const valor = inputEmail.value.trim();
        if (!valor.includes("@") || !valor.includes(".")) {
            spanEmail.textContent = "Email inválido.";
            spanEmail.className = "form-text text-danger";
            emailValido = false;
            atualizarBotao();
            return;
        }

        fetch(`validar_email.php?email=${encodeURIComponent(valor)}&id=${idUsuario}`)
            .then(res => res.text())
            .then(resp => {
                if (resp === "existe") {
                    spanEmail.textContent = "Este email já está cadastrado.";
                    spanEmail.className = "form-text text-danger";
                    emailValido = false;
                } else {
                    spanEmail.textContent = "Disponível!";
                    spanEmail.className = "form-text text-success";
                    emailValido = true;
                }
                atualizarBotao();
            });
    });

    atualizarBotao(); // Inicializa o estado do botão
});
</script>

