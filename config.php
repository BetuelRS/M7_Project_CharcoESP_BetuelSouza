<?php
// Inicia a sessão para controle de login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define a URL base para links (use http:// ou https:// conforme seu ambiente)
define('BASE_URL', 'http://localhost/M5_Project/');

// Define o caminho absoluto no servidor para includes
define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
?>