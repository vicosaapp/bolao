-- Usa o banco de dados
USE bolao_db;

-- Insere apostadores de exemplo
INSERT INTO apostadores (nome, cidade, estado, telefone, email) VALUES
('Gilson Santos', 'Salvador', 'BA', '(71) 99999-9999', 'gilson@email.com'),
('Orlando Dias da Silva', 'Alagoinhas', 'BA', '(75) 98888-8888', 'orlando@email.com'),
('Gratidão', 'Araguari', 'MG', '(34) 97777-7777', 'gratidao@email.com'),
('Máfia do Guincho', 'Barueri', 'SP', '(11) 96666-6666', 'mafia@email.com'),
('Joab', 'Castro Alves', 'BA', '(75) 95555-5555', 'joab@email.com'),
('Neném', 'Irará', 'BA', '(75) 94444-4444', 'nenem@email.com'),
('Valdir Brito dos Santos', 'Irará', 'BA', '(75) 93333-3333', 'valdir@email.com'),
('Gigante', 'São Paulo', 'SP', '(11) 92222-2222', 'gigante@email.com');

-- Insere vendedores de exemplo
INSERT INTO vendedores (codigo, nome, telefone, email, comissao) VALUES
('012106', 'Harcon', '(75) 99999-9999', 'harcon@email.com', 10.00),
('012107', 'José Silva', '(75) 98888-8888', 'jose@email.com', 10.00),
('012108', 'Maria Santos', '(75) 97777-7777', 'maria@email.com', 10.00);

-- Insere concurso de exemplo
INSERT INTO concursos (numero, nome, data_inicio, data_fim, status, valor_premios) VALUES
(307, 'Bolão de Quarta', '2025-03-12 18:00:00', '2025-03-13 16:00:00', 'finalizado', 54480.00);

-- Insere sorteios de exemplo
INSERT INTO sorteios (concurso_id, ordem, descricao, data_sorteio, numeros_sorteados) VALUES
(1, 1, '21h RIO', '2025-03-12 21:00:00', '18,12,81,12,24,35,83,07,08,88'),
(1, 2, '09h RIO', '2025-03-13 09:00:00', '35,61,72,42,25,36,71,21,21,88'),
(1, 3, '11h RIO', '2025-03-13 11:00:00', '17,30,64,35,03,02,71,30,09,16'),
(1, 4, '14h RIO', '2025-03-13 14:00:00', '65,61,08,10,28,77,09,37,91,71'),
(1, 5, '16h RIO', '2025-03-13 16:00:00', '14,65,86,11,43,67,12,76,07,46');

-- Insere os números sorteados (separados para facilitar as consultas)
-- 1º Sorteio
INSERT INTO numeros_sorteados (concurso_id, sorteio_id, numero) VALUES
(1, 1, 18), (1, 1, 12), (1, 1, 81), (1, 1, 12), (1, 1, 24), (1, 1, 35), (1, 1, 83), (1, 1, 7), (1, 1, 8), (1, 1, 88);

-- 2º Sorteio
INSERT INTO numeros_sorteados (concurso_id, sorteio_id, numero) VALUES
(1, 2, 35), (1, 2, 61), (1, 2, 72), (1, 2, 42), (1, 2, 25), (1, 2, 36), (1, 2, 71), (1, 2, 21), (1, 2, 21), (1, 2, 88);

-- 3º Sorteio
INSERT INTO numeros_sorteados (concurso_id, sorteio_id, numero) VALUES
(1, 3, 17), (1, 3, 30), (1, 3, 64), (1, 3, 35), (1, 3, 3), (1, 3, 2), (1, 3, 71), (1, 3, 30), (1, 3, 9), (1, 3, 16);

-- 4º Sorteio
INSERT INTO numeros_sorteados (concurso_id, sorteio_id, numero) VALUES
(1, 4, 65), (1, 4, 61), (1, 4, 8), (1, 4, 10), (1, 4, 28), (1, 4, 77), (1, 4, 9), (1, 4, 37), (1, 4, 91), (1, 4, 71);

-- 5º Sorteio
INSERT INTO numeros_sorteados (concurso_id, sorteio_id, numero) VALUES
(1, 5, 14), (1, 5, 65), (1, 5, 86), (1, 5, 11), (1, 5, 43), (1, 5, 67), (1, 5, 12), (1, 5, 76), (1, 5, 7), (1, 5, 46);

-- Insere os prêmios do concurso
INSERT INTO premios (concurso_id, descricao, valor, regra, status, quantidade_ganhadores, valor_por_ganhador) VALUES
(1, '10 Pontos', 44480.00, 'Ganha ao completar 10 Pontos.', 'pago', 1, 44480.00),
(1, '9 Pontos', 5000.00, 'Ganha ao completar 9 Pontos até o bolão finalizar.', 'pago', 13, 384.62),
(1, '0 Ponto', 1000.00, 'Ganha ao ficar com 0 Ponto ate o bolão finalizar.', 'pago', 267, 3.75),
(1, 'Sena 1º Sorteio', 3000.00, 'Ganha ao Acertar 6 ou mais Pontos só no 1º Sorteio.', 'acumulado', 0, 0.00),
(1, 'Quina 1º Sorteio', 1000.00, 'Ganha ao Acertar 5 Pontos só no 1º Sorteio.', 'pago', 2, 500.00);

-- Insere bilhetes de exemplo
-- Bilhete Premiado com 10 pontos
INSERT INTO bilhetes (numero, concurso_id, apostador_id, vendedor_id, valor, status, data_compra) VALUES
('8741891', 1, 1, 1, 5.00, 'pago', '2025-03-12 15:30:00');

-- Outros bilhetes
INSERT INTO bilhetes (numero, concurso_id, apostador_id, vendedor_id, valor, status, data_compra) VALUES
('8716846', 1, 2, 1, 5.00, 'pago', '2025-03-12 14:20:00'),
('8745949', 1, 3, 2, 5.00, 'pago', '2025-03-12 16:45:00'),
('8749737', 1, 4, 2, 5.00, 'pago', '2025-03-12 17:15:00'),
('8744520', 1, 8, 1, 5.00, 'pago', '2025-03-12 17:59:50');

-- Jogos para o bilhete premiado (8741891)
INSERT INTO jogos_bilhete (bilhete_id, ordem, status, pontos) VALUES
(1, 1, 'premiado', 10);

-- Números para o jogo do bilhete premiado
INSERT INTO numeros_bilhete (jogo_id, numero) VALUES
(1, 17), (1, 64), (1, 71), (1, 46), (1, 18), (1, 65), (1, 61), (1, 2), (1, 10), (1, 83);

-- Jogo para o bilhete 8716846
INSERT INTO jogos_bilhete (bilhete_id, ordem, status, pontos) VALUES
(2, 1, 'premiado', 9);

-- Números para o jogo do bilhete 8716846
INSERT INTO numeros_bilhete (jogo_id, numero) VALUES
(2, 9), (2, 14), (2, 21), (2, 28), (2, 30), (2, 36), (2, 71), (2, 73), (2, 83), (2, 88);

-- Jogo para o bilhete 8745949
INSERT INTO jogos_bilhete (bilhete_id, ordem, status, pontos) VALUES
(3, 1, 'premiado', 9);

-- Números para o jogo do bilhete 8745949
INSERT INTO numeros_bilhete (jogo_id, numero) VALUES
(3, 11), (3, 12), (3, 16), (3, 17), (3, 37), (3, 41), (3, 42), (3, 72), (3, 77), (3, 81);

-- Jogo para o bilhete 8749737
INSERT INTO jogos_bilhete (bilhete_id, ordem, status, pontos) VALUES
(4, 1, 'premiado', 9);

-- Números para o jogo do bilhete 8749737
INSERT INTO numeros_bilhete (jogo_id, numero) VALUES
(4, 7), (4, 8), (4, 9), (4, 11), (4, 12), (4, 13), (4, 35), (4, 36), (4, 37), (4, 81);

-- Jogo para o bilhete 8744520 (não premiado)
INSERT INTO jogos_bilhete (bilhete_id, ordem, status, pontos) VALUES
(5, 1, 'nao_premiado', 2);

-- Números para o jogo do bilhete 8744520
INSERT INTO numeros_bilhete (jogo_id, numero) VALUES
(5, 35), (5, 64), (5, 15), (5, 13), (5, 78), (5, 82), (5, 33), (5, 89), (5, 22), (5, 59);

-- Registra os ganhadores
-- Ganhador do prêmio de 10 pontos
INSERT INTO ganhadores (premio_id, bilhete_id, valor, data_pagamento, status) VALUES
(1, 1, 44480.00, '2025-03-14 10:00:00', 'pago');

-- Ganhadores do prêmio de 9 pontos (simplificado para apenas 3 dos 13 ganhadores)
INSERT INTO ganhadores (premio_id, bilhete_id, valor, data_pagamento, status) VALUES
(2, 2, 384.62, '2025-03-14 10:15:00', 'pago'),
(2, 3, 384.62, '2025-03-14 10:30:00', 'pago'),
(2, 4, 384.62, '2025-03-14 10:45:00', 'pago'); 