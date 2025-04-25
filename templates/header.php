<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - Bolão 10' : 'Bolão 10' ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? $pageDescription : 'O Bolão 10 é o maior sistema de bolões do Brasil. Participe e concorra a prêmios incríveis!' ?>">
    <meta name="keywords" content="<?= isset($pageKeywords) ? $pageKeywords : 'bolão, sorteios, prêmios, loteria, bilhetes' ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= isset($_SERVER['REQUEST_URI']) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'https://bolao10.com.br' ?>">
    <meta property="og:title" content="<?= isset($pageTitle) ? $pageTitle . ' - Bolão 10' : 'Bolão 10' ?>">
    <meta property="og:description" content="<?= isset($pageDescription) ? $pageDescription : 'O Bolão 10 é o maior sistema de bolões do Brasil. Participe e concorra a prêmios incríveis!' ?>">
    <meta property="og:image" content="<?= isset($pageImage) ? $pageImage : 'assets/img/social-share.jpg' ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= isset($_SERVER['REQUEST_URI']) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'https://bolao10.com.br' ?>">
    <meta property="twitter:title" content="<?= isset($pageTitle) ? $pageTitle . ' - Bolão 10' : 'Bolão 10' ?>">
    <meta property="twitter:description" content="<?= isset($pageDescription) ? $pageDescription : 'O Bolão 10 é o maior sistema de bolões do Brasil. Participe e concorra a prêmios incríveis!' ?>">
    <meta property="twitter:image" content="<?= isset($pageImage) ? $pageImage : 'assets/img/social-share.jpg' ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/img/logo.png" alt="Bolão 10">
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="concursos.php">Concursos</a></li>
                    <li><a href="resultados.php">Resultados</a></li>
                    <li><a href="como_funciona.php">Como Funciona</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="minha_conta.php" class="btn-account">Minha Conta</a>
                    <a href="logout.php" class="btn-logout">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Entrar</a>
                    <a href="cadastro.php" class="btn-register">Cadastrar</a>
                <?php endif; ?>
            </div>
            <button class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="container">
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="concursos.php">Concursos</a></li>
                <li><a href="resultados.php">Resultados</a></li>
                <li><a href="como_funciona.php">Como Funciona</a></li>
                <li><a href="contato.php">Contato</a></li>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <li><a href="minha_conta.php">Minha Conta</a></li>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                    <li><a href="cadastro.php">Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="site-content">
        <!-- O conteúdo das páginas será inserido aqui -->
    </main>
</body>
</html> 