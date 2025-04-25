    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="assets/img/logo.png" alt="Bolão 10">
                    <p>O maior sistema de bolões do Brasil.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Links Rápidos</h4>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="concursos.php">Concursos</a></li>
                        <li><a href="resultados.php">Resultados</a></li>
                        <li><a href="como_funciona.php">Como Funciona</a></li>
                        <li><a href="contato.php">Contato</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h4>Contato</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> contato@bolao10.com.br</li>
                        <li><i class="fas fa-phone"></i> (11) 99999-9999</li>
                        <li><i class="fas fa-map-marker-alt"></i> São Paulo, SP</li>
                    </ul>
                </div>
                
                <div class="footer-social">
                    <h4>Redes Sociais</h4>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Bolão 10. Todos os direitos reservados.</p>
                <div class="footer-legal">
                    <a href="termos.php">Termos de Uso</a>
                    <a href="privacidade.php">Política de Privacidade</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" aria-label="Voltar ao topo">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 