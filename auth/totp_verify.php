<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['totp_user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação.';
    } else {
        $codigo = $_POST['codigo'] ?? '';
        $stmt = $conn->prepare("SELECT cod_utilizador, totp_secret FROM utilizadores WHERE cod_utilizador = ? AND totp_secret IS NOT NULL");
        $stmt->bind_param('i', $_SESSION['totp_user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && totp_verificar($user['totp_secret'], $codigo)) {
            // Completar login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['cod_utilizador'];

            // Buscar nome
            $stmt = $conn->prepare("SELECT nome_completo, username, ADMIN FROM utilizadores WHERE cod_utilizador = ?");
            $stmt->bind_param('i', $user['cod_utilizador']);
            $stmt->execute();
            $u = $stmt->get_result()->fetch_assoc();
            $_SESSION['user_name'] = $u['nome_completo'] ?: $u['username'];
            $_SESSION['user_admin'] = (int)$u['ADMIN'];
            unset($_SESSION['totp_user_id']);

            registrar_auditoria($conn, $user['cod_utilizador'], 'login', 'utilizador', $user['cod_utilizador'], 'Login com 2FA');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $error = 'Código inválido. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação 2FA - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-shield-alt"></i>
                <h2>Autenticação de Dois Fatores</h2>
            </div>

            <?php if ($error): ?>
            <div class="auth-message error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <p style="text-align:center;color:#6b7280;font-size:14px;margin-bottom:20px;">Introduza o código de 6 dígitos da sua aplicação autenticadora.</p>

            <form method="POST" style="text-align:center;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <input type="text" name="codigo" pattern="[0-9]{6}" maxlength="6" required placeholder="000000" autocomplete="off" style="font-size:32px;letter-spacing:12px;text-align:center;padding:16px;width:200px;margin:0 auto;">
                </div>
                <button type="submit" class="auth-btn" style="margin-top:16px;"><i class="fas fa-check"></i> Verificar</button>
            </form>

            <div class="auth-links">
                <p><a href="<?= BASE_URL ?>auth/login.php">Voltar ao Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
