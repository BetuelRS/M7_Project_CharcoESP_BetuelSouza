<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['register_error'] = 'Erro de validação do formulário. Tente novamente.';
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit();
}

$nome_completo = trim($_POST['nome_completo'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];
if (empty($nome_completo)) $errors[] = 'Nome completo é obrigatório.';
if (empty($username)) $errors[] = 'Nome de utilizador é obrigatório.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
if (strlen($password) < 6) $errors[] = 'A senha deve ter pelo menos 6 caracteres.';
if ($password !== $confirm_password) $errors[] = 'As senhas não coincidem.';

// Verifica duplicatas
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT cod_utilizador FROM utilizadores WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = 'Nome de utilizador ou email já registado.';
    }
    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['register_error'] = implode('<br>', $errors);
    header('Location: ' . BASE_URL . 'auth/register.php');
    exit();
}


$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO utilizadores (nome_completo, username, email, password, ADMIN) VALUES (?, ?, ?, ?, 0)");
$stmt->bind_param("ssss", $nome_completo, $username, $email, $password_hash);

if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    registrar_auditoria($conn, null, 'registar', 'utilizador', $new_id, "Novo utilizador: $username");
    $_SESSION['register_success'] = 'Conta criada com sucesso! Faça login.';
    header('Location: ' . BASE_URL . 'auth/login.php');
} else {
    $_SESSION['register_error'] = 'Erro ao criar conta. Tente novamente.';
    header('Location: ' . BASE_URL . 'auth/register.php');
}
$stmt->close();
$conn->close();