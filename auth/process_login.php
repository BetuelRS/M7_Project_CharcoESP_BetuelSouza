<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Preencha todos os campos.';
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

// Busca o utilizador por username ou email
$sql = "SELECT cod_utilizador, username, password, nome_completo, ADMIN FROM utilizadores WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($password === $user['password']) {
        // Login bem-sucedido
        $_SESSION['user_id'] = $user['cod_utilizador'];
        $_SESSION['user_name'] = $user['nome_completo'] ?: $user['username'];
        $_SESSION['user_admin'] = $user['ADMIN'];
        
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

// Credenciais inválidas
$_SESSION['login_error'] = 'Usuário/email ou senha incorretos.';
header('Location: ' . BASE_URL . 'auth/login.php');
exit();