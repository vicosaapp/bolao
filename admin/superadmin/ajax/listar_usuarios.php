<?php
// Incluir arquivos necessários
require_once '../../../config.php';
require_once '../../../includes/Database.php';
require_once '../../models/UsuarioModel.php';
require_once '../../inc/auth.php';

// Verificar permissão (mínimo nível 3 - admin)
requireAccess(3);

// Coletar parâmetros
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$busca = isset($_GET['busca']) ? $_GET['busca'] : null;

// Resposta padrão
$resposta = [
    'status' => false,
    'mensagem' => 'Erro ao listar usuários',
    'usuarios' => [],
    'total' => 0,
    'paginas' => 0
];

try {
    // Instanciar o modelo de usuário
    $usuarioModel = new UsuarioModel();
    
    // Buscar usuários com filtros e paginação
    $usuarios = $usuarioModel->listarUsuariosComFiltros($pagina, $porPagina, $status, $tipo, $busca);
    $total = $usuarioModel->contarUsuariosComFiltros($status, $tipo, $busca);
    
    // Calcular número de páginas
    $paginas = ceil($total / $porPagina);
    
    // Limpar dados sensíveis e formatar datas
    foreach ($usuarios as &$usuario) {
        // Remover senha e outros dados sensíveis
        unset($usuario['senha']);
        
        // Formatar data de cadastro
        if (isset($usuario['data_cadastro']) && $usuario['data_cadastro']) {
            $usuario['data_cadastro_formatada'] = date('d/m/Y H:i', strtotime($usuario['data_cadastro']));
        } else {
            $usuario['data_cadastro_formatada'] = '-';
        }
        
        // Formatar último acesso
        if (isset($usuario['ultimo_acesso']) && $usuario['ultimo_acesso']) {
            $usuario['ultimo_acesso_formatado'] = date('d/m/Y H:i', strtotime($usuario['ultimo_acesso']));
        } else {
            $usuario['ultimo_acesso_formatado'] = '-';
        }
        
        // Formatar status
        $usuario['status_texto'] = $usuario['status'] == 1 ? 'Ativo' : 'Inativo';
        
        // Formatar tipo
        switch ($usuario['tipo']) {
            case 1:
                $usuario['tipo_texto'] = 'Usuário';
                break;
            case 2:
                $usuario['tipo_texto'] = 'Operador';
                break;
            case 3:
                $usuario['tipo_texto'] = 'Administrador';
                break;
            case 4:
                $usuario['tipo_texto'] = 'Super Admin';
                break;
            default:
                $usuario['tipo_texto'] = 'Desconhecido';
        }
    }
    
    // Preparar resposta de sucesso
    $resposta = [
        'status' => true,
        'mensagem' => 'Usuários listados com sucesso',
        'usuarios' => $usuarios,
        'total' => $total,
        'paginas' => $paginas,
        'pagina_atual' => $pagina
    ];
} catch (Exception $e) {
    // Registrar erro no log
    error_log('[AJAX Listar Usuários] ' . $e->getMessage());
    $resposta['mensagem'] = 'Erro ao processar a requisição: ' . $e->getMessage();
}

// Enviar resposta JSON
header('Content-Type: application/json');
echo json_encode($resposta); 