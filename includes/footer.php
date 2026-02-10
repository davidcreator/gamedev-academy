<?php
// includes/footer.php - Footer padrão do site

// Obter estatísticas do site se não foram definidas
if (!isset($totalUsers) && class_exists('User')) {
    $userModel = new User();
    $totalUsers = $userModel->count();
}

if (!isset($totalCourses) && class_exists('Course')) {
    $courseModel = new Course();
    $totalCourses = $courseModel->count();
}
?>
    </main>
    <!-- End Main Content -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Footer Top -->
            <div class="footer-top">
                <div class="footer-grid">
                    <!-- About Section -->
                    <div class="footer-section">
                        <div class="footer-brand">
                            <span class="logo-icon">🎮</span>
                            <h3>GameDev Academy</h3>
                        </div>
                        <p class="footer-description">
                            Sua jornada no desenvolvimento de jogos começa aqui. 
                            Aprenda com os melhores e crie seus próprios jogos.
                        </p>
                        <div class="footer-stats">
                            <div class="stat-item">
                                <strong><?= number_format($totalUsers ?? 0) ?></strong>
                                <span>Alunos</span>
                            </div>
                            <div class="stat-item">
                                <strong><?= number_format($totalCourses ?? 0) ?></strong>
                                <span>Cursos</span>
                            </div>
                            <div class="stat-item">
                                <strong>4.8</strong>
                                <span>Avaliação</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="footer-section">
                        <h4>Links Rápidos</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url() ?>">Início</a></li>
                            <li><a href="<?= url('courses.php') ?>">Cursos</a></li>
                            <li><a href="<?= url('news.php') ?>">Novidades</a></li>
                            <li><a href="<?= url('leaderboard.php') ?>">Ranking</a></li>
                            <li><a href="<?= url('about.php') ?>">Sobre Nós</a></li>
                            <li><a href="<?= url('contact.php') ?>">Contato</a></li>
                        </ul>
                    </div>
                    
                    <!-- Categories -->
                    <div class="footer-section">
                        <h4>Categorias</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('courses.php?category=unity') ?>">Unity</a></li>
                            <li><a href="<?= url('courses.php?category=unreal') ?>">Unreal Engine</a></li>
                            <li><a href="<?= url('courses.php?category=godot') ?>">Godot</a></li>
                            <li><a href="<?= url('courses.php?category=programming') ?>">Programação</a></li>
                            <li><a href="<?= url('courses.php?category=art') ?>">Arte 2D/3D</a></li>
                            <li><a href="<?= url('courses.php?category=design') ?>">Game Design</a></li>
                        </ul>
                    </div>
                    
                    <!-- Support & Legal -->
                    <div class="footer-section">
                        <h4>Suporte</h4>
                        <ul class="footer-links">
                            <li><a href="<?= url('help.php') ?>">Central de Ajuda</a></li>
                            <li><a href="<?= url('faq.php') ?>">FAQ</a></li>
                            <li><a href="<?= url('terms.php') ?>">Termos de Uso</a></li>
                            <li><a href="<?= url('privacy.php') ?>">Privacidade</a></li>
                            <li><a href="<?= url('cookies.php') ?>">Política de Cookies</a></li>
                        </ul>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="footer-section">
                        <h4>Newsletter</h4>
                        <p>Receba as últimas novidades e atualizações</p>
                        <form class="newsletter-form" id="newsletterForm" onsubmit="subscribeNewsletter(event)">
                            <div class="newsletter-input-group">
                                <input type="email" 
                                       placeholder="seu@email.com" 
                                       required 
                                       class="newsletter-input">
                                <button type="submit" class="newsletter-btn">
                                    Inscrever
                                </button>
                            </div>
                        </form>
                        
                        <!-- Social Links -->
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook">
                                <svg width="24" height="24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <svg width="24" height="24" fill="currentColor">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="YouTube">
                                <svg width="24" height="24" fill="currentColor">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                            <a href="#" class="social-link" aria-label="Discord">
                                <svg width="24" height="24" fill="currentColor">
                                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">
                        © <?= date('Y') ?> GameDev Academy. Todos os direitos reservados.
                    </p>
                    <div class="footer-badges">
                        <span class="badge-item">🔒 SSL Seguro</span>
                        <span class="badge-item">💳 Pagamento Seguro</span>
                        <span class="badge-item">📱 Mobile Friendly</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" onclick="scrollToTop()">
        <svg width="24" height="24" fill="currentColor">
            <path d="M12 2l8 8h-6v12h-4V10H4l8-8z"/>
        </svg>
    </button>
    
    <!-- JavaScript Principal -->
    <script src="<?= asset('js/main.js') ?>"></script>
    
    <!-- JavaScript Adicional para páginas específicas -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Script para variáveis PHP em JavaScript -->
    <script>
        // Configurações globais
        window.GameDevAcademy = {
            baseUrl: '<?= SITE_URL ?>',
            assetUrl: '<?= SITE_URL ?>/assets',
            isLoggedIn: <?= $auth->isLoggedIn() ? 'true' : 'false' ?>,
            userId: <?= $auth->isLoggedIn() ? $currentUser['id'] : 'null' ?>,
            csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>'
        };
    </script>
</body>
</html>