<!-- SN_editar.php edita sensores -->
<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Sensor</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../struct/header.php'; ?>

    <main class="form-main">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Editar Sensor</h1>
            <a href="<?= BASE_URL ?>SN/Sensores.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <?php
        if (isset($_GET['cod_sensor'])) {
            $cod_sensor = intval($_GET['cod_sensor']);
            $sql = "SELECT * FROM sensores WHERE cod_sensor = $cod_sensor";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
        ?>
        <form action="SN_atualizar.php" method="post" class="sensor-form">
            <input type="hidden" name="cod_sensor" value="<?= $row['cod_sensor'] ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo:</label>
                    <input type="text" id="tipo" name="tipo" value="<?= htmlspecialchars($row['tipo']) ?>" required>
                </div>

                <div class="form-group full-width">
                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" rows="3"><?= htmlspecialchars($row['descricao']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo:</label>
                    <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($row['modelo']) ?>">
                </div>

                <div class="form-group">
                    <label for="fabricante">Fabricante:</label>
                    <input type="text" id="fabricante" name="fabricante" value="<?= htmlspecialchars($row['fabricante']) ?>">
                </div>

                <div class="form-group">
                    <label for="localizacao">Localização:</label>
                    <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($row['localizacao']) ?>">
                </div>

                <div class="form-group">
                    <label for="ativo">Ativo:</label>
                    <select id="ativo" name="ativo">
                        <option value="1" <?= $row['ativo'] ? 'selected' : '' ?>>Sim</option>
                        <option value="0" <?= !$row['ativo'] ? 'selected' : '' ?>>Não</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar Sensor
                </button>
                <a href="<?= BASE_URL ?>SN/Sensores.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
        <?php
            } else {
                echo '<p class="error-message">Sensor não encontrado.</p>';
            }
        } else {
            echo '<p class="error-message">Código do sensor não fornecido.</p>';
        }
        ?>
    </main>

    <?php include '../struct/footer.php'; ?>
</body>
</html>