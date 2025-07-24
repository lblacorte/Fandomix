<?php
$foto_perfil = $logado ? "/fandomix/uploads/" . ($_SESSION["foto"] ?? "default.png") : null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="/fandomix/dashboard.php">
            <img src="/fandomix/assets/images/logo.png" alt="Fandomix" width="200">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-between" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="interacoesDropdown" role="button" data-bs-toggle="dropdown">
                        Interações
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/fandomix/match.php">Match</a></li>
                        <li><a class="dropdown-item" href="/fandomix/favoritos.php">Favoritos</a></li>
                        <li><a class="dropdown-item" href="/fandomix/avaliacoes.php">Avaliações</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="comunidadeDropdown" role="button" data-bs-toggle="dropdown">
                        Comunidade
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/fandomix/grupos.php">Grupos</a></li>
                        <li><a class="dropdown-item" href="/fandomix/eventos.php">Eventos</a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="/fandomix/descobrir.php">Explorar</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="conteudoDropdown" role="button" data-bs-toggle="dropdown">
                        Conteúdo
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/fandomix/conquistas.php">Conquistas</a></li>
                        <li><a class="dropdown-item" href="/fandomix/rankings.php">Rankings</a></li>
                        <li><a class="dropdown-item" href="/fandomix/playlists.php">Playlists</a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="/fandomix/pesquisa.php">Pesquisa</a></li>
            </ul>

            <ul class="navbar-nav">
                <?php if ($logado): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $foto_perfil ?>" alt="Perfil" class="rounded-circle me-2" width="40" height="40">
                            <span>@<?= $_SESSION["usuario"] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/fandomix/perfil.php">Perfil</a></li>
                            <li><a class="dropdown-item" href="/fandomix/configuracoes.php">Configurações</a></li>
                            <li><a class="dropdown-item" href="/fandomix/historico_mensagens.php">Mensagens</a></li>
                            <li><a class="dropdown-item" href="/fandomix/convites.php">Convites</a></li>
                            <li><a class="dropdown-item" href="/fandomix/solicitacoes.php">Solicitações</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/fandomix/logout.php">Sair</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/fandomix/login.php" class="btn btn-outline-primary">Entrar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Espaço para compensar navbar fixa -->
<div style="padding-top: 80px;"></div>
