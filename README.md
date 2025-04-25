# Sistema de Bolão

Este projeto é uma réplica do site Bolão da Sorte, um sistema de bolão de loteria com funcionalidades para consulta de bilhetes, visualização de resultados e prêmios.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache, Nginx, etc.)

## Instalação

1. Clone ou baixe este repositório para o diretório do seu servidor web.

2. Configure o banco de dados:
   - Crie um banco de dados MySQL chamado `bolao_db` ou use o script de configuração automática.
   - Configure as credenciais do banco de dados no arquivo `includes/db_config.php`.

3. Execute o script de configuração do banco de dados:
   ```
   php database/setup_database.php
   ```
   Este script irá criar as tabelas e inserir dados de exemplo no banco de dados.

4. Acesse o sistema pelo navegador:
   ```
   http://localhost/bolao
   ```

## Estrutura do Projeto

- `/assets`: Arquivos estáticos (CSS, JavaScript, imagens)
- `/database`: Scripts SQL e de configuração do banco de dados
- `/includes`: Arquivos PHP de configuração e funções
- `/templates`: Templates HTML reutilizáveis

## Funcionalidades

- Consulta de bilhetes por número
- Visualização de detalhes do bilhete e pontuação
- Visualização dos sorteios realizados
- Visualização dos prêmios disponíveis
- Ranking dos bilhetes com maior pontuação

## Banco de Dados

O sistema utiliza as seguintes tabelas:

- `apostadores`: Cadastro de apostadores
- `vendedores`: Cadastro de vendedores
- `concursos`: Cadastro de concursos/edições do bolão
- `sorteios`: Sorteios realizados em cada concurso
- `numeros_sorteados`: Números sorteados em cada sorteio
- `premios`: Prêmios disponíveis em cada concurso
- `bilhetes`: Bilhetes vendidos
- `jogos_bilhete`: Jogos de cada bilhete
- `numeros_bilhete`: Números escolhidos em cada jogo
- `ganhadores`: Registro dos ganhadores de prêmios
- `usuarios`: Usuários do sistema administrativo

## Conta de Administrador

Para acessar a área administrativa (quando implementada), use as seguintes credenciais:

- Usuário: admin
- Senha: admin123

## Contribuição

Sinta-se à vontade para contribuir com o projeto enviando pull requests ou relatando problemas na seção de issues.

## Licença

Este projeto é distribuído sob a licença MIT. Veja o arquivo LICENSE para mais detalhes. 