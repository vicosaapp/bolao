<?php
// Iniciar sessão
session_start();

// Informações de configuração de sessão
$config = [
    'session.save_path' => session_save_path(),
    'session.save_handler' => ini_get('session.save_handler'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
];

// Informações da sessão atual
$sessao = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'variaveis_sessao' => $_SESSION,
    'cookies_sessao' => $_COOKIE,
];

// Exibir informações
header('Content-Type: application/json');
echo json_encode([
    'configuracao' => $config,
    'sessao' => $sessao
], JSON_PRETTY_PRINT);
exit(); 