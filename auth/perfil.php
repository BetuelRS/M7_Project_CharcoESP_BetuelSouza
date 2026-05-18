<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT cod_utilizador, username, email, nome_completo, ADMIN, preferencias, created_at FROM utilizadores WHERE cod_utilizador = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$preferencias = $user['preferencias'] ? json_decode($user['preferencias'], true) : [];
$success = $_SESSION['perfil_success'] ?? '';
$error = $_SESSION['perfil_error'] ?? '';
unset($_SESSION['perfil_success'], $_SESSION['perfil_error']);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="sidebar">
        <?php include __DIR__ . '/../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <div class="container">
            <h1 class="page-title"><i class="fas fa-user-circle"></i> Perfil do Utilizador</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="profile-grid">
                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        <h3>Informações Pessoais</h3>
                    </div>
                    <form action="perfil_process.php" method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="info">
                        <div class="form-group">
                            <label for="username">Nome de Utilizador</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly class="input-readonly">
                            <small style="color: #9ca3af;">O username não pode ser alterado</small>
                        </div>
                        <div class="form-group">
                            <label for="nome_completo">Nome Completo</label>
                            <input type="text" id="nome_completo" name="nome_completo" value="<?= htmlspecialchars($user['nome_completo'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Alterações
                        </button>
                    </form>
                </div>

                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-lock"></i>
                        <h3>Alterar Password</h3>
                    </div>
                    <form action="perfil_process.php" method="POST" class="profile-form" id="passwordForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="password">
                        <div class="form-group">
                            <label for="password_atual">Password Atual</label>
                            <input type="password" id="password_atual" name="password_atual" required>
                        </div>
                        <div class="form-group">
                            <label for="password_nova">Nova Password</label>
                            <input type="password" id="password_nova" name="password_nova" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="password_confirma">Confirmar Nova Password</label>
                            <input type="password" id="password_confirma" name="password_confirma" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Alterar Password
                        </button>
                    </form>
                </div>

                <div class="profile-card">
                    <div class="card-header">
                        <i class="fas fa-sliders-h"></i>
                        <h3>Preferências</h3>
                    </div>
                    <form action="perfil_process.php" method="POST" class="profile-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="preferencias">
                        <div class="form-group">
                            <label for="notificacoes">Notificações por Email</label>
                            <select id="notificacoes" name="notificacoes">
                                <option value="1" <?= ($preferencias['notificacoes'] ?? '1') === '1' ? 'selected' : '' ?>>Ativadas</option>
                                <option value="0" <?= ($preferencias['notificacoes'] ?? '1') === '0' ? 'selected' : '' ?>>Desativadas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tema">Tema da Interface</label>
                            <select id="tema" name="tema">
                                <option value="claro" <?= ($preferencias['tema'] ?? 'claro') === 'claro' ? 'selected' : '' ?>>Claro</option>
                                <option value="escuro" <?= ($preferencias['tema'] ?? 'claro') === 'escuro' ? 'selected' : '' ?>>Escuro</option>
                                <option value="auto" <?= ($preferencias['tema'] ?? 'claro') === 'auto' ? 'selected' : '' ?>>Automático</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ordem_leituras">Ordenação Predefinida</label>
                            <select id="ordem_leituras" name="ordem_leituras">
                                <option value="desc" <?= ($preferencias['ordem_leituras'] ?? 'desc') === 'desc' ? 'selected' : '' ?>>Mais Recentes Primeiro</option>
                                <option value="asc" <?= ($preferencias['ordem_leituras'] ?? 'desc') === 'asc' ? 'selected' : '' ?>>Mais Antigas Primeiro</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Preferências
                        </button>
                    </form>
                </div>

                <div class="profile-card profile-info">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i>
                        <h3>Detalhes da Conta</h3>
                    </div>
                    <div class="info-items">
                        <div class="info-item">
                            <span class="info-label">Tipo de Conta:</span>
                            <span class="info-value"><?= $user['ADMIN'] ? '<span class="badge badge-admin">Administrador</span>' : '<span class="badge badge-user">Utilizador</span>' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">ID do Utilizador:</span>
                            <span class="info-value">#<?= $user['cod_utilizador'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Membro desde:</span>
                            <span class="info-value"><?= $user['created_at'] ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <?php include __DIR__ . '/../struct/footer.php'; ?>
    </footer>
    <script src="<?= BASE_URL ?>assets/form-validation.js"></script>
    <script>
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const nova = document.getElementById('password_nova').value;
            const confirma = document.getElementById('password_confirma').value;
            if (nova !== confirma) {
                e.preventDefault();
                alert('As passwords não coincidem!');
            }
            if (nova.length < 6) {
                e.preventDefault();
                alert('A nova password deve ter pelo menos 6 caracteres!');
            }
        });
    </script>
</body>
</html>

<style>
.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.profile-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.profile-card .card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}
.profile-card .card-header i {
    font-size: 20px;
    color: #3b82f6;
}
.profile-card .card-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
}
.profile-form .form-group {
    margin-bottom: 16px;
}
.profile-form label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}
.profile-form input,
.profile-form select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    transition: border-color 0.2s;
}
.profile-form input:focus,
.profile-form select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.input-readonly {
    background: #f3f4f6 !important;
    color: #6b7280;
}
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-primary {
    background: #3b82f6;
    color: white;
}
.btn-primary:hover {
    background: #2563eb;
}
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}
.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.info-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}
.info-item:last-child {
    border-bottom: none;
}
.info-label {
    color: #6b7280;
    font-size: 13px;
}
.info-value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
}
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}
.badge-admin {
    background: #dbeafe;
    color: #1e40af;
}
.badge-user {
    background: #d1fae5;
    color: #065f46;
}
</style>