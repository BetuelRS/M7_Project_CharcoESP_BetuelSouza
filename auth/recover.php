<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Password - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-key"></i>
                <h2>Recuperar Password</h2>
            </div>

            <?php if (isset($_SESSION['recover_error'])): ?>
                <div class="auth-message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $_SESSION['recover_error'] ?>
                </div>
                <?php unset($_SESSION['recover_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['recover_success'])): ?>
                <div class="auth-message success">
                    <i class="fas fa-check-circle"></i>
                    <?= $_SESSION['recover_success'] ?>
                </div>
                <?php unset($_SESSION['recover_success']); ?>
            <?php endif; ?>

            <form class="auth-form" action="<?= BASE_URL ?>auth/process_recover.php" method="post">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email da conta
                    </label>
                    <input type="email" name="email" id="email" placeholder="Digite o seu email" required>
                </div>

                <button type="submit" class="auth-btn">
                    <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
                </button>
            </form>

            <div class="auth-links">
                <p> Lembrou a password? <a href="<?= BASE_URL ?>auth/login.php">Voltar ao Login</a></p>
            </div>
        </div>
    </div>
</body>

</html>