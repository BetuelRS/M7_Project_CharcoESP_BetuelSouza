<!-- Header -->
<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <a href="<?= BASE_URL ?>index.php">
                <i class="fas fa-leaf"></i> 
                <span>DashBoard ESP</span>
            </a>
        </div>
        
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav class="main-nav" id="mainNav">
            <ul class="nav-list">
                <li><a href="<?= BASE_URL ?>index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= BASE_URL ?>Palmela/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'Palmela/') !== false ? 'active' : '' ?>"><i class="fas fa-cloud-sun"></i> Palmela</a></li>
                
<?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?= BASE_URL ?>LT/Leituras.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'Leituras.php') !== false ? 'active' : '' ?>">Leituras</a></li>
                        <li><a href="<?= BASE_URL ?>LT/grafico.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'grafico.php') !== false ? 'active' : '' ?>">Gráficos</a></li>
<li><a href="<?= BASE_URL ?>SN/Sensores.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'Sensores.php') !== false ? 'active' : '' ?>">Sensores</a></li>
                        <li><a href="<?= BASE_URL ?>RT/relatorios.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'RT/') !== false ? 'active' : '' ?>">Relatórios</a></li>
                    
                    <?php if ($_SESSION['user_admin']): ?>
                        <li><a href="<?= BASE_URL ?>admin/admin.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'admin/admin.php') !== false ? 'active' : '' ?>">Admin</a></li>
                    <?php endif; ?>
                    
                    <li class="user-menu">
                        <a href="#" class="user-name user-menu-toggle">
                            <i class="fas fa-user-circle"></i> 
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="user-dropdown">
                            <a href="<?= BASE_URL ?>auth/perfil.php" class="dropdown-item">
                                <i class="fas fa-user-cog"></i> Perfil
                            </a>
                            <a href="<?= BASE_URL ?>auth/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>auth/login.php" class="nav-link">Entrar</a></li>
                    <li><a href="<?= BASE_URL ?>auth/register.php" class="nav-link btn-register">Registar</a></li>
                <?php endif; ?>
                
                
            </ul>
        </nav>
    </div>
</header>