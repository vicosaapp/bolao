<?php
// Verificar permissões
require_once '../../../config/database.php';
require_once '../../auth.php';

// Função para formatar data
function formatar_data($data) {
    if (!$data) return '-';
    return date('d/m/Y H:i', strtotime($data));
}

// Verificar se está autenticado
if (!isset($_SESSION['usuario_id']) || !isset($_POST['concurso_id'])) {
    exit('Acesso negado');
}

$concurso_id = $_POST['concurso_id'];
$usuario_id = $_SESSION['usuario_id'];

// Carregar modelos com verificação de existência
$models_dir = '../../models/';
$concurso_model = $models_dir . 'ConcursoModel.php';
$bilhete_model = $models_dir . 'BilheteModel.php';

// Incluir os modelos necessários
if (file_exists($concurso_model) && file_exists($bilhete_model)) {
    require_once $concurso_model;
    require_once $bilhete_model;
    
    // Inicializar modelos
    try {
        $concursoModel = new ConcursoModel();
        $bilheteModel = new BilheteModel();
        
        // Busca detalhes do concurso
        $concurso = $concursoModel->obterConcursoPorId($concurso_id);
        if (!$concurso) {
            exit('Concurso não encontrado');
        }
        
        // Buscar bilhetes do usuário para este concurso
        $bilhetes = $bilheteModel->listarBilhetesUsuarioConcurso($usuario_id, $concurso_id);
    } catch (Exception $e) {
        error_log("Erro ao carregar detalhes do concurso: " . $e->getMessage());
        exit('Erro ao carregar detalhes do concurso. Por favor, tente novamente.');
    }
} else {
    exit('Erro ao carregar os modelos necessários');
}
?>

<div class="row">
    <div class="col-md-12">
        <h4><?php echo htmlspecialchars($concurso['titulo'] ?? $concurso['nome']); ?></h4>
        <p>Período: <?php echo formatar_data($concurso['data_inicio']); ?> 
           até <?php echo formatar_data($concurso['data_fim'] ?? $concurso['data_sorteio']); ?></p>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <h5>Seus Bilhetes neste Concurso</h5>
        <?php if (empty($bilhetes)): ?>
            <div class="alert alert-info">
                Você não possui bilhetes para este concurso.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Valor</th>
                        <th>Data Compra</th>
                        <th>Status</th>
                        <?php if (!empty($bilhetes[0]['valor_premio'])): ?>
                            <th>Premiação</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bilhetes as $bilhete): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bilhete['numero']); ?></td>
                        <td>R$ <?php echo number_format($bilhete['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo formatar_data($bilhete['data_compra']); ?></td>
                        <td><?php echo ucfirst($bilhete['status']); ?></td>
                        <?php if (isset($bilhete['valor_premio'])): ?>
                            <td>R$ <?php echo number_format($bilhete['valor_premio'], 2, ',', '.'); ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>