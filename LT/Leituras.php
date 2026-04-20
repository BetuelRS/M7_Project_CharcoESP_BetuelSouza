<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Últimas Leituras</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="readings-container">
        <h1 class="page-title">Últimas 10 Leituras</h1>

        <div class="action-buttons">
            <a href="<?= BASE_URL ?>LT/leituras_add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Leitura
            </a>
            <a href="<?= BASE_URL ?>LT/leituras_todas.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Ver Todas
            </a>
            <a href="<?= BASE_URL ?>index.php" class="btn btn-water">
                <i class="fas fa-home"></i> Voltar ao Início
            </a>
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
                <?php
                $sql = "SELECT l.cod_leituras, s.nome AS sensor_nome, l.valor, l.unidade, l.data_hora
                        FROM leituras l
                        INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
                        ORDER BY l.data_hora DESC
                        LIMIT 10";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['cod_leituras'] ?></td>
                    <td><?= htmlspecialchars($row['sensor_nome']) ?></td>
                    <td><?= htmlspecialchars($row['valor']) ?></td>
                    <td><?= htmlspecialchars($row['unidade']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                    <td>
                        <a href="leituras_editar.php?cod_leituras=<?= $row['cod_leituras'] ?>" class="action-link edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="leituras_excluir.php?cod_leituras=<?= $row['cod_leituras'] ?>" 
                           class="action-link delete" 
                           onclick="return confirm('Tem certeza que deseja excluir esta leitura?')">
                            <i class="fas fa-trash"></i> Excluir
                        </a>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="no-data">Nenhuma leitura encontrada</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>