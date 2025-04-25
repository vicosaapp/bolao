<?php
require_once 'config/database.php';

// Verificar a estrutura da tabela
$query = "DESCRIBE usuarios";
$result = $conn->query($query);

if ($result) {
    echo "Colunas da tabela usuarios:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo "Erro ao consultar a tabela: " . $conn->error;
}

$conn->close();
?> 