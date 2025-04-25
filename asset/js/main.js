// Variáveis globais
let slideIndex = 1;
let slideInterval;
const autoRotateTime = 5000; // Tempo de rotação do slider em milissegundos
const backToTopBtn = document.getElementById("backToTop");

// Inicialização
document.addEventListener("DOMContentLoaded", function() {
    // Pré-carrega as imagens do slider
    preloadSliderImages();
    
    // Inicializa o slider
    initSlider();
    
    // Event listener para o botão "Voltar ao topo"
    // window.addEventListener("scroll", scrollFunction);
    if (backToTopBtn) {
        // backToTopBtn.addEventListener("click", backToTop);
    }
    
    // Adiciona classe para indicar que o JavaScript está ativo
    document.body.classList.add('js-enabled');
    
    initTooltips();
    initBackToTop();
    highlightCurrentPage();
    
    // Menu Mobile
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileMenuToggle && mobileNav) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            
            // Anima as barras do botão
            const spans = this.querySelectorAll('span');
            spans.forEach(span => span.classList.toggle('active'));
            
            // Adiciona classe ao botão para animação
            this.classList.toggle('active');
        });
    }
    
    // Fecha o menu ao clicar em um link
    const mobileNavLinks = document.querySelectorAll('.mobile-nav a');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            mobileMenuToggle.classList.remove('active');
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans.forEach(span => span.classList.remove('active'));
        });
    });
});

// Função para pré-carregar as imagens do slider
function preloadSliderImages() {
    const slideImages = document.querySelectorAll('.slide-image');
    const imageUrls = [];
    
    slideImages.forEach((img, index) => {
        if (img.src) {
            const imageUrl = img.src;
            imageUrls.push(imageUrl);
            
            // Cria um novo objeto de imagem para pré-carregar
            const preloadImage = new Image();
            preloadImage.src = imageUrl;
            
            // Adiciona evento para detecção de erros
            preloadImage.onerror = function() {
                console.error(`Erro ao carregar imagem ${index + 1}: ${imageUrl}`);
                // Tenta usar uma imagem de fallback
                img.src = `https://via.placeholder.com/1920x1080/0072c6/ffffff?text=Slide+${index + 1}`;
            };
            
            // Verifica se a imagem já está em erro
            if (img.complete && img.naturalHeight === 0) {
                console.error(`Imagem ${index + 1} não carregou corretamente. Usando fallback.`);
                img.src = `https://via.placeholder.com/1920x1080/0072c6/ffffff?text=Slide+${index + 1}`;
            }
        }
    });
    
    console.log('Pré-carregando imagens do slider:', imageUrls.length, 'imagens');
}

// Função para iniciar a rotação automática
function startSlideRotation() {
    pauseSlideRotation(); // Limpa qualquer intervalo existente
    slideInterval = setInterval(() => {
        plusSlides(1);
    }, autoRotateTime);
}

// Função para pausar a rotação automática
function pauseSlideRotation() {
    clearInterval(slideInterval);
}

// Função para resetar a rotação automática
function resetSlideRotation() {
    pauseSlideRotation();
    startSlideRotation();
}

// Função para mudar slides
function plusSlides(n) {
    showSlides(slideIndex += n);
}

// Função para definir slide atual
function currentSlide(n) {
    showSlides(slideIndex = n);
}

// Função principal para mostrar os slides
function showSlides(n) {
    const slides = document.querySelectorAll(".slide");
    const dots = document.querySelectorAll(".slider-dot");
    
    if (!slides.length) {
        console.error("Nenhum slide encontrado!");
        return;
    }
    
    // Ajusta o índice se estiver fora dos limites
    if (n > slides.length) slideIndex = 1;
    if (n < 1) slideIndex = slides.length;
    
    // Remove a classe "active" de todos os slides e dots
    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
        slides[i].style.display = "none";
        slides[i].style.opacity = "0";
        slides[i].style.zIndex = "1";
    }
    
    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    // Garante que todas as imagens estejam visíveis
    const allImages = document.querySelectorAll('.slide-image');
    allImages.forEach(img => {
        img.style.opacity = "1";
        img.style.display = "block";
        img.style.visibility = "visible";
    });
    
    // Mostra o slide atual e ativa o dot correspondente
    if (slides[slideIndex - 1]) {
        slides[slideIndex - 1].style.display = "block";
        slides[slideIndex - 1].style.opacity = "1";
        slides[slideIndex - 1].style.zIndex = "2";
        slides[slideIndex - 1].classList.add("active");
        
        // Verifica se a imagem dentro do slide está carregada
        const img = slides[slideIndex - 1].querySelector('.slide-image');
        if (img) {
            console.log(`Carregando imagem: ${img.src}`);
            img.style.opacity = "1";
            img.style.display = "block";
            img.style.visibility = "visible";
            
            if (img.complete) {
                if (img.naturalHeight === 0) {
                    console.error(`Erro ao carregar imagem do slide ${slideIndex}`);
                    img.src = `https://via.placeholder.com/1920x800/0072c6/ffffff?text=Slide+${slideIndex}`;
                } else {
                    console.log(`Imagem do slide ${slideIndex} carregada com sucesso`);
                }
            }
        }
    } else {
        console.error(`Slide ${slideIndex} não encontrado! Total de slides: ${slides.length}`);
    }
    
    if (dots.length > 0 && dots[slideIndex - 1]) {
        dots[slideIndex - 1].classList.add("active");
    }
    
    // Força a renderização do slide
    setTimeout(() => {
        slides[slideIndex - 1].style.opacity = "1";
    }, 50);
    
    // Adiciona um log para debug
    console.log(`Exibindo slide ${slideIndex} de ${slides.length}`);
}

// Função para controlar a visibilidade do botão "Voltar ao topo"
function scrollFunction() {
    // Desativada temporariamente para investigação
    // if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    //     if (backToTopBtn) backToTopBtn.style.display = "block";
    // } else {
    //     if (backToTopBtn) backToTopBtn.style.display = "none";
    // }
}

// Função para rolar para o topo da página
function backToTop() {
    document.body.scrollTop = 0; // Para Safari
    document.documentElement.scrollTop = 0; // Para Chrome, Firefox, IE e Opera
}

// Função para inicializar tooltips
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            // Remove qualquer tooltip existente
            const existingTooltip = document.querySelector('.tooltip');
            if (existingTooltip) {
                existingTooltip.remove();
            }
            
            // Cria o elemento tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            // Calcula a posição do tooltip
            const rect = element.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            const tooltipWidth = tooltipRect.width;
            
            let left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
            
            // Verifica se o tooltip vai sair da tela à direita
            if (left + tooltipWidth > window.innerWidth) {
                left = window.innerWidth - tooltipWidth - 10;
            }
            
            // Verifica se o tooltip vai sair da tela à esquerda
            if (left < 10) {
                left = 10;
            }
            
            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${rect.top - tooltipRect.height - 10}px`;
            
            // Torna o tooltip visível
            setTimeout(() => {
                tooltip.classList.add('visible');
            }, 10);
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.classList.remove('visible');
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.remove();
                    }
                }, 300);
            }
        });
    });
}

// Função para inicializar o botão de voltar ao topo
function initBackToTop() {
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (!backToTopButton) return;
    
    // Mostra ou esconde o botão conforme o scroll
    // window.addEventListener('scroll', () => {
    //     if (window.scrollY > 300) {
    //         backToTopButton.classList.add('visible');
    //     } else {
    //         backToTopButton.classList.remove('visible');
    //     }
    // });
    
    // Adiciona evento de clique para voltar ao topo
    // backToTopButton.addEventListener('click', (e) => {
    //     e.preventDefault();
    //     window.scrollTo({
    //         top: 0,
    //         behavior: 'smooth'
    //     });
    // });
}

// Função para destacar o link atual no menu
function highlightCurrentPage() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || 
            (currentPath.endsWith('/') && href === currentPath.slice(0, -1)) ||
            (!currentPath.endsWith('/') && href === currentPath + '/')) {
            link.classList.add('active');
        }
    });
}

/**
 * Inicializa o carrossel de slides moderno
 */
function initSlider() {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    const dots = document.querySelectorAll('.slider-dot');
    
    if (!slider || slides.length === 0) return;
    
    let currentSlide = 0;
    let slideInterval;
    const slideTime = 5000; // 5 segundos por slide
    
    // Funções de controle do slider
    function showSlide(index) {
        // Normaliza o índice (loop circular)
        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }
        
        // Remover classe active de todos os slides e dots
        slides.forEach(slide => {
            slide.classList.remove('active');
            slide.style.opacity = "0";
            slide.style.transform = "scale(1.05)";
            slide.style.zIndex = "1";
        });
        
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Adicionar classe active ao slide atual e dot correspondente
        slides[currentSlide].classList.add('active');
        slides[currentSlide].style.opacity = "1";
        slides[currentSlide].style.transform = "scale(1)";
        slides[currentSlide].style.zIndex = "2";
        
        dots[currentSlide].classList.add('active');
        
        // Anima os elementos de texto do slide atual
        const currentSlideContent = slides[currentSlide].querySelector('.slide-content');
        if (currentSlideContent) {
            const heading = currentSlideContent.querySelector('h2');
            const paragraph = currentSlideContent.querySelector('p');
            const button = currentSlideContent.querySelector('.slide-button');
            
            if (heading) {
                heading.style.animation = 'none';
                void heading.offsetWidth; // Força reflow
                heading.style.opacity = '0';
            }
            
            if (paragraph) {
                paragraph.style.animation = 'none';
                void paragraph.offsetWidth; // Força reflow
                paragraph.style.opacity = '0';
            }
            
            if (button) {
                button.style.animation = 'none';
                void button.offsetWidth; // Força reflow
                button.style.opacity = '0';
            }
        }
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    function startAutoSlide() {
        if (slideInterval) clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, slideTime);
    }
    
    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }
    
    // Event listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoSlide();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoSlide();
        });
    }
    
    // Controle pelos dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            resetAutoSlide();
        });
    });
    
    // Pausar o slider ao passar o mouse
    slider.addEventListener('mouseenter', () => {
        clearInterval(slideInterval);
    });
    
    slider.addEventListener('mouseleave', () => {
        startAutoSlide();
    });
    
    // Touch events para mobile
    let touchStartX = 0;
    let touchEndX = 0;
    
    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        clearInterval(slideInterval);
    }, { passive: true });
    
    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        startAutoSlide();
    }, { passive: true });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        if (touchEndX < touchStartX - swipeThreshold) {
            // Swipe esquerda
            nextSlide();
        } else if (touchEndX > touchStartX + swipeThreshold) {
            // Swipe direita
            prevSlide();
        }
    }
    
    // Acessibilidade - controle por teclado
    slider.setAttribute('tabindex', '0');
    slider.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            resetAutoSlide();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            resetAutoSlide();
        }
    });
    
    // Iniciar o slider com o primeiro slide
    showSlide(0);
    startAutoSlide();
}

// Função para prevenir scroll infinito
function preventInfiniteScroll() {
    // Desativada temporariamente para investigação
    // Lógica original aqui
}

// Chama a função de prevenção de rolagem infinita quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', preventInfiniteScroll);