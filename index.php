<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">

</head>
<body>
    <header class = "sidebar">
        <?php include 'struct/header.php'; ?>
    </header>
    <main class = "conteudo">
        <?php include 'dashboard.php'; ?>
    </main>
    <footer>
        <?php include 'struct/footer.php'; ?>
    </footer>
</body>
</html>