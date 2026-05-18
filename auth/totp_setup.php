<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';
$error = '';

// Buscar secret atual
$stmt = $conn->prepare("SELECT totp_secret, username, email FROM utilizadores WHERE cod_utilizador = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Gerar novo secret
$current_secret = $user['totp_secret'];
if (!$current_secret) {
    $current_secret = totp_gerar_secreto();
    $stmt = $conn->prepare("UPDATE utilizadores SET totp_secret = ? WHERE cod_utilizador = ?");
    $stmt->bind_param('si', $current_secret, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Verificar código para ativar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Erro de validação.';
    } elseif (isset($_POST['codigo'])) {
        if (totp_verificar($current_secret, $_POST['codigo'])) {
            $msg = '✅ Autenticação de dois fatores ativada com sucesso!';
        } else {
            $error = 'Código inválido. Tente novamente.';
        }
    } elseif (isset($_POST['desativar'])) {
        $stmt = $conn->prepare("UPDATE utilizadores SET totp_secret = NULL WHERE cod_utilizador = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        $current_secret = null;
        $msg = 'Autenticação de dois fatores desativada.';
    }
}

$totp_uri = 'otpauth://totp/' . rawurlencode('DashBoard ESP') . ':' . rawurlencode($user['email']) . '?secret=' . $current_secret . '&issuer=' . rawurlencode('DashBoard ESP');
$qr_url = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . rawurlencode($totp_uri);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header><?php include '../struct/header.php'; ?></header>
    <main class="conteudo">
        <div class="container" style="max-width:600px;margin:0 auto;">
            <h1 class="page-title"><i class="fas fa-shield-alt"></i> Autenticação de Dois Fatores</h1>

            <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <?php if ($current_secret): ?>
            <div class="profile-card">
                <h3><i class="fas fa-qrcode"></i> 1. Escaneie o QR Code</h3>
                <p style="color:#6b7280;font-size:14px;">Use o Google Authenticator, Authy ou outra app TOTP.</p>
                <div style="text-align:center;margin:16px 0;">
                    <img src="<?= htmlspecialchars($qr_url) ?>" alt="TOTP QR Code" style="border-radius:8px;">
                </div>
                <p style="font-size:12px;color:#9ca3af;text-align:center;">Ou insira manualmente: <code><?= htmlspecialchars($current_secret) ?></code></p>

                <h3 style="margin-top:24px;"><i class="fas fa-check"></i> 2. Verifique o código</h3>
                <form method="POST" style="display:flex;gap:8px;align-items:end;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="form-group" style="flex:1;margin:0;">
                        <label for="codigo">Código de 6 dígitos:</label>
                        <input type="text" id="codigo" name="codigo" pattern="[0-9]{6}" maxlength="6" required placeholder="000000" style="font-size:24px;letter-spacing:8px;text-align:center;">
                    </div>
                    <button type="submit" class="btn btn-primary">Ativar 2FA</button>
                </form>
            </div>

            <div style="margin-top:1rem;text-align:center;">
                <form method="POST" onsubmit="return confirm('Desativar 2FA?');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="desativar" value="1">
                    <button type="submit" class="btn btn-secondary" style="background:#ef4444;color:white;">Desativar 2FA</button>
                </form>
            </div>

            <?php else: ?>
            <div class="profile-card">
                <p style="color:#6b7280;font-size:14px;">A autenticação de dois fatores adiciona uma camada extra de segurança à sua conta.</p>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-shield-alt"></i> Ativar 2FA</button>
                </form>
            </div>
            <?php endif; ?>

            <div style="margin-top:1.5rem;text-align:center;">
                <a href="<?= BASE_URL ?>auth/perfil.php" class="btn btn-water"><i class="fas fa-arrow-left"></i> Voltar ao Perfil</a>
            </div>
        </div>
    </main>
    <footer><?php include '../struct/footer.php'; ?></footer>
</body>
</html>
