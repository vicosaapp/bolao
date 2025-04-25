<?php
require_once './includes/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar tabelas existentes
    $query = "SHOW TABLES FROM bolao";
    $result = $db->query($query);
    
    echo "<h2>Tabelas existentes no banco de dados:</h2>";
    echo "<ul>";
    while ($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
    // Verificar estrutura da tabela sorteios (se existir)
    $query = "SHOW TABLES FROM bolao LIKE 'sorteios'";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        $query = "DESCRIBE sorteios";
        $result = $db->query($query);
        
        echo "<h2>Estrutura da tabela 'sorteios':</h2>";
        echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h2>A tabela 'sorteios' não existe!</h2>";
    }
    
    // Verificar estrutura da tabela numeros_sorteados (se existir)
    $query = "SHOW TABLES FROM bolao LIKE 'numeros_sorteados'";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        $query = "DESCRIBE numeros_sorteados";
        $result = $db->query($query);
        
        echo "<h2>Estrutura da tabela 'numeros_sorteados':</h2>";
        echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h2>A tabela 'numeros_sorteados' não existe!</h2>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?> 