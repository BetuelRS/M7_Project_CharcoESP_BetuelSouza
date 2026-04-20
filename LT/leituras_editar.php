<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

// Verifica se o código da leitura foi passado
if (!isset($_GET['cod_leituras']) || empty($_GET['cod_leituras'])) {
    header('Location: ' . BASE_URL . 'LT/Leituras.php');
    exit();
}

$cod_leituras = (int)$_GET['cod_leituras'];

// Busca os dados da leitura
$stmt = $conn->prepare("SELECT * FROM leituras WHERE cod_leituras = ?");
$stmt->bind_param("i", $cod_leituras);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Leitura não encontrada
    header('Location: ' . BASE_URL . 'LT/Leituras.php');
    exit();
}

$leitura = $result->fetch_assoc();
$stmt->close();

// Busca lista de sensores para o select
$sensores = $conn->query("SELECT cod_sensor, nome FROM sensores WHERE ativo = 1 ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Leitura</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="readings-container">
        <h1 class="page-title">Editar Leitura #<?= $leitura['cod_leituras'] ?></h1>

        <div class="form-container">
            <form action="leituras_atualizar.php" method="post">
                <input type="hidden" name="cod_leituras" value="<?= $leitura['cod_leituras'] ?>">

                <div class="form-group">
                    <label for="cod_sensor">Sensor:</label>
                    <select id="cod_sensor" name="cod_sensor" required>
                        <option value="">Selecione um sensor</option>
                        <?php while ($sensor = $sensores->fetch_assoc()): ?>
                            <option value="<?= $sensor['cod_sensor'] ?>" 
                                <?= $sensor['cod_sensor'] == $leitura['cod_sensor'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sensor['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="valor">Valor:</label>
                    <input type="number" step="0.01" id="valor" name="valor" 
                           value="<?= htmlspecialchars($leitura['valor']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="unidade">Unidade:</label>
                    <input type="text" id="unidade" name="unidade" 
                           value="<?= htmlspecialchars($leitura['unidade']) ?>" required 
                           placeholder="ex: °C, %, m">
                </div>

                <div class="form-group">
                    <label for="data_hora">Data/Hora:</label>
                    <input type="datetime-local" id="data_hora" name="data_hora" 
                           value="<?= date('Y-m-d\TH:i', strtotime($leitura['data_hora'])) ?>" required>
                </div>

                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes" rows="3"><?= htmlspecialchars($leitura['observacoes']) ?></textarea>
                </div>

                <button type="submit" class="btn-submit">Atualizar Leitura</button>
                <a href="Leituras.php" class="btn btn-water" style="margin-top: 1rem; display: inline-block;">Cancelar</a>
            </form>
        </div>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>