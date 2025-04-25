<?php
// Script de verificação de imagens para o slider

$images = [
    'assets/img/slider/slide1.jpg',
    'assets/img/slider/slide2.jpg',
    'assets/img/slider/slide3.jpg'
];

echo '<h1>Verificação de Imagens do Slider</h1>';

foreach ($images as $index => $image) {
    echo "<h2>Imagem {$index + 1}: {$image}</h2>";
    
    // Verifica se o arquivo existe
    if (file_exists($image)) {
        echo "<p style='color:green'>✅ Arquivo existe</p>";
        
        // Verifica o tamanho e as permissões
        $size = filesize($image);
        $perms = substr(sprintf('%o', fileperms($image)), -4);
        
        echo "<p>Tamanho: " . number_format($size / 1024, 2) . " KB</p>";
        echo "<p>Permissões: {$perms}</p>";
        
        // Tenta obter as dimensões da imagem
        $imgInfo = getimagesize($image);
        if ($imgInfo !== false) {
            echo "<p>Dimensões: {$imgInfo[0]} x {$imgInfo[1]} pixels</p>";
            echo "<p>Tipo: " . image_type_to_mime_type($imgInfo[2]) . "</p>";
        } else {
            echo "<p style='color:red'>❌ Não foi possível obter informações da imagem</p>";
        }
        
        // Exibe a imagem
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<img src='{$image}?v=" . time() . "' alt='Slide " . ($index + 1) . "' style='max-width:500px; max-height:300px;'>";
        echo "</div>";
    } else {
        echo "<p style='color:red'>❌ Arquivo não existe</p>";
    }
    
    echo "<hr>";
}

// Teste de caminhos para depuração
echo "<h2>Informações do Servidor</h2>";
echo "<p>Diretório atual: " . getcwd() . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
?> 