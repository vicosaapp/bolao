<?php
// Verificar se a sessão já está ativa antes de iniciar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Função para verificar o nível de acesso do usuário
function hasAccess($requiredLevel) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Níveis de acesso: 1 = usuário normal, 2 = revendedor, 3 = superadmin
    $userLevel = $_SESSION['usuario_nivel'] ?? 1;
    
    return $userLevel >= $requiredLevel;
}

// Função para redirecionar baseado no nível de acesso
function redirectBasedOnAccess() {
    if (!isLoggedIn()) {
        header("Location: ../login.php?redirect=admin");
        exit;
    }
    
    $userLevel = $_SESSION['usuario_nivel'] ?? 1;
    
    if ($userLevel == 3) {
        header("Location: superadmin/");
        exit;
    } elseif ($userLevel == 2) {
        header("Location: revendedor/");
        exit;
    } else {
        header("Location: usuario/");
        exit;
    }
}

// Função para verificar e redirecionar se não tiver acesso
function requireAccess($requiredLevel) {
    if (!hasAccess($requiredLevel)) {
        header("Location: ../login.php?redirect=admin&access_denied=1");
        exit;
    }
} 