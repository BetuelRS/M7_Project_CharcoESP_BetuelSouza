<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-leaf"></i>
                <h2>Criar Conta</h2>
            </div>

            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="auth-message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $_SESSION['register_error'] ?>
                </div>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['register_success'])): ?>
                <div class="auth-message success">
                    <i class="fas fa-check-circle"></i>
                    <?= $_SESSION['register_success'] ?>
                </div>
                <?php unset($_SESSION['register_success']); ?>
            <?php endif; ?>

            <form class="auth-form" action="<?= BASE_URL ?>auth/process_register.php" method="post">
                <div class="form-group">
                    <label for="nome_completo">
                        <i class="fas fa-user"></i> Nome Completo
                    </label>
                    <input type="text" name="nome_completo" id="nome_completo" 
                           placeholder="Seu nome completo" required>
                </div>

                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-at"></i> Nome de Utilizador
                    </label>
                    <input type="text" name="username" id="username" 
                           placeholder="ex: joaosilva" required>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" name="email" id="email" 
                           placeholder="ex: joao@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Senha
                    </label>
                    <input type="password" name="password" id="password" 
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Confirmar Senha
                    </label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                           placeholder="Digite a senha novamente" required>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Registar
                </button>
            </form>

            <div class="auth-links">
                <p>Já tem conta? <a href="<?= BASE_URL ?>auth/login.php">Faça login</a></p>
            </div>
        </div>
    </div>
</body>
</html>