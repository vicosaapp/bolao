-- Criar a tabela de usuários no banco de dados bolao_db
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin', 'operador') NOT NULL DEFAULT 'operador',
  `status` enum('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acesso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir um usuário superadmin inicial (senha: admin123)
INSERT INTO `usuarios` (`nome`, `usuario`, `email`, `senha`, `tipo`, `status`) VALUES
('Administrador', 'admin', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'ativo');

-- Para referência:
-- A senha 'admin123' foi hasheada usando password_hash() com o algoritmo padrão (atualmente bcrypt)
-- Tipos de usuário:
-- 'admin' = Super Admin (nível 4)
-- 'operador' = Administrador, Revendedor ou Apostador (níveis 1-3) 