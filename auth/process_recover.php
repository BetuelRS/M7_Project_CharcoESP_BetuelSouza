<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['recover_error'] = 'Erro de validação do formulário. Tente novamente.';
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    $_SESSION['recover_error'] = 'Por favor, insira o seu email.';
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

$stmt = $conn->prepare("SELECT cod_utilizador, email FROM utilizadores WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['recover_error'] = 'Email não encontrado.';
    header('Location: ' . BASE_URL . 'auth/recover.php');
    exit();
}

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expires);
$stmt->execute();

$reset_link = BASE_URL . "auth/reset_password.php?token=" . $token;

$_SESSION['recover_success'] = "Link de recuperação enviado! (Modo teste: $reset_link)";
$_SESSION['recover_token'] = $token;
header('Location: ' . BASE_URL . 'auth/recover.php');
exit();