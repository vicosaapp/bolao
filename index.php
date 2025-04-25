<?php
// Configurações da página
$pageTitle = "Página Inicial";
$pageDescription = "Participe do Bolão 10 e tenha a chance de ganhar grandes prêmios!";
$pageKeywords = "bolão, sorteio, prêmios, loteria";
$pageImage = "assets/img/social-share.jpg";

// Inclui as funções e a conexão com o banco de dados
require_once 'includes/functions.php';

// Inclui o cabeçalho
include 'templates/header.php';
?>

<!-- Slider Rotativo - Versão Simplificada -->
<section class="slider-section">
    <div class="slider-container">
        <!-- Slides -->
        <div id="slide1" class="slide active">
            <div class="slide-image" style="background-image: url('assets/img/slider/slide1.jpg?v=<?= time() ?>')"></div>
            <div class="container">
                <div class="slide-content">
                    <h2>Bem-vindo ao Bolão 10</h2>
                    <p>Participe do maior bolão do Brasil e concorra a prêmios incríveis!</p>
                    <a href="comprar_bilhete.php" class="slide-button">Apostar Agora</a>
                </div>
            </div>
        </div>
        
        <div id="slide2" class="slide">
            <div class="slide-image" style="background-image: url('assets/img/slider/slide2.jpg?v=<?= time() ?>')"></div>
            <div class="container">
                <div class="slide-content">
                    <h2>Prêmios Especiais</h2>
                    <p>Nossos sorteios oferecem prêmios exclusivos e chances reais de ganhar!</p>
                    <a href="premios.php" class="slide-button">Ver Prêmios</a>
                </div>
            </div>
        </div>
        
        <div id="slide3" class="slide">
            <div class="slide-image" style="background-image: url('assets/img/slider/slide3.jpg?v=<?= time() ?>')"></div>
            <div class="container">
                <div class="slide-content">
                    <h2>Próximo Sorteio</h2>
                    <p>Não perca o próximo sorteio! Compre seu bilhete hoje mesmo.</p>
                    <a href="calendario.php" class="slide-button">Ver Calendário</a>
                </div>
            </div>
        </div>
        
        <!-- Navegação do Slider -->
        <div class="slider-dots">
            <button class="slider-dot active" onclick="changeSlide(1)"></button>
            <button class="slider-dot" onclick="changeSlide(2)"></button>
            <button class="slider-dot" onclick="changeSlide(3)"></button>
        </div>
        
        <!-- Botões de Navegação -->
        <button class="slider-prev" onclick="prevSlide()">❮</button>
        <button class="slider-next" onclick="nextSlide()">❯</button>
    </div>
</section>

<!-- Script simples para o slider -->
<script>
    let currentSlide = 1;
    const totalSlides = 3;
    let slideInterval;
    
    // Função para mudar slides
    function changeSlide(slideNumber) {
        // Remove a classe active de todos os slides e dots
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Adiciona a classe active ao slide e dot selecionados
        document.getElementById('slide' + slideNumber).classList.add('active');
        dots[slideNumber - 1].classList.add('active');
        
        // Atualiza slide atual
        currentSlide = slideNumber;
        
        // Reseta o intervalo
        resetAutoSlide();
    }
    
    // Função para navegação
    function nextSlide() {
        let nextSlideIndex = currentSlide + 1;
        if (nextSlideIndex > totalSlides) nextSlideIndex = 1;
        changeSlide(nextSlideIndex);
    }
    
    function prevSlide() {
        let prevSlideIndex = currentSlide - 1;
        if (prevSlideIndex < 1) prevSlideIndex = totalSlides;
        changeSlide(prevSlideIndex);
    }
    
    // Inicia rotação automática
    function startAutoSlide() {
        stopAutoSlide();
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    // Para rotação automática
    function stopAutoSlide() {
        clearInterval(slideInterval);
    }
    
    // Reseta a rotação automática
    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }
    
    // Inicia o slider
    document.addEventListener('DOMContentLoaded', function() {
        // Configura o primeiro slide
        changeSlide(1);
        
        // Inicia a rotação automática
        startAutoSlide();
        
        // Eventos para pausar/retomar a rotação ao passar o mouse
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoSlide);
            sliderContainer.addEventListener('mouseleave', startAutoSlide);
        }
        
        // Adiciona suporte para swipe em dispositivos móveis
        let touchStartX = 0;
        let touchEndX = 0;
        
        sliderContainer.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoSlide();
        }, { passive: true });
        
        sliderContainer.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoSlide();
        }, { passive: true });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            if (touchEndX < touchStartX - swipeThreshold) {
                // Swipe para a esquerda
                nextSlide();
            } else if (touchEndX > touchStartX + swipeThreshold) {
                // Swipe para a direita
                prevSlide();
            }
        }
    });
</script>

<!-- Consulta de Bilhete -->
<div class="consulta-bilhete">
    <h2><i class="fas fa-search"></i> Consultar Bilhete</h2>
    <form action="consultar-bilhete.php" method="get" class="consulta-form">
        <input type="text" name="numero" placeholder="Digite o número do bilhete" class="consulta-input" required>
        <button type="submit" class="consulta-button">Consultar</button>
    </form>
</div>

<!-- Concursos ativos -->
<?php
// Simulação de concurso ativo (em um cenário real, viria do banco de dados)
$temConcursoAtivo = false;
?>

<?php if ($temConcursoAtivo): ?>
    <div class="concurso-info">
        <div class="concurso-header">
            <h2 class="concurso-title">Bolão da Sorte #308</h2>
            <span class="concurso-status status-aberto">Aberto</span>
        </div>
        
        <p>O próximo sorteio acontecerá em breve. Não perca essa oportunidade!</p>
        <p><strong>Valor premiação:</strong> R$ 45.000,00</p>
        
        <div class="social-links">
            <a href="#" class="social-button"><i class="fab fa-whatsapp"></i> Adquira seu bilhete</a>
            <a href="#" class="social-button"><i class="fas fa-calendar-alt"></i> Ver programação</a>
        </div>
    </div>
<?php else: ?>
    <div class="concurso-info">
        <div class="concurso-header">
            <h2 class="concurso-title">Nenhum concurso disponível no momento</h2>
        </div>
        
        <p>Por favor, volte mais tarde para verificar os próximos concursos.</p>
        <p>Enquanto isso, você pode consultar os resultados dos concursos anteriores.</p>
        
        <div class="social-links">
            <a href="#" class="social-button"><i class="fab fa-whatsapp"></i> Fale conosco</a>
            <a href="resultados.php" class="social-button"><i class="fas fa-trophy"></i> Ver resultados anteriores</a>
        </div>
    </div>
<?php endif; ?>

<!-- Último resultado -->
<div class="premios-container">
    <div class="premios-header">
        <h2>Último Resultado</h2>
        <span class="premio-valor">Bolão #307</span>
    </div>
    
    <div class="sorteios-grid">
        <?php
        // Em um cenário real, esses dados viriam do banco de dados
        $sorteios = [
            [
                "ordem" => 1,
                "descricao" => "21h RIO",
                "data" => "12/03/2025",
                "numeros" => [18, 12, 81, 12, 24, 35, 83, 7, 8, 88]
            ],
            [
                "ordem" => 2,
                "descricao" => "09h RIO",
                "data" => "13/03/2025",
                "numeros" => [35, 61, 72, 42, 25, 36, 71, 21, 21, 88]
            ],
            [
                "ordem" => 3,
                "descricao" => "11h RIO",
                "data" => "13/03/2025",
                "numeros" => [17, 30, 64, 35, 3, 2, 71, 30, 9, 16]
            ]
        ];
        
        foreach ($sorteios as $sorteio): ?>
            <div class="sorteio-card card-hover">
                <div class="sorteio-header">
                    <span class="sorteio-title"><?= $sorteio["ordem"] ?>º Sorteio - <?= $sorteio["descricao"] ?></span>
                    <span><?= $sorteio["data"] ?></span>
                </div>
                <div class="sorteio-numeros">
                    <?php foreach ($sorteio["numeros"] as $numero): ?>
                        <div class="sorteio-numero"><?= $numero ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center" style="margin-top: 20px;">
        <a href="resultados.php" class="ver-mais-btn">Ver todos os resultados</a>
    </div>
</div>

<!-- Top Bilhetes -->
<div class="top-bilhetes-container">
    <div class="top-bilhetes-header">
        <h2>Top Bilhetes - Concurso #307</h2>
        <p>Os bilhetes com maior pontuação no último concurso</p>
    </div>
    
    <div class="bilhetes-list">
        <div class="bilhete-row">
            <div class="bilhete-numero">8741891</div>
            <div class="bilhete-info">Gilson Santos - Salvador/BA</div>
            <div class="bilhete-pontos">10 pts</div>
        </div>
        <div class="bilhete-row">
            <div class="bilhete-numero">8716846</div>
            <div class="bilhete-info">Orlando Dias da Silva - Alagoinhas/BA</div>
            <div class="bilhete-pontos">9 pts</div>
        </div>
        <div class="bilhete-row">
            <div class="bilhete-numero">8745949</div>
            <div class="bilhete-info">Gratidão - Araguari/MG</div>
            <div class="bilhete-pontos">9 pts</div>
        </div>
        <div class="bilhete-row">
            <div class="bilhete-numero">8749737</div>
            <div class="bilhete-info">Máfia do Guincho - Barueri/SP</div>
            <div class="bilhete-pontos">9 pts</div>
        </div>
    </div>
    
    <div class="text-center" style="margin-top: 20px;">
        <a href="ranking.php" class="ver-mais-btn">Ver ranking completo</a>
    </div>
</div>

<!-- Botão de voltar ao topo -->
<a href="#" class="back-to-top" data-tooltip="Voltar ao topo">
    <i class="fas fa-chevron-up"></i>
</a>

<!-- Script para tratamento de erros de imagem -->
<script>
    // Script para lidar com erros de carregamento de imagens
    document.addEventListener('DOMContentLoaded', function() {
        const sliderImages = document.querySelectorAll('.slide-image');
        
        sliderImages.forEach((img, index) => {
            // Adiciona tratamento de erro às imagens
            img.onerror = function() {
                console.error(`Erro ao carregar imagem do slide ${index + 1}`);
                this.src = `https://via.placeholder.com/1920x1080/0072c6/ffffff?text=Slide+${index + 1}`;
                this.classList.add('error');
            };
            
            // Verifica se a imagem já está com erro
            if (img.complete && img.naturalHeight === 0) {
                img.src = `https://via.placeholder.com/1920x1080/0072c6/ffffff?text=Slide+${index + 1}`;
                img.classList.add('error');
            }
        });
    });
</script>

<!-- Scripts complementares -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicialização do slider
        let currentSlide = 0;
        const slides = document.querySelectorAll(".slide");
        const dots = document.querySelectorAll(".slider-dot");
        const totalSlides = slides.length;
        
        // Função para exibir um slide específico
        function showSlide(n) {
            // Normaliza o índice do slide
            if (n >= totalSlides) {
                currentSlide = 0;
            } else if (n < 0) {
                currentSlide = totalSlides - 1;
            } else {
                currentSlide = n;
            }
            
            // Esconde todos os slides
            for (let i = 0; i < totalSlides; i++) {
                slides[i].classList.remove('active');
            }
            
            // Remove a classe active de todos os dots
            for (let i = 0; i < dots.length; i++) {
                dots[i].classList.remove("active");
            }
            
            // Exibe o slide atual e ativa o dot correspondente
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add("active");
        }
        
        // Função para navegar para o próximo slide
        function nextSlide() {
            showSlide(currentSlide + 1);
        }
        
        // Função para navegar para o slide anterior
        function prevSlide() {
            showSlide(currentSlide - 1);
        }
        
        // Adiciona eventos para os botões de navegação
        document.querySelector(".slider-btn-prev").addEventListener("click", prevSlide);
        document.querySelector(".slider-btn-next").addEventListener("click", nextSlide);
        
        // Adiciona eventos para os dots de navegação
        for (let i = 0; i < dots.length; i++) {
            dots[i].addEventListener("click", function() {
                showSlide(i);
            });
        }
        
        // Inicia o slider
        showSlide(0);
        
        // Configura a transição automática a cada 5 segundos
        setInterval(nextSlide, 5000);
        
        // Configura o botão "Voltar ao topo"
        const backToTopBtn = document.querySelector(".back-to-top");
        
        window.addEventListener("scroll", function() {
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                backToTopBtn.classList.add("visible");
            } else {
                backToTopBtn.classList.remove("visible");
            }
        });
        
        backToTopBtn.addEventListener("click", function() {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    });
</script>

<?php
// Inclui o rodapé
include 'templates/footer.php';
?> 