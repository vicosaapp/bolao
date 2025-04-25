<?php
/**
 * Script para criar a tabela de logs de atividades no banco de dados
 */
require_once '../../includes/config.php';
require_once '../../includes/Database.php';
require_once '../auth.php';

// Verificar autenticação e permissão
requireAccess(3); // Nível 3: superadmin

// Criar instância do banco de dados
$db = new Database();
$conn = $db->getConnection();

// Definir o SQL para criar a tabela
$sql = "
CREATE TABLE IF NOT EXISTS `log_atividades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_atividade` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `ip` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `dados_adicionais` text DEFAULT NULL,
  `data_registro` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_log_usuario` (`usuario_id`),
  KEY `idx_log_tipo` (`tipo_atividade`),
  KEY `idx_log_data` (`data_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Executar a consulta
try {
    if ($conn->query($sql)) {
        echo "Tabela log_atividades criada com sucesso!";
        
        // Registrar a criação da tabela no próprio log
        $usuario_id = $_SESSION['usuario_id'];
        
        // Usar prepared statement para inserir o primeiro registro de log
        $stmt = $conn->prepare("
            INSERT INTO log_atividades 
            (usuario_id, tipo_atividade, descricao, ip, user_agent, data_registro) 
            VALUES (?, 'sistema', 'Tabela de logs criada no sistema', ?, ?, NOW())
        ");
        
        // Obter IP e user agent
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
        
        $stmt->bind_param("iss", $usuario_id, $ip, $user_agent);
        $stmt->execute();
        
        echo "<br>Primeiro registro de log criado com sucesso!";
    } else {
        echo "Erro ao criar a tabela: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?> 