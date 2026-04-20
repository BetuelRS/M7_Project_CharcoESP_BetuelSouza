<!-- Footer -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-column">
            <h4>Sobre o DashBoard</h4>
            <p>Sistema de monitoramento de sensores e leituras para análise de dados em tempo real.</p>
        </div>
        <div class="footer-column">
            <h4>Links Rápidos</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= BASE_URL ?>auth/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>auth/register.php">Registar</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Contacto</h4>
            <ul class="contact-info">
                <li><i class="fas fa-envelope"></i> suporte@dashboard.pt</li>
                <li><i class="fas fa-phone"></i> +351 123 456 789</li>
                <li><i class="fas fa-map-marker-alt"></i> Av. Principal, 123, Lisboa</li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Siga-nos</h4>
            <div class="social-links">
                <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://linkedin.com" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                <a href="https://github.com/BetuelRS" target="_blank"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> BetuelRS. Todos os direitos reservados. | <a href="#">Política de Privacidade</a> | <a href="#">Termos de Uso</a></p>
    </div>
</footer>