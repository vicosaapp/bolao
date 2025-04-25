<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_config.php';

class RevendedorModel {
    private $conexao;

    public function __construct() {
        global $conn;
        $this->conexao = $conn;
    }

    /**
     * Recupera os top revendedores com base em suas vendas
     * 
     * @param int $limite Número de revendedores a serem recuperados
     * @return array Lista de top revendedores
     */
    public function getTopRevendedores($limite = 5) {
        try {
            $cores = [
                '#FF6B8A', '#4D96FF', '#FFDA83', 
                '#75D7B5', '#B388FF', '#FF9F43', 
                '#6A5ACD', '#20B2AA'
            ];

            $query = "
                SELECT 
                    r.id, 
                    r.nome, 
                    COALESCE(SUM(v.valor), 0) as total_vendas
                FROM 
                    revendedores r
                LEFT JOIN 
                    vendas v ON r.id = v.revendedor_id
                WHERE 
                    v.status = 'concluida'
                    AND v.data >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                GROUP BY 
                    r.id, r.nome
                ORDER BY 
                    total_vendas DESC
                LIMIT ?
            ";

            $stmt = $this->conexao->prepare($query);
            $stmt->bind_param("i", $limite);
            $stmt->execute();

            $resultado = $stmt->get_result();
            $revendedores = [];

            while ($revendedor = $resultado->fetch_assoc()) {
                $revendedores[] = $revendedor;
            }

            // Adicionar cores e formatar dados
            return array_map(function($revendedor, $index) use ($cores) {
                return [
                    'nome' => $revendedor['nome'],
                    'vendas' => floatval($revendedor['total_vendas']),
                    'cor' => $cores[$index % count($cores)]
                ];
            }, $revendedores, array_keys($revendedores));

        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao buscar top revendedores: " . $e->getMessage());
            
            // Retornar array vazio em caso de erro
            return [];
        }
    }

    /**
     * Calcula o total de vendas dos top revendedores
     * 
     * @param array $revendedores Lista de revendedores
     * @return float Total de vendas
     */
    public function calcularTotalVendas($revendedores) {
        return array_reduce($revendedores, function($carry, $item) {
            return $carry + $item['vendas'];
        }, 0);
    }
} 