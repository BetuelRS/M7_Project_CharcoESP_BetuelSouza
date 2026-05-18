<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

$sid = session_id();
$conn->query("DELETE FROM sessoes WHERE session_id = '" . $conn->real_escape_string($sid) . "'");

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: ' . BASE_URL . 'index.php');
exit();