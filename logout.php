<?php
session_start();
session_unset();     // Limpa todas as variáveis da sessão
session_destroy();   // Destrói a sessão

header("Location: dashboard.php");
exit;
