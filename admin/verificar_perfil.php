<?php
// Iniciar sessão
session_start();

// Verificar permissões
$arquivos_perfil = [
    'superadmin' => realpath(__DIR__ . '/superadmin/perfil.php'),
    'revendedor' => realpath(__DIR__ . '/revendedor/perfil.php'),
    'usuario' => realpath(__DIR__ . '/usuario/perfil.php')
];

$nivel_acesso = $_SESSION['usuario_nivel'] ?? 'não definido';
$id_usuario = $_SESSION['usuario_id'] ?? 'não definido';
$nome_usuario = $_SESSION['usuario_nome'] ?? 'não definido';

$dashboard_path = '/admin/';
if ($nivel_acesso == 3) { // Super Admin
    $dashboard_path = '/admin/superadmin/';
} elseif ($nivel_acesso == 2) { // Revendedor
    $dashboard_path = '/admin/revendedor/';
} elseif ($nivel_acesso == 1) { // Usuário comum
    $dashboard_path = '/admin/usuario/';
}

// Construir caminhos absolutos
$document_root = $_SERVER['DOCUMENT_ROOT'];
$caminho_perfil_absoluto = $document_root . $dashboard_path . 'perfil.php';

// Obter informações do servidor para diagnóstico
$info_servidor = [
    'DOCUMENT_ROOT' => $document_root,
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'não definido', 
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'não definido',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'não definido',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'não definido',
];

// Verificar existência de diretórios
$diretorios = [
    'superadmin' => is_dir(__DIR__ . '/superadmin'),
    'revendedor' => is_dir(__DIR__ . '/revendedor'),
    'usuario' => is_dir(__DIR__ . '/usuario'),
];

// Preparar resultados para exibição
$resultados = [
    'Sessão' => [
        'ID de Sessão' => session_id(),
        'Status da Sessão' => (session_status() == PHP_SESSION_ACTIVE) ? 'Ativa' : 'Inativa',
        'Dados do Usuário' => [
            'ID' => $id_usuario,
            'Nome' => $nome_usuario,
            'Nível de Acesso' => $nivel_acesso,
        ],
    ],
    'Caminhos' => [
        'Dashboard Path' => $dashboard_path,
        'Caminho do Perfil' => $dashboard_path . 'perfil.php',
        'Caminho Absoluto do Perfil' => $caminho_perfil_absoluto,
        'Arquivo Existe?' => file_exists($caminho_perfil_absoluto) ? 'SIM' : 'NÃO',
    ],
    'Arquivos de Perfil' => [
        'superadmin/perfil.php' => [
            'Caminho' => $arquivos_perfil['superadmin'] ?: 'não encontrado',
            'Existe?' => file_exists($arquivos_perfil['superadmin']) ? 'SIM' : 'NÃO',
            'Tamanho' => file_exists($arquivos_perfil['superadmin']) ? filesize($arquivos_perfil['superadmin']) . ' bytes' : 'N/A',
        ],
        'revendedor/perfil.php' => [
            'Caminho' => $arquivos_perfil['revendedor'] ?: 'não encontrado',
            'Existe?' => file_exists($arquivos_perfil['revendedor']) ? 'SIM' : 'NÃO',
            'Tamanho' => file_exists($arquivos_perfil['revendedor']) ? filesize($arquivos_perfil['revendedor']) . ' bytes' : 'N/A',
        ],
        'usuario/perfil.php' => [
            'Caminho' => $arquivos_perfil['usuario'] ?: 'não encontrado',
            'Existe?' => file_exists($arquivos_perfil['usuario']) ? 'SIM' : 'NÃO',
            'Tamanho' => file_exists($arquivos_perfil['usuario']) ? filesize($arquivos_perfil['usuario']) . ' bytes' : 'N/A',
        ],
    ],
    'Diretórios' => $diretorios,
    'Servidor' => $info_servidor,
];

// Verificar permissões de arquivo
foreach ($arquivos_perfil as $tipo => $arquivo) {
    if (file_exists($arquivo)) {
        $resultados['Arquivos de Perfil'][$tipo . '/perfil.php']['Permissões'] = substr(sprintf('%o', fileperms($arquivo)), -4);
    }
}

// Definir cabeçalho para exibir como JSON
header('Content-Type: application/json');
echo json_encode($resultados, JSON_PRETTY_PRINT);
?> 