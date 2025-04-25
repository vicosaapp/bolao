<?php
// Definir o título da página
$page_title = "Configurações do Sistema";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/configuracoes.css'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/ConfiguracaoModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelo
$configuracaoModel = new ConfiguracaoModel();

// Obter configurações
$configuracoes = $configuracaoModel->obterConfiguracoes();

// Organizar configurações por categoria
$configPorCategoria = [];
foreach ($configuracoes as $config) {
    $categoria = $config['categoria'];
    if (!isset($configPorCategoria[$categoria])) {
        $configPorCategoria[$categoria] = [];
    }
    $configPorCategoria[$categoria][] = $config;
}

// Obter categorias
$categorias = $configuracaoModel->obterCategorias();

// Função auxiliar para gerar input baseado no tipo
function gerarInput($config) {
    $chave = $config['chave'];
    $valor = $config['valor'];
    $tipo = $config['tipo'];
    $descricao = $config['descricao'];
    
    $html = '<div class="form-floating mb-3">';
    
    switch ($tipo) {
        case 'boolean':
            $checked = ($valor === 'true' || $valor === '1') ? 'checked' : '';
            $html .= '<div class="form-check form-switch mt-2">';
            $html .= '<input class="form-check-input" type="checkbox" id="' . $chave . '" name="' . $chave . '" ' . $checked . '>';
            $html .= '<label class="form-check-label" for="' . $chave . '">' . $descricao . '</label>';
            $html .= '</div>';
            break;
            
        case 'textarea':
            $html .= '<textarea class="form-control" id="' . $chave . '" name="' . $chave . '" style="height: 100px">' . htmlspecialchars($valor) . '</textarea>';
            $html .= '<label for="' . $chave . '">' . $descricao . '</label>';
            break;
            
        case 'select':
            $opcoes = json_decode($config['opcoes'], true);
            $html .= '<select class="form-select" id="' . $chave . '" name="' . $chave . '">';
            
            if ($opcoes && is_array($opcoes)) {
                $valorArray = json_decode($valor, true);
                
                foreach ($opcoes as $value => $label) {
                    $selected = '';
                    
                    if (is_array($valorArray) && in_array($value, $valorArray)) {
                        $selected = 'selected';
                    } elseif ($valor == $value) {
                        $selected = 'selected';
                    }
                    
                    $html .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
                }
            }
            
            $html .= '</select>';
            $html .= '<label for="' . $chave . '">' . $descricao . '</label>';
            break;
            
        case 'color':
            $html .= '<div class="input-group">';
            $html .= '<span class="cor-preview"></span>';
            $html .= '<input type="color" class="form-control form-control-color" id="' . $chave . '" name="' . $chave . '" value="' . htmlspecialchars($valor) . '" title="Escolha uma cor">';
            $html .= '<label for="' . $chave . '">' . $descricao . '</label>';
            $html .= '</div>';
            break;
            
        case 'file':
            $html .= '<div class="mb-3">';
            $html .= '<label for="' . $chave . '" class="form-label">' . $descricao . '</label>';
            if (!empty($valor)) {
                $html .= '<div class="mb-2">';
                $html .= '<img src="' . $valor . '" alt="Imagem atual" class="img-thumbnail" style="max-height: 100px">';
                $html .= '</div>';
            }
            $html .= '<input class="form-control" type="file" id="' . $chave . '" name="' . $chave . '">';
            $html .= '</div>';
            break;
            
        default: // text, number, email, etc
            $inputType = ($tipo == 'number') ? 'number' : ($tipo == 'email' ? 'email' : 'text');
            $step = ($tipo == 'number') ? 'step="0.01"' : '';
            
            $html .= '<input type="' . $inputType . '" class="form-control" id="' . $chave . '" name="' . $chave . '" value="' . htmlspecialchars($valor) . '" ' . $step . '>';
            $html .= '<label for="' . $chave . '">' . $descricao . '</label>';
            break;
    }
    
    $html .= '</div>';
    return $html;
}
?>

<div class="content">
    <div class="content-header">
        <h1>Configurações do Sistema</h1>
        <p class="text-muted">Gerencie todas as configurações do sistema de bolão</p>
    </div>
    
    <!-- Área de alertas -->
    <div id="alertPlaceholder"></div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Gerenciar Configurações</h5>
        </div>
        
        <div class="card-body">
            <form id="formConfiguracoes" class="config-form" enctype="multipart/form-data">
                <div class="row">
                    <!-- Navegação por tabs -->
                    <div class="col-md-3">
                        <div class="nav flex-column nav-pills me-3" id="config-tab" role="tablist">
                            <?php foreach ($categorias as $index => $categoria): ?>
                                <button class="nav-link <?php echo ($index === 0) ? 'active' : ''; ?>" 
                                        id="tab-<?php echo $categoria; ?>" 
                                        data-bs-toggle="pill" 
                                        data-bs-target="#config-<?php echo $categoria; ?>" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="config-<?php echo $categoria; ?>" 
                                        aria-selected="<?php echo ($index === 0) ? 'true' : 'false'; ?>">
                                    <i class="fas fa-<?php 
                                        $icon = 'sliders-h'; // valor padrão
                                        switch($categoria) {
                                            case 'geral':
                                                $icon = 'cog';
                                                break;
                                            case 'aparencia':
                                                $icon = 'palette';
                                                break;
                                            case 'bolao':
                                                $icon = 'ticket-alt';
                                                break;
                                            case 'pagamento':
                                                $icon = 'money-bill';
                                                break;
                                            case 'notificacao':
                                                $icon = 'bell';
                                                break;
                                            case 'relatorios':
                                                $icon = 'chart-bar';
                                                break;
                                            case 'seguranca':
                                                $icon = 'shield-alt';
                                                break;
                                        }
                                        echo $icon;
                                    ?>"></i> 
                                    <?php echo ucfirst($categoria); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Conteúdo dos tabs -->
                    <div class="col-md-9">
                        <div class="tab-content" id="config-tabContent">
                            <?php foreach ($categorias as $index => $categoria): ?>
                                <div class="tab-pane fade <?php echo ($index === 0) ? 'show active' : ''; ?>" 
                                     id="config-<?php echo $categoria; ?>" 
                                     role="tabpanel" 
                                     aria-labelledby="tab-<?php echo $categoria; ?>">
                                    
                                    <h4><?php echo ucfirst($categoria); ?></h4>
                                    <hr>
                                    
                                    <?php if (isset($configPorCategoria[$categoria])): ?>
                                        <?php foreach ($configPorCategoria[$categoria] as $config): ?>
                                            <?php echo gerarInput($config); ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            Nenhuma configuração disponível nesta categoria.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Botão de salvar -->
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg btn-save-settings">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/configuracoes.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 