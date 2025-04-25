<?php
// Definir o título da página (opcional - se não definido, será usado um título padrão)
$page_title = "Dashboard";

// Importar o cabeçalho
require_once 'includes/header.php';

// Verificar o nível de acesso do usuário
$user_level = $_SESSION['user_level'] ?? 0;
?>

<div class="dashboard-wrapper">
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Apostas</h5>
                    <h2 class="card-text">
                        <?php
                        // Consulta SQL para obter o total de apostas (exemplo)
                        // $query = "SELECT COUNT(*) as total FROM apostas";
                        // $result = $conn->query($query);
                        // $row = $result->fetch_assoc();
                        // echo $row['total'];
                        echo "243"; // Valor de exemplo
                        ?>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver detalhes</span>
                    <a href="apostas.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Vendas do Mês</h5>
                    <h2 class="card-text">
                        R$ 
                        <?php
                        // Consulta SQL para obter o total de vendas do mês (exemplo)
                        // $query = "SELECT SUM(valor) as total FROM apostas WHERE MONTH(data) = MONTH(CURRENT_DATE())";
                        // $result = $conn->query($query);
                        // $row = $result->fetch_assoc();
                        // echo number_format($row['total'], 2, ',', '.');
                        echo "1.250,00"; // Valor de exemplo
                        ?>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver relatório</span>
                    <a href="relatorios.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Usuários Ativos</h5>
                    <h2 class="card-text">
                        <?php
                        // Consulta SQL para obter o total de usuários ativos (exemplo)
                        // $query = "SELECT COUNT(*) as total FROM users WHERE status = 'ativo'";
                        // $result = $conn->query($query);
                        // $row = $result->fetch_assoc();
                        // echo $row['total'];
                        echo "58"; // Valor de exemplo
                        ?>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Gerenciar usuários</span>
                    <a href="usuarios.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Concursos Ativos</h5>
                    <h2 class="card-text">
                        <?php
                        // Consulta SQL para obter o total de concursos ativos (exemplo)
                        // $query = "SELECT COUNT(*) as total FROM concursos WHERE status = 'ativo'";
                        // $result = $conn->query($query);
                        // $row = $result->fetch_assoc();
                        // echo $row['total'];
                        echo "5"; // Valor de exemplo
                        ?>
                    </h2>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver concursos</span>
                    <a href="concursos.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Apostas Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Concurso</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Dados de exemplo - normalmente viriam do banco de dados
                                $apostas_recentes = [
                                    ['id' => 1023, 'cliente' => 'João Silva', 'concurso' => 'Mega-Sena 2345', 'data' => '15/05/2023', 'valor' => 'R$ 17,50', 'status' => 'Pago'],
                                    ['id' => 1022, 'cliente' => 'Maria Souza', 'concurso' => 'Lotofácil 1234', 'data' => '15/05/2023', 'valor' => 'R$ 30,00', 'status' => 'Pago'],
                                    ['id' => 1021, 'cliente' => 'Pedro Costa', 'concurso' => 'Quina 6789', 'data' => '14/05/2023', 'valor' => 'R$ 10,00', 'status' => 'Pago'],
                                    ['id' => 1020, 'cliente' => 'Ana Oliveira', 'concurso' => 'Mega-Sena 2345', 'data' => '14/05/2023', 'valor' => 'R$ 4,50', 'status' => 'Pendente'],
                                    ['id' => 1019, 'cliente' => 'Carlos Santos', 'concurso' => 'Timemania 987', 'data' => '13/05/2023', 'valor' => 'R$ 10,00', 'status' => 'Pago'],
                                ];

                                foreach ($apostas_recentes as $aposta) {
                                    echo '<tr>';
                                    echo '<td>#' . $aposta['id'] . '</td>';
                                    echo '<td>' . $aposta['cliente'] . '</td>';
                                    echo '<td>' . $aposta['concurso'] . '</td>';
                                    echo '<td>' . $aposta['data'] . '</td>';
                                    echo '<td>' . $aposta['valor'] . '</td>';
                                    echo '<td><span class="badge ' . ($aposta['status'] == 'Pago' ? 'bg-success' : 'bg-warning') . '">' . $aposta['status'] . '</span></td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="apostas.php" class="btn btn-primary btn-sm">Ver todas as apostas</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Próximos Sorteios</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php
                        // Dados de exemplo - normalmente viriam do banco de dados
                        $proximos_sorteios = [
                            ['concurso' => 'Mega-Sena 2345', 'data' => '17/05/2023', 'valor' => 'R$ 45.000.000,00'],
                            ['concurso' => 'Lotofácil 1234', 'data' => '18/05/2023', 'valor' => 'R$ 1.700.000,00'],
                            ['concurso' => 'Quina 6789', 'data' => '19/05/2023', 'valor' => 'R$ 12.500.000,00'],
                            ['concurso' => 'Timemania 987', 'data' => '20/05/2023', 'valor' => 'R$ 4.300.000,00'],
                        ];

                        foreach ($proximos_sorteios as $sorteio) {
                            echo '<li class="list-group-item">';
                            echo '<div class="d-flex justify-content-between align-items-center">';
                            echo '<div>';
                            echo '<h6 class="mb-0">' . $sorteio['concurso'] . '</h6>';
                            echo '<small class="text-muted">Data: ' . $sorteio['data'] . '</small>';
                            echo '</div>';
                            echo '<span class="badge bg-primary rounded-pill">' . $sorteio['valor'] . '</span>';
                            echo '</div>';
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="concursos.php" class="btn btn-primary btn-sm">Ver todos os concursos</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos da página (opcional)
$page_scripts = [
    'assets/js/dashboard.js'
];

// Importar o rodapé
require_once 'includes/footer.php';
?> 