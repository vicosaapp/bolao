<?php
/**
 * Script para salvar configurações do sistema
 * Requer nível de acesso de administrador (3)
 */

require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../models/DB.php';
require_once '../models/ConfiguracaoModel.php';
require_once '../models/LogAtividadeModel.php';
require_once '../auth.php';

// Definir header para retornar JSON
header('Content-Type: application/json');

// Verificar se é administrador
requireAccess(3); // Nível 3: superadmin

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido']);
    exit;
}

// Inicializar modelos
$configuracaoModel = new ConfiguracaoModel();
$logModel = new LogAtividadeModel();

try {
    // Obter todas as configurações existentes para saber quais valores devem ser atualizados
    $configuracoesExistentes = $configuracaoModel->obterConfiguracoes();
    $chaves = [];
    
    foreach ($configuracoesExistentes as $config) {
        $chaves[] = $config['chave'];
    }
    
    // Lista de configurações atualizadas
    $atualizadas = [];
    
    // Processar cada configuração enviada
    foreach ($_POST as $chave => $valor) {
        // Verificar se a chave existe nas configurações
        if (in_array($chave, $chaves)) {
            // Sanitizar o valor
            $valorSanitizado = filter_var($valor, FILTER_SANITIZE_STRING);
            
            // Atualizar configuração
            $resultado = $configuracaoModel->atualizarConfiguracao($chave, $valorSanitizado);
            
            if ($resultado) {
                $atualizadas[] = $chave;
            }
        }
    }
    
    // Verificar configurações de arquivo (upload)
    foreach ($_FILES as $chave => $arquivo) {
        if (in_array($chave, $chaves) && $arquivo['error'] === UPLOAD_ERR_OK) {
            // Verificar se é uma imagem válida
            $imagemInfo = getimagesize($arquivo['tmp_name']);
            if ($imagemInfo === false) {
                throw new Exception("O arquivo enviado não é uma imagem válida");
            }
            
            // Gerar nome único para o arquivo
            $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid("img_") . '.' . $extensao;
            $diretorioDestino = '../../assets/img/';
            
            // Criar diretório se não existir
            if (!file_exists($diretorioDestino)) {
                mkdir($diretorioDestino, 0755, true);
            }
            
            // Mover arquivo
            if (move_uploaded_file($arquivo['tmp_name'], $diretorioDestino . $nomeArquivo)) {
                // Salvar caminho do arquivo no banco
                $caminho = '/assets/img/' . $nomeArquivo;
                $resultado = $configuracaoModel->atualizarConfiguracao($chave, $caminho);
                
                if ($resultado) {
                    $atualizadas[] = $chave;
                }
            } else {
                throw new Exception("Erro ao fazer upload do arquivo");
            }
        }
    }
    
    // Registrar no log
    if (!empty($atualizadas)) {
        $descricao = "Configurações atualizadas: " . implode(', ', $atualizadas);
        $logModel->registrarLog($_SESSION['usuario_id'], 'atualizar_configuracoes', $descricao);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => count($atualizadas) . ' configurações foram atualizadas com sucesso'
        ]);
    } else {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Nenhuma configuração foi alterada']);
    }
    
} catch (Exception $e) {
    // Log do erro
    if (isset($_SESSION['usuario_id'])) {
        $logModel->registrarLog($_SESSION['usuario_id'], 'erro', "Erro ao salvar configurações: " . $e->getMessage());
    }
    
    // Resposta de erro
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
?> 