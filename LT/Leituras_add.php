<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

// Busca sensores ativos para o select
$sensores = $conn->query("SELECT cod_sensor, nome FROM sensores WHERE ativo = 1 ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Leitura</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="readings-container">
        <h1 class="page-title">Nova Leitura</h1>

        <div class="form-container">
            <form action="Leituras_add_process.php" method="post">
                <div class="form-group">
                    <label for="cod_sensor">Sensor:</label>
                    <select name="cod_sensor" id="cod_sensor" required>
                        <option value="">Selecione um sensor</option>
                        <?php while ($sensor = $sensores->fetch_assoc()): ?>
                            <option value="<?= $sensor['cod_sensor'] ?>">
                                <?= htmlspecialchars($sensor['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="valor">Valor:</label>
                    <input type="number" step="0.01" id="valor" name="valor" required placeholder="Ex: 23.5">
                </div>

                <div class="form-group">
                    <label for="unidade">Unidade:</label>
                    <input type="text" id="unidade" name="unidade" required placeholder="Ex: °C, %, m">
                </div>

                <div class="form-group">
                    <label for="data_hora">Data/Hora:</label>
                    <input type="datetime-local" id="data_hora" name="data_hora" required>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3" placeholder="Opcional"></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Adicionar Leitura
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="Leituras.php" class="btn btn-water">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>