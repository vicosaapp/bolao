<?php
// Iniciar sessão
session_start();

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Redirecionar para o dashboard
header('Location: dashboard.php');
exit;
?> 