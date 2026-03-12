<?php
session_start();

// Limpa toda a sessão
$_SESSION = array();

// Destrói a sessão no servidor
session_destroy();

// Redireciona para o login com uma mensagem de confirmação (opcional)
header("Location: login.php?msg=saiu");
exit;