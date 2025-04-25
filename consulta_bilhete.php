<?php
// Configurações da página
$pageTitle = 'Consulta de Bilhete';

// Inclui as funções e a conexão com o banco de dados
require_once 'includes/functions.php';

// Verifica se foi passado um número de bilhete
$numeroBilhete = isset($_GET['bilhete']) ? trim($_GET['bilhete']) : '';

$bilhete = null;
$concurso = null;
$sorteios = [];
$numerosSorteados = [];
$jogos = [];
$pontos = 0;

// Se o número do bilhete foi informado, busca as informações
if (!empty($numeroBilhete)) {
    // Busca o bilhete
    $bilhete = buscarBilhete($numeroBilhete);
    
    if ($bilhete) {
        // Busca o concurso
        $concurso = buscarConcurso($bilhete['concurso_id']);
        
        // Busca os sorteios do concurso
        $sorteios = buscarSorteiosConcurso($bilhete['concurso_id']);
        
        // Busca os números sorteados
        $numerosSorteados = buscarNumerosSorteados($bilhete['concurso_id']);
        
        // Busca os jogos do bilhete
        $jogos = buscarJogosBilhete($bilhete['id']);
        
        // Calcula os pontos do bilhete
        $pontos = calcularPontosBilhete($bilhete['id']);
    } else {
        // Se o bilhete não foi encontrado, exibe uma mensagem
        $erro = "Bilhete não encontrado. Verifique o número e tente novamente.";
    }
} else {
    // Se o número do bilhete não foi informado, exibe uma mensagem
    $erro = "Por favor, informe o número do bilhete.";
}

// Script para inicializar o detalhe do bilhete
$footerScript = '';
if (!empty($numerosSorteados)) {
    $footerScript = 'initDetalheBilhete(' . json_encode($numerosSorteados) . ');';
}

// Inclui o cabeçalho
include 'templates/header.php';
?>

<div class="consulta-bilhete">
    <h2>Consulta de Bilhete</h2>
    <form id="consulta-form" class="consulta-form" action="consulta_bilhete.php" method="get">
        <input type="text" name="bilhete" id="numero-bilhete" class="consulta-input" placeholder="Digite o número do bilhete" value="<?php echo htmlspecialchars($numeroBilhete); ?>" required>
        <button type="submit" class="consulta-button">Consultar</button>
    </form>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($bilhete && $concurso): ?>
    <div class="bilhete-detalhe">
        <div class="bilhete-header">
            <h3>~ Consulta Online do Bilhete ~</h3>
            <p>Bilhete: <?php echo $bilhete['numero']; ?></p>
            <p><?php echo $bilhete['status']; ?></p>
        </div>
        
        <div class="bilhete-body">
            <div class="bilhete-info-row">
                <div class="bilhete-label">*Apostador:</div>
                <div class="bilhete-value"><?php echo $bilhete['apostador']; ?></div>
            </div>
            <div class="bilhete-info-row">
                <div class="bilhete-label">Cidade........:</div>
                <div class="bilhete-value"><?php echo $bilhete['cidade']; ?> - <?php echo $bilhete['estado']; ?></div>
            </div>
            <div class="bilhete-info-row">
                <div class="bilhete-label">Vendedor...:</div>
                <div class="bilhete-value"><?php echo $bilhete['vendedor']; ?></div>
            </div>
            <div class="bilhete-info-row">
                <div class="bilhete-label">Compra Realizada:</div>
                <div class="bilhete-value"><?php echo formatarData($bilhete['data_compra'], 'D, d/M/Y\-H:i:s'); ?></div>
            </div>
            
            <div class="bilhete-jogos">
                <h4>JOGO</h4>
                
                <?php if (empty($jogos)): ?>
                    <p>Nenhum jogo encontrado para este bilhete.</p>
                <?php else: ?>
                    <?php 
                    $premiado = false;
                    if ($concurso['status'] === 'finalizado') {
                        // Verifica se o bilhete foi premiado
                        $sql = "SELECT COUNT(*) as total FROM ganhadores WHERE bilhete_id = " . $bilhete['id'];
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $premiado = $row['total'] > 0;
                        }
                    }
                    ?>
                    
                    <div class="bilhete-resultado">
                        <?php if ($concurso['status'] === 'finalizado'): ?>
                            <?php if ($premiado): ?>
                                <div class="alert alert-success">
                                    <strong>Bilhete premiado!</strong>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    Bilhete não premiado!
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Concurso em andamento. Aguarde a finalização.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php foreach ($jogos as $index => $jogo): ?>
                        <?php
                        // Busca os números do jogo
                        $sql = "SELECT numero FROM numeros_bilhete WHERE jogo_id = " . $jogo['id'] . " ORDER BY id ASC";
                        $result = $conn->query($sql);
                        $numeros = [];
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $numeros[] = $row['numero'];
                            }
                        }
                        
                        // Calcula os pontos do jogo
                        $pontosJogo = 0;
                        foreach ($numeros as $numero) {
                            if (in_array($numero, $numerosSorteados)) {
                                $pontosJogo++;
                            }
                        }
                        ?>
                        
                        <div class="bilhete-jogo">
                            <div class="jogo-header">
                                <div class="jogo-numero"><?php echo $index + 1; ?>.</div>
                                <div class="jogo-pontos">
                                    <?php echo $pontosJogo; ?> Pontos
                                </div>
                            </div>
                            
                            <div class="jogo-numeros">
                                <?php foreach ($numeros as $numero): ?>
                                    <div class="jogo-numero <?php echo in_array($numero, $numerosSorteados) ? 'numero-acertado' : ''; ?>">
                                        <?php echo $numero; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="bilhete-footer">
                        <div class="bilhete-info-row">
                            <div class="bilhete-label">Valor:</div>
                            <div class="bilhete-value"><?php echo formatarDinheiro($bilhete['valor']); ?></div>
                        </div>
                        <div class="bilhete-info-row">
                            <div class="bilhete-label">Jogos:</div>
                            <div class="bilhete-value"><?php echo count($jogos); ?></div>
                        </div>
                        <div class="bilhete-info-row">
                            <div class="bilhete-label">Total:</div>
                            <div class="bilhete-value"><?php echo formatarDinheiro($bilhete['valor'] * count($jogos)); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($sorteios)): ?>
    <div class="resultados-container">
        <div class="resultado-header">
            Resultados dos Sorteios
        </div>
        <div class="resultado-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Sorteio</th>
                            <th>Data</th>
                            <th>Números Sorteados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sorteios as $sorteio): ?>
                        <tr>
                            <td><?php echo $sorteio['ordem']; ?>º Sorteio</td>
                            <td><?php echo formatarData($sorteio['data_sorteio'], 'H\h \R\I\O D, d/M/Y'); ?></td>
                            <td>
                                <?php if ($sorteio['numeros_sorteados']): ?>
                                    <div class="sorteio-numeros" style="justify-content: flex-start;">
                                        <?php
                                        $numeros = explode(',', $sorteio['numeros_sorteados']);
                                        foreach ($numeros as $numero):
                                        ?>
                                            <div class="sorteio-numero" style="margin-right: 5px;"><?php echo $numero; ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    Aguardando sorteio
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// Inclui o rodapé
include 'templates/footer.php';
?> 