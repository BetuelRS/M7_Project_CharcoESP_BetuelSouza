<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(
    env('DB_HOST', 'localhost'),
    env('DB_USER', 'root'),
    env('DB_PASS', ''),
    env('DB_NAME', 'charco_db')
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>