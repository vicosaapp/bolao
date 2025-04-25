<?php
// Arquivo de teste para as imagens do slider
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Imagens do Slider</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .image-container {
            margin-bottom: 30px;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
        }
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 10px;
        }
        h2 {
            color: #0072c6;
        }
    </style>
</head>
<body>
    <h1>Teste de Imagens do Slider</h1>
    
    <div class="image-container">
        <h2>Imagem 1 (slide1.jpg)</h2>
        <img src="assets/img/slider/slide1.jpg?v=<?= time() ?>" alt="Slide 1">
        <p>Se esta imagem está visível, slide1.jpg está funcionando corretamente.</p>
    </div>
    
    <div class="image-container">
        <h2>Imagem 2 (slide2.jpg)</h2>
        <img src="assets/img/slider/slide2.jpg?v=<?= time() ?>" alt="Slide 2">
        <p>Se esta imagem está visível, slide2.jpg está funcionando corretamente.</p>
    </div>
    
    <div class="image-container">
        <h2>Imagem 3 (slide3.jpg)</h2>
        <img src="assets/img/slider/slide3.jpg?v=<?= time() ?>" alt="Slide 3">
        <p>Se esta imagem está visível, slide3.jpg está funcionando corretamente.</p>
    </div>
    
    <script>
        // Verifica se as imagens carregaram corretamente
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            
            images.forEach((img, index) => {
                img.addEventListener('error', function() {
                    console.error(`Erro ao carregar a imagem ${index + 1}`);
                    this.parentNode.style.backgroundColor = '#ffdddd';
                    this.parentNode.innerHTML += '<p style="color: red;"><strong>ERRO:</strong> Esta imagem não pode ser carregada. Verifique o arquivo.</p>';
                });
                
                if (img.complete) {
                    if (img.naturalHeight === 0) {
                        console.error(`Imagem ${index + 1} não carregou corretamente`);
                        img.parentNode.style.backgroundColor = '#ffdddd';
                        img.parentNode.innerHTML += '<p style="color: red;"><strong>ERRO:</strong> Esta imagem não carregou corretamente.</p>';
                    } else {
                        console.log(`Imagem ${index + 1} carregada: ${img.naturalWidth}x${img.naturalHeight}`);
                    }
                }
            });
        });
    </script>
</body>
</html> 