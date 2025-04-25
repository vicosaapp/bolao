<?php
// Iniciar sessão
session_start();

// Incluir arquivo de autenticação
require_once 'auth.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login.php?redirect=admin');
    exit;
}

// Obter nível de acesso do usuário
$nivel = $_SESSION['usuario_nivel'] ?? 1;

// Redirecionar para o painel apropriado
if ($nivel == 3) {
    header('Location: superadmin/');
    exit;
} elseif ($nivel == 2) {
    header('Location: revendedor/');
    exit;
} else {
    header('Location: usuario/');
    exit;
}
?> 