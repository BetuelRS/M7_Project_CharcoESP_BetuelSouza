<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['login_error'] = 'Erro de validação do formulário. Tente novamente.';
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
    $senha_valida = false;
    
    if (password_get_info($user['password'])['algo'] !== 0) {
        $senha_valida = password_verify($password, $user['password']);
    } else {
        $senha_valida = ($password === $user['password']);
    }
    
    if ($senha_valida) {
        // Migra password para hash se ainda for texto plano
        if (password_get_info($user['password'])['algo'] === 0) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE utilizadores SET password = ? WHERE cod_utilizador = ?");
            $stmt->bind_param("si", $hashed, $user['cod_utilizador']);
            $stmt->execute();
        }

        // Login bem-sucedido
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['cod_utilizador'];
        $_SESSION['user_name'] = $user['nome_completo'] ?: $user['username'];
        $_SESSION['user_admin'] = (int)$user['ADMIN'];
        registrar_auditoria($conn, $user['cod_utilizador'], 'login', 'utilizador', $user['cod_utilizador'], 'Login realizado');

        // Registar sessão
        $sid = session_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt = $conn->prepare("INSERT INTO sessoes (utilizador_id, session_id, ip, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user['cod_utilizador'], $sid, $ip, $ua);
        $stmt->execute();
        $stmt->close();

        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

// Credenciais inválidas
$_SESSION['login_error'] = 'Usuário/email ou senha incorretos.';
header('Location: ' . BASE_URL . 'auth/login.php');
exit();