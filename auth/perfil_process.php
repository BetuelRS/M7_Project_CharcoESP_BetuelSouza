<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/perfil.php');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['perfil_error'] = 'Erro de validação do formulário. Tente novamente.';
    header('Location: ' . BASE_URL . 'auth/perfil.php');
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'info':
        $nome_completo = trim($_POST['nome_completo'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['perfil_error'] = 'Email é obrigatório';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['perfil_error'] = 'Email inválido';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        $stmt = $conn->prepare("SELECT email FROM utilizadores WHERE email = ? AND cod_utilizador != ?");
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['perfil_error'] = 'Este email já está em uso';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        $stmt = $conn->prepare("UPDATE utilizadores SET nome_completo = ?, email = ? WHERE cod_utilizador = ?");
        $stmt->bind_param('ssi', $nome_completo, $email, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $nome_completo ?: $_SESSION['user_name'];
            $_SESSION['perfil_success'] = 'Informações atualizadas com sucesso';
        } else {
            $_SESSION['perfil_error'] = 'Erro ao atualizar informações';
        }
        header('Location: ' . BASE_URL . 'auth/perfil.php');
        exit;

    case 'password':
        $password_atual = $_POST['password_atual'] ?? '';
        $password_nova = $_POST['password_nova'] ?? '';
        $password_confirma = $_POST['password_confirma'] ?? '';

        if (empty($password_atual) || empty($password_nova) || empty($password_confirma)) {
            $_SESSION['perfil_error'] = 'Preencha todos os campos';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        if (strlen($password_nova) < 6) {
            $_SESSION['perfil_error'] = 'A nova password deve ter pelo menos 6 caracteres';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        if ($password_nova !== $password_confirma) {
            $_SESSION['perfil_error'] = 'As passwords não coincidem';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        $stmt = $conn->prepare("SELECT password FROM utilizadores WHERE cod_utilizador = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $senha_valida = false;
        if (password_get_info($user['password'])['algo'] !== 0) {
            $senha_valida = password_verify($password_atual, $user['password']);
        } else {
            $senha_valida = ($password_atual === $user['password']);
        }

        if (!$senha_valida) {
            $_SESSION['perfil_error'] = 'Password atual incorreta';
            header('Location: ' . BASE_URL . 'auth/perfil.php');
            exit;
        }

        $hashed = password_hash($password_nova, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE utilizadores SET password = ? WHERE cod_utilizador = ?");
        $stmt->bind_param('si', $hashed, $user_id);

        if ($stmt->execute()) {
            $_SESSION['perfil_success'] = 'Password alterada com sucesso';
        } else {
            $_SESSION['perfil_error'] = 'Erro ao alterar password';
        }
        header('Location: ' . BASE_URL . 'auth/perfil.php');
        exit;

    case 'preferencias':
        $preferencias = [
            'notificacoes' => $_POST['notificacoes'] ?? '1',
            'tema' => $_POST['tema'] ?? 'claro',
            'ordem_leituras' => $_POST['ordem_leituras'] ?? 'desc'
        ];

        $preferencias_json = json_encode($preferencias);
        $stmt = $conn->prepare("UPDATE utilizadores SET preferencias = ? WHERE cod_utilizador = ?");
        $stmt->bind_param('si', $preferencias_json, $user_id);

        if ($stmt->execute()) {
            $_SESSION['perfil_success'] = 'Preferências guardadas com sucesso';
        } else {
            $_SESSION['perfil_error'] = 'Erro ao guardar preferências';
        }
        header('Location: ' . BASE_URL . 'auth/perfil.php');
        exit;

    default:
        header('Location: ' . BASE_URL . 'auth/perfil.php');
        exit;
}