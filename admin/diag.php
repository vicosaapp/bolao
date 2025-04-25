<?php
// Iniciar sessão
session_start();

// Função para verificar se um arquivo existe
function fileExistsCheck($path) {
    $exists = file_exists($path);
    return [
        'path' => $path,
        'exists' => $exists,
        'readable' => $exists ? is_readable($path) : false,
    ];
}

// Função para obter o caminho absoluto
function getAbsolutePath($relativePath) {
    // Obtém o diretório base da aplicação
    $baseDir = __DIR__;
    // Remove 'admin' do caminho base para obter o diretório raiz
    $rootDir = dirname($baseDir);
    // Combina com o caminho relativo
    return realpath($rootDir . '/' . ltrim($relativePath, '/'));
}

// Informações de sessão
$sessionInfo = [
    'session_started' => (session_status() == PHP_SESSION_ACTIVE),
    'session_id' => session_id(),
    'usuario_id' => $_SESSION['usuario_id'] ?? 'não definido',
    'usuario_nome' => $_SESSION['usuario_nome'] ?? 'não definido',
    'usuario_email' => $_SESSION['usuario_email'] ?? 'não definido',
    'usuario_nivel' => $_SESSION['usuario_nivel'] ?? 'não definido'
];

// Verificação de arquivos críticos
$filesCheck = [
    fileExistsCheck(__DIR__ . '/templates/header.php'),
    fileExistsCheck(__DIR__ . '/templates/footer.php'),
    fileExistsCheck(__DIR__ . '/assets/css/admin.css'),
    fileExistsCheck(__DIR__ . '/assets/js/admin.js'),
    fileExistsCheck(__DIR__ . '/../assets/img/logo.png'),
    fileExistsCheck(__DIR__ . '/../assets/img/user-avatar.png')
];

// Informações do servidor
$serverInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'desconhecido',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'desconhecido',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'desconhecido',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'desconhecido'
];

// Diretórios atuais
$directoryInfo = [
    'current_dir' => __DIR__,
    'parent_dir' => dirname(__DIR__),
    'absolute_path' => realpath(__DIR__),
];

// Verificação de inclusão
ob_start();
include __DIR__ . '/templates/header.php';
$headerOutput = ob_get_clean();
$headerSuccess = !empty($headerOutput);

ob_start();
include __DIR__ . '/templates/footer.php';
$footerOutput = ob_get_clean();
$footerSuccess = !empty($footerOutput);

// Saída em JSON para fácil visualização
header('Content-Type: application/json');
echo json_encode([
    'session' => $sessionInfo,
    'files' => $filesCheck,
    'server' => $serverInfo,
    'directories' => $directoryInfo,
    'includes' => [
        'header_success' => $headerSuccess,
        'footer_success' => $footerSuccess
    ]
], JSON_PRETTY_PRINT);
?> 