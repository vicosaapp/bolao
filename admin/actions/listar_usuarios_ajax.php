<?php
include_once '../../includes/config.php';
include_once '../auth.php';

// Verificar permissão
requireAccess(3); // Nível 3 = Administrador

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

try {
    // Parâmetros de paginação
    $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
    $itensPorPagina = isset($_GET['itensPorPagina']) ? intval($_GET['itensPorPagina']) : 10;
    $offset = ($pagina - 1) * $itensPorPagina;
    
    // Parâmetros de filtro
    $filtroStatus = isset($_GET['status']) ? $_GET['status'] : '';
    $filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
    $busca = isset($_GET['busca']) ? $_GET['busca'] : '';
    
    // Construir a consulta SQL com os filtros
    $sqlWhere = [];
    $params = [];
    
    if ($filtroStatus !== '') {
        $ativo = $filtroStatus == '1' ? 1 : 0;
        $sqlWhere[] = "ativo = :ativo";
        $params[':ativo'] = $ativo;
    }
    
    if ($filtroTipo !== '') {
        $sqlWhere[] = "nivel_acesso = :nivel";
        $params[':nivel'] = intval($filtroTipo);
    }
    
    if ($busca !== '') {
        $busca = '%' . $busca . '%';
        $sqlWhere[] = "(nome LIKE :busca OR email LIKE :busca OR id = :buscaId)";
        $params[':busca'] = $busca;
        $params[':buscaId'] = is_numeric($busca) ? str_replace('%', '', $busca) : 0;
    }
    
    $whereClause = count($sqlWhere) > 0 ? 'WHERE ' . implode(' AND ', $sqlWhere) : '';
    
    // Consulta para contagem total de registros filtrados
    $sqlCount = "SELECT COUNT(*) as total FROM usuarios $whereClause";
    $stmtCount = $pdo->prepare($sqlCount);
    foreach ($params as $key => $value) {
        $stmtCount->bindValue($key, $value);
    }
    $stmtCount->execute();
    $totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Cálculo do número total de páginas
    $totalPaginas = ceil($totalRegistros / $itensPorPagina);
    
    // Consulta principal para obter os usuários
    $sql = "SELECT id, nome, email, nivel_acesso, ativo, data_cadastro, ultimo_acesso 
            FROM usuarios 
            $whereClause 
            ORDER BY id DESC 
            LIMIT :offset, :limit";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados dos usuários para a resposta
    $usuariosFormatados = [];
    foreach ($usuarios as $usuario) {
        $nivel = '';
        switch($usuario['nivel_acesso']) {
            case 1:
                $nivel = ['label' => 'Usuário', 'class' => 'info'];
                break;
            case 2:
                $nivel = ['label' => 'Operador', 'class' => 'primary'];
                break;
            case 3:
                $nivel = ['label' => 'Administrador', 'class' => 'warning'];
                break;
            case 4:
                $nivel = ['label' => 'Super Admin', 'class' => 'danger'];
                break;
            default:
                $nivel = ['label' => 'Desconhecido', 'class' => 'secondary'];
        }
        
        $status = $usuario['ativo'] == 1 ? 
            ['label' => 'Ativo', 'class' => 'success'] : 
            ['label' => 'Inativo', 'class' => 'danger'];
        
        $dataCadastro = date('d/m/Y', strtotime($usuario['data_cadastro']));
        $ultimoAcesso = $usuario['ultimo_acesso'] ? 
            date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 
            'Nunca acessou';
        
        $usuariosFormatados[] = [
            'id' => $usuario['id'],
            'nome' => htmlspecialchars($usuario['nome']),
            'email' => htmlspecialchars($usuario['email']),
            'nivel' => $nivel,
            'status' => $status,
            'data_cadastro' => $dataCadastro,
            'ultimo_acesso' => $ultimoAcesso,
            'pode_excluir' => ($_SESSION['user_id'] != $usuario['id'])
        ];
    }
    
    // Preparar a resposta
    $resposta = [
        'status' => 'success',
        'usuarios' => $usuariosFormatados,
        'paginacao' => [
            'pagina_atual' => $pagina,
            'itens_por_pagina' => $itensPorPagina,
            'total_registros' => $totalRegistros,
            'total_paginas' => $totalPaginas
        ]
    ];
    
    echo json_encode($resposta);
    
} catch (Exception $e) {
    $resposta = [
        'status' => 'error',
        'message' => 'Erro ao buscar usuários: ' . $e->getMessage()
    ];
    
    http_response_code(500);
    echo json_encode($resposta);
} 