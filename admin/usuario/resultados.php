<?php
// Verificar permissões
require_once '../auth.php';
requireAccess(1); // Nível mínimo: usuário

// Definir o título da página
$page_title = "Resultados";

// Incluir arquivos necessários
require_once '../templates/header.php';
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';

// Função para formatar data
function formatar_data($data) {
    if (!$data) return '-';
    return date('d/m/Y H:i', strtotime($data));
}

// Inicializar modelos
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();

// Obter ID do usuário logado
$usuario_id = $_SESSION['usuario_id'] ?? 0;

// Busca concursos finalizados
$concursos = $concursoModel->listarConcursosFinalizados();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Resultados</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Resultados</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-trophy me-1"></i>
            Resultados dos Concursos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Concurso</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Valor Prêmios</th>
                            <th>Meus Bilhetes</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($concursos as $concurso): 
                              $total_bilhetes = $bilheteModel->contarBilhetesUsuarioConcurso($usuario_id, $concurso['id']);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($concurso['titulo'] ?? $concurso['nome']); ?></td>
                            <td><?php echo formatar_data($concurso['data_inicio']); ?></td>
                            <td><?php echo formatar_data($concurso['data_fim'] ?? $concurso['data_sorteio']); ?></td>
                            <td>R$ <?php echo number_format($concurso['valor_premios'], 2, ',', '.'); ?></td>
                            <td><?php echo $total_bilhetes; ?></td>
                            <td>
                                <button type="button" 
                                        class="btn btn-primary btn-sm" 
                                        onclick="verDetalhes(<?php echo $concurso['id']; ?>)">
                                    <i class="fas fa-search"></i> Ver Detalhes
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Concurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function verDetalhes(concursoId) {
    $.ajax({
        url: 'ajax/detalhes_concurso.php',
        type: 'POST',
        data: { concurso_id: concursoId },
        success: function(response) {
            $('#modalContent').html(response);
            $('#modalDetalhes').modal('show');
        }
    });
}

$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        }
    });
});
</script>

<?php 
// Incluir o rodapé
$footer_file = '../templates/footer.php';
if (file_exists($footer_file)) {
    require_once $footer_file;
}
?>