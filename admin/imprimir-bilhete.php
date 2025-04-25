<?php
/**
 * Página de impressão de bilhete
 * URL: /admin/imprimir-bilhete.php?id=X
 */

// Verificar permissões
require_once 'auth.php';
requireAccess(1); // Nível mínimo: usuário comum

// Incluir modelos necessários
require_once 'models/BilheteModel.php';
require_once 'models/ConcursoModel.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: usuario/meus-bilhetes.php");
    exit;
}

// Obter ID do bilhete
$bilheteId = intval($_GET['id']);

// Obter ID do usuário logado
$usuarioId = $_SESSION['usuario_id'] ?? 0;

// Inicializar modelos
$bilheteModel = new BilheteModel();

// Obter detalhes do bilhete
$bilhete = $bilheteModel->obterDetalhesBilhete($bilheteId);

// Verificar se o bilhete existe
if (!$bilhete) {
    header("Location: usuario/meus-bilhetes.php");
    exit;
}

// Verificar se o bilhete pertence ao usuário logado ou se é um administrador
if ($bilhete['usuario_id'] != $usuarioId && !isAdmin()) {
    header("Location: usuario/meus-bilhetes.php");
    exit;
}

/**
 * Verifica se o usuário é um administrador
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] >= 3;
}

// Formatar dezenas
$dezenas = [];
if (!empty($bilhete['dezenas'])) {
    $dezenas = explode(',', $bilhete['dezenas']);
}

// Formatar datas
$dataCompra = date('d/m/Y H:i', strtotime($bilhete['data_compra']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilhete #<?php echo $bilhete['numero']; ?> - Impressão</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.4;
        }
        .page {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .receipt {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 4px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .logo {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px;
        }
        .subtitle {
            font-size: 12pt;
            margin-top: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            margin-right: 10px;
        }
        .value {
            flex: 1;
        }
        .numbers {
            margin: 15px 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        .number {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #aaa;
            border-radius: 50%;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
        }
        .barcode img {
            max-width: 100%;
            height: 60px;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
        }
        .status-aguardando {
            background-color: #f0f0f0;
            color: #555;
        }
        .status-premiado {
            background-color: #d4edda;
            color: #155724;
        }
        .status-nao_premiado {
            background-color: #f8d7da;
            color: #721c24;
        }
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</head>
<body>
    <div class="page">
        <div class="receipt">
            <div class="header">
                <div class="logo">BOLÃO</div>
                <div>COMPROVANTE DE APOSTA</div>
            </div>
            
            <div class="title">BILHETE #<?php echo $bilhete['numero']; ?></div>
            <div class="subtitle">Concurso: <?php echo $bilhete['concurso_nome']; ?> (#<?php echo $bilhete['concurso_numero']; ?>)</div>
            
            <div style="margin: 20px 0;">
                <div class="info-row">
                    <div class="label">Data da Compra:</div>
                    <div class="value"><?php echo $dataCompra; ?></div>
                </div>
                <div class="info-row">
                    <div class="label">Apostador:</div>
                    <div class="value"><?php echo $bilhete['usuario_nome']; ?></div>
                </div>
                <div class="info-row">
                    <div class="label">Valor da Aposta:</div>
                    <div class="value">R$ <?php echo number_format($bilhete['valor'], 2, ',', '.'); ?></div>
                </div>
                <div class="info-row">
                    <div class="label">Status:</div>
                    <div class="value">
                        <span class="status status-<?php echo $bilhete['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $bilhete['status'])); ?>
                        </span>
                    </div>
                </div>
                <?php if (isset($bilhete['valor_premio']) && $bilhete['valor_premio'] > 0): ?>
                <div class="info-row">
                    <div class="label">Valor do Prêmio:</div>
                    <div class="value">R$ <?php echo number_format($bilhete['valor_premio'], 2, ',', '.'); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="title">NÚMEROS APOSTADOS</div>
            <div class="numbers">
                <?php foreach ($dezenas as $dezena): ?>
                <div class="number"><?php echo trim($dezena); ?></div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($bilhete['jogos'])): ?>
            <div class="title">JOGOS</div>
            <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Tipo</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Números</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bilhete['jogos'] as $jogo): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $jogo['tipo']; ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $jogo['numeros']; ?></td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><?php echo $jogo['resultado'] ?? '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <div class="barcode">
                <img src="assets/img/barcode.png" alt="Código de Barras">
                <div><?php echo $bilhete['numero']; ?></div>
            </div>
            
            <div class="footer">
                <p>Este comprovante é válido apenas com autenticação. Guarde-o em local seguro.</p>
                <p>Impresso em: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        </div>
        
        <div class="no-print">
            <button class="btn" onclick="window.print();">Imprimir</button>
            <a href="usuario/meus-bilhetes.php" class="btn" style="background-color: #6c757d;">Voltar</a>
        </div>
    </div>
</body>
</html> 