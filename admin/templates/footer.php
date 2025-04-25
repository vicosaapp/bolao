            </div> <!-- Fim do content-container -->
        </div> <!-- Fim do main-content -->
    </div> <!-- Fim do admin-container -->

    <!-- JavaScript -->
    <!-- Bootstrap Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personalizado para o painel admin -->
    <script src="<?php echo $base_url ?? ''; ?>../assets/js/admin.js"></script>
    
    <!-- Scripts adicionais específicos da página -->
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts da página, definidos nas páginas individuais -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Script para toggle do sidebar -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle do sidebar no desktop
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
                });
            }
            
            // Toggle do sidebar no mobile
            const mobileToggle = document.getElementById('mobileToggle');
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('mobile-open');
                });
            }
            
            // Toggle dos dropdowns
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const dropdown = this.parentElement;
                    dropdown.classList.toggle('open');
                    
                    // Fechar outros dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            otherToggle.parentElement.classList.remove('open');
                        }
                    });
                });
            });
            
            // Fechar dropdowns ao clicar fora
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.dropdown');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.contains(event.target)) {
                        dropdown.classList.remove('open');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php
// Liberar o buffer de saída
if (ob_get_level() > 0) {
    ob_end_flush();
}
?> 