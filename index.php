<?php
// Configurações da página
$pageTitle = "Página Inicial";
$pageDescription = "Participe do Bolão 10 e tenha a chance de ganhar grandes prêmios!";
$pageKeywords = "bolão, sorteio, prêmios, loteria";
$pageImage = "asset/img/social-share.jpg";

// Inclui as funções e a conexão com o banco de dados
require_once 'includes/functions.php';
require_once 'includes/Database.php';

// Buscar concursos ativos do banco de dados
$temConcursoAtivo = false;
$concursosAtivos = [];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Buscar concursos ativos (status = 'em_andamento' e data_fim maior que hoje)
    $dataAtual = date('Y-m-d');
    $sql = "SELECT id, nome, valor_premiacao, data_fim, status FROM concursos 
            WHERE status = 'em_andamento' 
            AND data_fim > ? 
            ORDER BY data_fim ASC";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $dataAtual);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $temConcursoAtivo = true;
            while ($row = $result->fetch_assoc()) {
                $concursosAtivos[] = $row;
            }
        }
    } else {
        // Fallback para consulta direta se prepared statement falhar
        $sqlDireto = "SELECT id, nome, valor_premiacao, data_fim, status FROM concursos 
                      WHERE status = 'em_andamento' 
                      AND data_fim > '$dataAtual' 
                      ORDER BY data_fim ASC";
        
        $resultDireto = $conn->query($sqlDireto);
        
        if ($resultDireto && $resultDireto->num_rows > 0) {
            $temConcursoAtivo = true;
            while ($row = $resultDireto->fetch_assoc()) {
                $concursosAtivos[] = $row;
            }
        }
    }
    
    // Buscar o último concurso finalizado e seus sorteios
    $ultimoConcurso = null;
    $ultimosSorteios = [];
    
    // Primeiro tenta com prepared statement
    $sqlUltimoConcurso = "SELECT id, nome FROM concursos 
                        WHERE status = 'finalizado' 
                        ORDER BY data_fim DESC LIMIT 1";
    
    $resultUltimoConcurso = $conn->query($sqlUltimoConcurso);
    
    if ($resultUltimoConcurso && $resultUltimoConcurso->num_rows > 0) {
        $ultimoConcurso = $resultUltimoConcurso->fetch_assoc();
        
        // Buscar os sorteios do último concurso
        $idUltimoConcurso = (int)$ultimoConcurso['id'];
        
        $sqlSorteios = "SELECT id, ordem, descricao, data FROM sorteios 
                        WHERE id_concurso = ? 
                        ORDER BY ordem ASC";
        
        $stmt = $conn->prepare($sqlSorteios);
        
        if ($stmt) {
            $stmt->bind_param("i", $idUltimoConcurso);
            $stmt->execute();
            $resultSorteios = $stmt->get_result();
            
            if ($resultSorteios) {
                while ($sorteio = $resultSorteios->fetch_assoc()) {
                    // Buscar os números do sorteio
                    $idSorteio = (int)$sorteio['id'];
                    $sqlNumeros = "SELECT numero FROM numeros_sorteados 
                                WHERE id_sorteio = ? 
                                ORDER BY posicao ASC";
                    
                    $stmtNumeros = $conn->prepare($sqlNumeros);
                    
                    if ($stmtNumeros) {
                        $stmtNumeros->bind_param("i", $idSorteio);
                        $stmtNumeros->execute();
                        $resultNumeros = $stmtNumeros->get_result();
                        
                        $numeros = [];
                        if ($resultNumeros) {
                            while ($numero = $resultNumeros->fetch_assoc()) {
                                $numeros[] = $numero['numero'];
                            }
                        }
                        
                        $sorteio['numeros'] = $numeros;
                        $ultimosSorteios[] = $sorteio;
                    }
                }
            }
        } else {
            // Fallback para consulta direta se prepared statement falhar
            $sqlSorteiosDireto = "SELECT id, ordem, descricao, data FROM sorteios 
                                WHERE id_concurso = $idUltimoConcurso 
                                ORDER BY ordem ASC";
            
            $resultSorteiosDireto = $conn->query($sqlSorteiosDireto);
            
            if ($resultSorteiosDireto && $resultSorteiosDireto->num_rows > 0) {
                while ($sorteio = $resultSorteiosDireto->fetch_assoc()) {
                    $idSorteio = (int)$sorteio['id'];
                    $sqlNumerosDireto = "SELECT numero FROM numeros_sorteados 
                                        WHERE id_sorteio = $idSorteio 
                                        ORDER BY posicao ASC";
                    
                    $resultNumerosDireto = $conn->query($sqlNumerosDireto);
                    
                    $numeros = [];
                    if ($resultNumerosDireto && $resultNumerosDireto->num_rows > 0) {
                        while ($numero = $resultNumerosDireto->fetch_assoc()) {
                            $numeros[] = $numero['numero'];
                        }
                    }
                    
                    $sorteio['numeros'] = $numeros;
                    $ultimosSorteios[] = $sorteio;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Erro ao buscar dados para a página inicial: " . $e->getMessage());
}

// Inclui o cabeçalho
include 'templates/header1.php';
?>

<style>
    .consulta-bilhete {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 20px 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 30px auto;
        max-width: 800px;
        text-align: center;
    }

    .consulta-bilhete h2 {
        color: #0072c6;
        margin-bottom: 15px;
        font-size: 24px;
    }

    .consulta-form {
        display: flex;
        max-width: 600px;
        margin: 0 auto;
    }

    .consulta-input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px 0 0 5px;
        font-size: 16px;
    }

    .consulta-button {
        background-color: #0072c6;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 0 5px 5px 0;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .consulta-button:hover {
        background-color: #005ea3;
    }

    .concurso-info {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 30px auto;
        max-width: 800px;
    }

    .concurso-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .concurso-title {
        color: #0072c6;
        margin: 0;
        font-size: 24px;
    }

    .concurso-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
    }

    .status-aberto {
        background-color: #28a745;
        color: white;
    }

    .social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }

    .social-button {
        background-color: #0072c6;
        color: white;
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.3s;
    }

    .social-button i {
        margin-right: 8px;
    }

    .social-button:hover {
        background-color: #005ea3;
        color: white;
    }

    .premios-container {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 30px auto;
        max-width: 800px;
    }

    .premios-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .premios-header h2 {
        color: #0072c6;
        margin: 0;
        font-size: 24px;
    }

    .premio-valor {
        font-weight: bold;
        color: #28a745;
        font-size: 18px;
    }

    .sorteios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .sorteio-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 15px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .sorteio-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .sorteio-title {
        font-weight: bold;
        color: #0072c6;
    }

    .sorteio-numeros {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }

    .sorteio-numero {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #0072c6;
        color: white;
        border-radius: 50%;
        font-weight: bold;
    }

    .ver-mais-btn {
        background-color: #0072c6;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        display: inline-block;
        margin-top: 20px;
        transition: background-color 0.3s;
    }

    .ver-mais-btn:hover {
        background-color: #005ea3;
        color: white;
    }

    .text-center {
        text-align: center;
    }

    .top-bilhetes-container {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 30px auto;
        max-width: 800px;
    }

    .top-bilhetes-header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }

    .top-bilhetes-header h2 {
        color: #0072c6;
        margin-bottom: 5px;
    }

    .bilhetes-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .bilhete-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background-color: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .bilhete-numero {
        font-weight: bold;
        color: #0072c6;
    }

    .bilhete-info {
        flex: 1;
        margin-left: 15px;
        text-align: left;
    }

    .bilhete-pontos {
        background-color: #28a745;
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
    }

    /* Estilos responsivos */
    @media (max-width: 768px) {
        .consulta-form {
            flex-direction: column;
        }

        .consulta-input, .consulta-button {
            width: 100%;
            border-radius: 5px;
        }

        .consulta-button {
            margin-top: 10px;
        }

        .social-links {
            justify-content: center;
        }

        .sorteios-grid {
            grid-template-columns: 1fr;
        }

        .bilhete-row {
            flex-direction: column;
            text-align: center;
            gap: 5px;
        }

        .bilhete-info {
            margin-left: 0;
            text-align: center;
        }
    }
</style>

<!-- Slider Rotativo - Versão Simplificada -->
<section class="slider-section">
    <div class="slider-container">
        <!-- Slides -->
        <div id="slide1" class="slide active">
            <div class="slide-image" style="background-image: url('asset/img/slider/slide1.jpg?v=<?= time() ?>')"></div>
            <div class="container">
                <div class="slide-content">
                    <h2>Bem-vindo ao Bolão 10</h2>
                    <p>Participe do maior bolão do Brasil e concorra a prêmios incríveis!</p>
                    <a href="comprar_bilhete.php" class="slide-button">Apostar Agora</a>
                </div>
            </div>
        </div>
        
        <div id="slide2" class="slide">
            <div class="slide-image" style="background-image: url('asset/img/slider/slide2.jpg?v=<?= time() ?>')"></div>
            <div class="container">
                <div class="slide-content">
                    <h2>Prêmios Especiais</h2>
                    <p>Nossos sorteios oferecem prêmios exclusivos e chances reais de ganhar!</p>
                    <a href="premios.php" class="slide-button">Ver Prêmios</a>
                </div>
            </div>
        </div>
        
        <div id="slide3" class="slide">
            <div class="slide-image" style="background-image: url('asset/img/slider/slide3.jpg?v=<?= time() ?>')"></div>
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
<?php if ($temConcursoAtivo): ?>
    <?php foreach ($concursosAtivos as $index => $concurso): ?>
        <?php if ($index === 0): // Exibir apenas o primeiro concurso ativo ?>
            <div class="concurso-info">
                <div class="concurso-header">
                    <h2 class="concurso-title"><?= htmlspecialchars($concurso['nome']) ?></h2>
                    <span class="concurso-status status-aberto">Aberto</span>
                </div>
                
                <p>O próximo sorteio acontecerá em breve. Não perca essa oportunidade!</p>
                <p><strong>Valor premiação:</strong> R$ <?= number_format($concurso['valor_premiacao'], 2, ',', '.') ?></p>
                
                <div class="social-links">
                    <a href="comprar_bilhete.php?id=<?= (int)$concurso['id'] ?>" class="social-button"><i class="fab fa-whatsapp"></i> Adquira seu bilhete</a>
                    <a href="calendario.php" class="social-button"><i class="fas fa-calendar-alt"></i> Ver programação</a>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
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
<?php if ($ultimoConcurso && !empty($ultimosSorteios)): ?>
<div class="premios-container">
    <div class="premios-header">
        <h2>Último Resultado</h2>
        <span class="premio-valor"><?= htmlspecialchars($ultimoConcurso['nome']) ?></span>
    </div>
    
    <div class="sorteios-grid">
        <?php foreach ($ultimosSorteios as $sorteio): ?>
            <div class="sorteio-card card-hover">
                <div class="sorteio-header">
                    <span class="sorteio-title"><?= $sorteio["ordem"] ?>º Sorteio - <?= htmlspecialchars($sorteio["descricao"]) ?></span>
                    <span><?= date('d/m/Y', strtotime($sorteio["data"])) ?></span>
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
<?php endif; ?>

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