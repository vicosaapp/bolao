-- Cria o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS bolao_db;

-- Usa o banco de dados
USE bolao_db;

-- Tabela de Apostadores
CREATE TABLE IF NOT EXISTS apostadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    telefone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Vendedores
CREATE TABLE IF NOT EXISTS vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100),
    comissao DECIMAL(5,2) DEFAULT 10.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Concursos
CREATE TABLE IF NOT EXISTS concursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME NOT NULL,
    status ENUM('em_andamento', 'finalizado', 'cancelado') DEFAULT 'em_andamento',
    valor_premios DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Sorteios
CREATE TABLE IF NOT EXISTS sorteios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concurso_id INT NOT NULL,
    ordem INT NOT NULL,
    descricao VARCHAR(100) NOT NULL,
    data_sorteio DATETIME NOT NULL,
    numeros_sorteados VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (concurso_id) REFERENCES concursos(id)
);

-- Tabela de Números Sorteados (para facilitar as consultas)
CREATE TABLE IF NOT EXISTS numeros_sorteados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concurso_id INT NOT NULL,
    sorteio_id INT NOT NULL,
    numero INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (concurso_id) REFERENCES concursos(id),
    FOREIGN KEY (sorteio_id) REFERENCES sorteios(id)
);

-- Tabela de Prêmios do Concurso
CREATE TABLE IF NOT EXISTS premios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concurso_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    regra VARCHAR(255) NOT NULL,
    status ENUM('acumulado', 'pago') DEFAULT 'acumulado',
    quantidade_ganhadores INT DEFAULT 0,
    valor_por_ganhador DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (concurso_id) REFERENCES concursos(id)
);

-- Tabela de Bilhetes
CREATE TABLE IF NOT EXISTS bilhetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(20) NOT NULL,
    concurso_id INT NOT NULL,
    apostador_id INT,
    vendedor_id INT,
    valor DECIMAL(6,2) NOT NULL,
    status ENUM('pago', 'pendente', 'cancelado') DEFAULT 'pago',
    data_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (concurso_id) REFERENCES concursos(id),
    FOREIGN KEY (apostador_id) REFERENCES apostadores(id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
);

-- Tabela de Jogos por Bilhete
CREATE TABLE IF NOT EXISTS jogos_bilhete (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bilhete_id INT NOT NULL,
    ordem INT NOT NULL,
    status ENUM('normal', 'premiado', 'nao_premiado') DEFAULT 'normal',
    pontos INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bilhete_id) REFERENCES bilhetes(id)
);

-- Tabela de Números por Jogo
CREATE TABLE IF NOT EXISTS numeros_bilhete (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT NOT NULL,
    numero INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jogo_id) REFERENCES jogos_bilhete(id)
);

-- Tabela de Ganhadores por Prêmio
CREATE TABLE IF NOT EXISTS ganhadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    premio_id INT NOT NULL,
    bilhete_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME,
    status ENUM('pendente', 'pago') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (premio_id) REFERENCES premios(id),
    FOREIGN KEY (bilhete_id) REFERENCES bilhetes(id)
);

-- Tabela de Usuários do Sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    tipo ENUM('admin', 'operador') DEFAULT 'operador',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    ultimo_acesso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Índices para otimização de consultas
CREATE INDEX idx_bilhete_numero ON bilhetes(numero);
CREATE INDEX idx_concurso_numero ON concursos(numero);
CREATE INDEX idx_numero_sorteado ON numeros_sorteados(numero);
CREATE INDEX idx_numero_bilhete ON numeros_bilhete(numero);

-- Insere um usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, usuario, senha, email, tipo) VALUES
('Administrador', 'admin', '$2y$10$YMz9PDYQnM50pJbFQ8Zrce5FkH4wIlYYCE/dKjmXsrAwTHFoKKC2W', 'admin@bolao.com', 'admin'); 