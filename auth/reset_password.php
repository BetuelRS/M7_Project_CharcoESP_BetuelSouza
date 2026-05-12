<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if (empty($token)) {
    $error = 'Token inválido.';
} else {
    $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $error = 'Token inválido ou expirado.';
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Password - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <script src="<?= BASE_URL ?>assets/form-validation.js"></script>
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-lock"></i>
                <h2>Nova Password</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
                <div class="auth-links">
                    <p><a href="<?= BASE_URL ?>auth/recover.php">Solicitar novo link</a></p>
                </div>
            <?php elseif ($success): ?>
                <form class="auth-form" action="<?= BASE_URL ?>auth/process_reset_password.php" method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Nova Password
                        </label>
                        <input type="password" name="password" id="password" placeholder="Digite a nova password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirmar Password
                        </label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirme a password" required>
                    </div>

                    <button type="submit" class="auth-btn">
                        <i class="fas fa-save"></i> Alterar Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <p><a href="<?= BASE_URL ?>auth/login.php">Voltar ao Login</a></p>
            </div>
        </div>
    </div>
</body>

</html>