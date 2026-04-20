<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

// Validação do campo de ordenação
$allowed_orders = ['data_hora', 'sensor_nome', 'valor', 'unidade', 'cod_leituras'];
$order = $_GET['order'] ?? 'data_hora';
$order_dir = ($order === 'data_hora') ? 'DESC' : 'ASC'; // data_hora decrescente, outros ascendente
if (!in_array($order, $allowed_orders)) {
    $order = 'data_hora';
    $order_dir = 'DESC';
}


$order_column = ($order === 'sensor_nome') ? 's.nome' : "l.$order";

$sql = "SELECT l.cod_leituras, s.nome AS sensor_nome, l.valor, l.unidade, l.data_hora
        FROM leituras l
        INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
        ORDER BY $order_column $order_dir";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Todas as Leituras</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="readings-container">
        <h1 class="page-title">Todas as Leituras</h1>

        <div class="action-buttons">
            <span class="btn-label">Ordenar por:</span>
            <a href="?order=data_hora" class="btn btn-secondary <?= $order == 'data_hora' ? 'active' : '' ?>">Data/Hora</a>
            <a href="?order=sensor_nome" class="btn btn-secondary <?= $order == 'sensor_nome' ? 'active' : '' ?>">Sensor</a>
            <a href="?order=valor" class="btn btn-secondary <?= $order == 'valor' ? 'active' : '' ?>">Valor</a>
            <a href="?order=unidade" class="btn btn-secondary <?= $order == 'unidade' ? 'active' : '' ?>">Unidade</a>
            <a href="?order=cod_leituras" class="btn btn-secondary <?= $order == 'cod_leituras' ? 'active' : '' ?>">Código</a>
        </div>

        <table class="readings-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Sensor</th>
                    <th>Valor</th>
                    <th>Unidade</th>
                    <th>Data/Hora</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['cod_leituras'] ?></td>
                    <td><?= htmlspecialchars($row['sensor_nome']) ?></td>
                    <td><?= htmlspecialchars($row['valor']) ?></td>
                    <td><?= htmlspecialchars($row['unidade']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                    <td>
                        <a href="leituras_editar.php?cod_leituras=<?= $row['cod_leituras'] ?>" class="action-link edit"><i class="fas fa-edit"></i></a>
                        <a href="leituras_excluir.php?cod_leituras=<?= $row['cod_leituras'] ?>" class="action-link delete" onclick="return confirm('Tem certeza?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="no-data">Nenhuma leitura encontrada</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="action-buttons">
            <a href="Leituras.php" class="btn btn-water"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>