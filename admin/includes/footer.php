            </div> <!-- Fim da div .content -->
        </div> <!-- Fim da div .main-content -->
    </div> <!-- Fim da div .admin-container -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle da sidebar
            $('#sidebarToggle, #mobileToggle').on('click', function() {
                $('.sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('expanded');
            });

            // Toggle dos dropdowns
            $('.dropdown-toggle').on('click', function(e) {
                e.preventDefault();
                $(this).next('.dropdown-menu').toggleClass('show');
            });

            // Fechar dropdown ao clicar fora
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });

            // Responsividade automática em telas pequenas
            function checkScreenSize() {
                if (window.innerWidth < 992) {
                    $('.sidebar').addClass('collapsed');
                    $('.main-content').addClass('expanded');
                } else {
                    $('.sidebar').removeClass('collapsed');
                    $('.main-content').removeClass('expanded');
                }
            }

            // Verificar tamanho da tela ao carregar e ao redimensionar
            checkScreenSize();
            $(window).resize(checkScreenSize);
        });
    </script>

    <?php if (isset($page_scripts) && !empty($page_scripts)): ?>
    <!-- Scripts específicos da página -->
    <?php foreach($page_scripts as $script): ?>
    <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 