<?php
/**
 * Script para configurar o banco de dados
 * 
 * Este script cria o banco de dados e as tabelas e insere dados de exemplo
 */

// Habilita exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    echo "Iniciando configuração do banco de dados...\n";
    
    // Conecta ao servidor MySQL sem selecionar um banco de dados
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }
    
    echo "Conectado ao servidor de banco de dados com sucesso!\n";
    
    // Executa o script SQL para criar o banco de dados e tabelas
    $sqlCreateTablesPath = __DIR__ . '/create_tables.sql';
    echo "Lendo arquivo: $sqlCreateTablesPath\n";
    
    $sqlCreateTables = file_get_contents($sqlCreateTablesPath);
    
    if (empty($sqlCreateTables)) {
        throw new Exception("Não foi possível ler o arquivo de criação de tabelas.");
    }
    
    echo "Arquivo lido com sucesso. Tamanho: " . strlen($sqlCreateTables) . " bytes\n";
    
    // Executa os comandos SQL separadamente
    $queries = explode(';', $sqlCreateTables);
    
    foreach ($queries as $query) {
        $query = trim($query);
        
        if (empty($query)) {
            continue;
        }
        
        echo "Executando query: " . substr($query, 0, 50) . "...\n";
        
        if ($conn->query($query)) {
            echo "Query executada com sucesso!\n";
        } else {
            echo "Erro ao executar query: " . $conn->error . "\n";
            echo "Query completa: " . $query . "\n";
        }
    }
    
    // Seleciona o banco de dados criado
    echo "Selecionando banco de dados 'bolao_db'...\n";
    $conn->select_db('bolao_db');
    
    // Executa o script SQL para inserir dados de exemplo
    $sqlInsertDataPath = __DIR__ . '/sample_data.sql';
    echo "Lendo arquivo: $sqlInsertDataPath\n";
    
    $sqlInsertData = file_get_contents($sqlInsertDataPath);
    
    if (empty($sqlInsertData)) {
        throw new Exception("Não foi possível ler o arquivo de dados de exemplo.");
    }
    
    echo "Arquivo lido com sucesso. Tamanho: " . strlen($sqlInsertData) . " bytes\n";
    
    // Executa os comandos SQL separadamente
    $queries = explode(';', $sqlInsertData);
    
    foreach ($queries as $query) {
        $query = trim($query);
        
        if (empty($query)) {
            continue;
        }
        
        echo "Executando query: " . substr($query, 0, 50) . "...\n";
        
        if ($conn->query($query)) {
            echo "Query executada com sucesso!\n";
        } else {
            echo "Erro ao executar query: " . $conn->error . "\n";
            echo "Query completa: " . $query . "\n";
        }
    }
    
    echo "Banco de dados configurado com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($conn)) {
        $conn->close();
        echo "Conexão com o banco de dados fechada.\n";
    }
}

echo "Script finalizado.\n";
?> 