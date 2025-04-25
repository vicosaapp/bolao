<?php
// Script para corrigir as imagens do slider

// Definindo o diretório e arquivos
$imageDir = 'assets/img/slider/';
$images = [
    'slide1.jpg',
    'slide2.jpg',
    'slide3.jpg'
];

// Criando o diretório de backup se não existir
$backupDir = $imageDir . 'backup/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "Diretório de backup criado: {$backupDir}<br>";
}

// Função para corrigir uma imagem
function fixImage($imageFile, $backupDir) {
    $fullPath = $imageFile;
    $filename = basename($imageFile);
    
    echo "<h2>Verificando: {$filename}</h2>";
    
    // Verifica se o arquivo existe
    if (!file_exists($fullPath)) {
        echo "<p style='color:red'>Arquivo não encontrado: {$fullPath}</p>";
        return false;
    }
    
    // Verifica se o arquivo é uma imagem válida
    $imgInfo = @getimagesize($fullPath);
    if ($imgInfo === false) {
        echo "<p style='color:red'>Arquivo não é uma imagem válida: {$fullPath}</p>";
        return false;
    }
    
    // Faz backup da imagem original
    $backupFile = $backupDir . $filename;
    if (!file_exists($backupFile)) {
        if (copy($fullPath, $backupFile)) {
            echo "<p>Backup criado: {$backupFile}</p>";
        } else {
            echo "<p style='color:red'>Erro ao criar backup!</p>";
        }
    }
    
    // Carrega a imagem original
    $image = null;
    $mime = $imgInfo['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($fullPath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($fullPath);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($fullPath);
            break;
        default:
            echo "<p style='color:red'>Formato de imagem não suportado: {$mime}</p>";
            return false;
    }
    
    if (!$image) {
        echo "<p style='color:red'>Erro ao carregar a imagem!</p>";
        return false;
    }
    
    // Obtém dimensões
    $width = imagesx($image);
    $height = imagesy($image);
    echo "<p>Dimensões originais: {$width}x{$height} pixels</p>";
    
    // Salva a imagem em um novo arquivo JPEG
    $newFilename = $fullPath;
    if (imagejpeg($image, $newFilename, 90)) {
        echo "<p style='color:green'>Imagem corrigida e salva com sucesso!</p>";
        
        // Exibe a imagem corrigida
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<img src='{$newFilename}?v=" . time() . "' alt='{$filename}' style='max-width:100%; max-height:400px;'>";
        echo "</div>";
        
        imagedestroy($image);
        return true;
    } else {
        echo "<p style='color:red'>Erro ao salvar a imagem corrigida!</p>";
        imagedestroy($image);
        return false;
    }
}

// HTML header
echo '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção de Imagens do Slider</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #0072c6;
            border-bottom: 2px solid #0072c6;
            padding-bottom: 10px;
        }
        h2 {
            margin-top: 30px;
            border-left: 4px solid #0072c6;
            padding-left: 10px;
        }
        div {
            margin-bottom: 15px;
        }
        p {
            margin: 5px 0;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Correção de Imagens do Slider</h1>';

// Processa cada imagem
foreach ($images as $image) {
    $imageFile = $imageDir . $image;
    fixImage($imageFile, $backupDir);
    echo "<hr>";
}

// Link para voltar à página principal
echo '<div style="margin-top:30px;">
    <a href="index.php" style="display:inline-block; padding:10px 20px; background-color:#0072c6; color:white; text-decoration:none; border-radius:4px;">Voltar para a página principal</a>
</div>';

// HTML footer
echo '</body>
</html>';
?> 