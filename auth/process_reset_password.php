<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['recover_error'] = 'Erro de validação do formulário. Tente novamente.';
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($token) || empty($password) || empty($confirm_password)) {
    $_SESSION['recover_error'] = 'Preencha todos os campos.';
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['recover_error'] = 'As passwords não coincidem.';
    header('Location: ' . BASE_URL . 'auth/reset_password.php?token=' . $token);
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['recover_error'] = 'A password deve ter pelo menos 6 caracteres.';
    header('Location: ' . BASE_URL . 'auth/reset_password.php?token=' . $token);
    exit();
}

$stmt = $conn->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['recover_error'] = 'Token inválido ou expirado.';
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

$row = $result->fetch_assoc();
$email = $row['email'];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE utilizadores SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashed_password, $email);
$stmt->execute();

$stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

registrar_auditoria($conn, null, 'reset_password', 'utilizador', 0, "Password redefinida para: $email");
$_SESSION['login_success'] = 'Password alterada com sucesso! Faça login com a nova password.';
header('Location: ' . BASE_URL . 'auth/login.php');
exit();