<?php
// Funções auxiliares para o sistema

/**
 * Verifica se o usuário está logado
 * Redireciona para a página de login se não estiver autenticado
 */
function verificarLogin() {
    // Inicia a sessão se ainda não estiver ativa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verifica se o usuário está logado
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        // Redireciona para a página de login
        header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Verifica se o usuário tem permissão para acessar a página
 * @param int $nivelRequerido Nível mínimo de permissão necessário
 */
function verificarPermissao($nivelRequerido) {
    // Inicia a sessão se ainda não estiver ativa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verifica se o nível de permissão do usuário é suficiente
    if (!isset($_SESSION['nivel_acesso']) || $_SESSION['nivel_acesso'] < $nivelRequerido) {
        // Redireciona para a página de acesso negado
        header('Location: ../acesso-negado.php');
        exit;
    }
}

/**
 * Formata uma data para o formato brasileiro
 * @param string $data Data no formato Y-m-d ou Y-m-d H:i:s
 * @param bool $incluirHora Se deve incluir as horas na formatação
 * @return string Data formatada
 */
function formatar_data($data, $incluirHora = false) {
    if (empty($data)) {
        return '';
    }
    
    $timestamp = strtotime($data);
    
    if ($incluirHora) {
        return date('d/m/Y H:i', $timestamp);
    } else {
        return date('d/m/Y', $timestamp);
    }
}

// Outras funções existentes... 