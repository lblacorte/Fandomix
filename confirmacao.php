<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-4 text-center">
                <img src="assets/images/logo.png" width="100" class="mb-3">
                <h3>Conta criada com sucesso!</h3>
                <p class="text-muted">Agora envie uma foto de perfil (opcional):</p>

                <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </form>

                <div class="text-muted mt-3">
                    <p><a href="login.php">Pular e ir para o login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
