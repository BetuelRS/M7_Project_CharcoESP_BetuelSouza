<?php
require_once __DIR__ . '/config.php';
?>
<?php
$servername = "localhost";
$username = "BetuelSouza";
$password = "Betuel.300108";
$dbname = "charco_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>