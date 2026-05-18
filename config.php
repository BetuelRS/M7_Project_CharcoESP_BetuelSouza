<?php
require_once __DIR__ . '/includes/functions.php';
load_env();

if (session_status() === PHP_SESSION_NONE) {
    $lifetime = (int)env('SESSION_LIFETIME', 7200);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $lifetime)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Atualiza atividade da sessão a cada 5 minutos
if (isset($_SESSION['user_id']) && (!isset($_SESSION['session_updated']) || time() - $_SESSION['session_updated'] > 300)) {
    try {
        $conn_aux = new mysqli(env('DB_HOST', 'localhost'), env('DB_USER', 'root'), env('DB_PASS', ''), env('DB_NAME', 'charco_db'));
        if ($conn_aux->connect_error === null) {
            $sid = session_id();
            $conn_aux->query("UPDATE sessoes SET ultima_atividade = NOW() WHERE session_id = '$sid'");
            $conn_aux->close();
        }
    } catch (\Throwable $e) {}
    $_SESSION['session_updated'] = time();
}

define('BASE_URL', env('APP_URL', 'http://localhost/M7_Project/'));
define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
?>