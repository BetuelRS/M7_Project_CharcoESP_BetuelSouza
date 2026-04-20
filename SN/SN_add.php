<!-- adicionar novos sensores -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Sensor</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../struct/header.php'; ?>

    <main class="form-main">
        <div class="page-header">
            <h1><i class="fas fa-plus-circle"></i> Adicionar Novo Sensor</h1>
            <a href="<?= BASE_URL ?>SN/Sensores.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <form action="SN_add_process.php" method="post" class="sensor-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo:</label>
                    <input type="text" id="tipo" name="tipo" required>
                </div>

                <div class="form-group full-width">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo">
                </div>

                <div class="form-group">
                    <label for="fabricante">Fabricante:</label>
                    <input type="text" id="fabricante" name="fabricante">
                </div>

                <div class="form-group">
                    <label for="localizacao">Localização:</label>
                    <input type="text" id="localizacao" name="localizacao">
                </div>

                <div class="form-group">
                    <label for="data_instalacao">Data de Instalação:</label>
                    <input type="date" id="data_instalacao" name="data_instalacao">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Adicionar Sensor
                </button>
                <a href="<?= BASE_URL ?>SN/Sensores.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </main>

    <?php include '../struct/footer.php'; ?>
</body>
</html>