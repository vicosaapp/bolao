<?php
require_once 'db_config.php';

/**
 * Busca informações de um bilhete pelo número
 * 
 * @param int $numero_bilhete Número do bilhete
 * @return array|null Dados do bilhete ou null se não encontrado
 */
function buscarBilhete($numero_bilhete) {
    global $conn;
    
    $numero_bilhete = mysqli_real_escape_string($conn, $numero_bilhete);
    
    $sql = "SELECT b.*, a.nome as apostador, a.cidade, a.estado, v.nome as vendedor 
            FROM bilhetes b 
            LEFT JOIN apostadores a ON b.apostador_id = a.id 
            LEFT JOIN vendedores v ON b.vendedor_id = v.id 
            WHERE b.numero = '$numero_bilhete'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Busca os detalhes dos jogos de um bilhete
 * 
 * @param int $bilhete_id ID do bilhete
 * @return array Array com os jogos do bilhete
 */
function buscarJogosBilhete($bilhete_id) {
    global $conn;
    
    $bilhete_id = mysqli_real_escape_string($conn, $bilhete_id);
    
    $sql = "SELECT * FROM jogos_bilhete WHERE bilhete_id = '$bilhete_id' ORDER BY id ASC";
    
    $result = $conn->query($sql);
    $jogos = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jogos[] = $row;
        }
    }
    
    return $jogos;
}

/**
 * Busca informações do concurso pelo ID
 * 
 * @param int $concurso_id ID do concurso
 * @return array|null Dados do concurso ou null se não encontrado
 */
function buscarConcurso($concurso_id) {
    global $conn;
    
    $concurso_id = mysqli_real_escape_string($conn, $concurso_id);
    
    $sql = "SELECT * FROM concursos WHERE id = '$concurso_id'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Busca os sorteios de um concurso
 * 
 * @param int $concurso_id ID do concurso
 * @return array Array com os sorteios do concurso
 */
function buscarSorteiosConcurso($concurso_id) {
    global $conn;
    
    $concurso_id = mysqli_real_escape_string($conn, $concurso_id);
    
    $sql = "SELECT * FROM sorteios WHERE concurso_id = '$concurso_id' ORDER BY ordem ASC";
    
    $result = $conn->query($sql);
    $sorteios = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sorteios[] = $row;
        }
    }
    
    return $sorteios;
}

/**
 * Busca os números sorteados de um concurso
 * 
 * @param int $concurso_id ID do concurso
 * @return array Array com os números sorteados
 */
function buscarNumerosSorteados($concurso_id) {
    global $conn;
    
    $concurso_id = mysqli_real_escape_string($conn, $concurso_id);
    
    $sql = "SELECT numero FROM numeros_sorteados WHERE concurso_id = '$concurso_id' ORDER BY numero ASC";
    
    $result = $conn->query($sql);
    $numeros = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $numeros[] = $row['numero'];
        }
    }
    
    return $numeros;
}

/**
 * Busca os prêmios de um concurso
 * 
 * @param int $concurso_id ID do concurso
 * @return array Array com os prêmios do concurso
 */
function buscarPremiosConcurso($concurso_id) {
    global $conn;
    
    $concurso_id = mysqli_real_escape_string($conn, $concurso_id);
    
    $sql = "SELECT * FROM premios WHERE concurso_id = '$concurso_id' ORDER BY id ASC";
    
    $result = $conn->query($sql);
    $premios = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $premios[] = $row;
        }
    }
    
    return $premios;
}

/**
 * Busca os bilhetes com maior pontuação em um concurso
 * 
 * @param int $concurso_id ID do concurso
 * @param int $limite Limite de bilhetes a serem retornados
 * @return array Array com os bilhetes
 */
function buscarTopBilhetes($concurso_id, $limite = 20) {
    global $conn;
    
    $concurso_id = mysqli_real_escape_string($conn, $concurso_id);
    $limite = intval($limite);
    
    $sql = "SELECT b.*, a.nome as apostador, a.cidade, a.estado, COUNT(n.id) as pontos
            FROM bilhetes b 
            LEFT JOIN apostadores a ON b.apostador_id = a.id
            LEFT JOIN jogos_bilhete j ON b.id = j.bilhete_id
            LEFT JOIN numeros_bilhete n ON j.id = n.jogo_id
            INNER JOIN numeros_sorteados s ON n.numero = s.numero AND s.concurso_id = b.concurso_id
            WHERE b.concurso_id = '$concurso_id'
            GROUP BY b.id
            ORDER BY pontos DESC, b.id ASC
            LIMIT $limite";
    
    $result = $conn->query($sql);
    $bilhetes = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bilhetes[] = $row;
        }
    }
    
    return $bilhetes;
}

/**
 * Formata um valor monetário
 * 
 * @param float $valor Valor a ser formatado
 * @return string Valor formatado
 */
function formatarDinheiro($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata uma data
 * 
 * @param string $data Data no formato Y-m-d H:i:s
 * @param string $formato Formato desejado
 * @return string Data formatada
 */
function formatarData($data, $formato = 'd/m/Y H:i:s') {
    $timestamp = strtotime($data);
    return date($formato, $timestamp);
}

/**
 * Calcula os pontos de um bilhete
 * 
 * @param int $bilhete_id ID do bilhete
 * @return int Quantidade de pontos
 */
function calcularPontosBilhete($bilhete_id) {
    global $conn;
    
    $bilhete_id = mysqli_real_escape_string($conn, $bilhete_id);
    
    $sql = "SELECT COUNT(n.id) as pontos
            FROM bilhetes b 
            LEFT JOIN jogos_bilhete j ON b.id = j.bilhete_id
            LEFT JOIN numeros_bilhete n ON j.id = n.jogo_id
            INNER JOIN numeros_sorteados s ON n.numero = s.numero AND s.concurso_id = b.concurso_id
            WHERE b.id = '$bilhete_id'
            GROUP BY b.id";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['pontos'];
    }
    
    return 0;
}
?> 