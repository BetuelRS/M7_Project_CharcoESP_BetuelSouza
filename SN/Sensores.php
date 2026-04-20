<?php
require_once __DIR__ . '/../config.php';

// Verifica se o usuário está logado 
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

include BASE_PATH . 'db.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Sensores</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../struct/header.php'; ?>

    <main class="sensors-main">
        <div class="page-header">
            <h1><i class="fas fa-microchip"></i> Gestão de Sensores</h1>
            <a href="<?= BASE_URL ?>SN/SN_add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Adicionar Sensor
            </a>
        </div>

        <div class="table-responsive">
            <table class="sensors-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th>Modelo</th>
                        <th>Fabricante</th>
                        <th>Localização</th>
                        <th>Ativo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT cod_sensor, nome, tipo, descricao, modelo, fabricante, localizacao, ativo FROM sensores";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['cod_sensor'] ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= htmlspecialchars($row['tipo']) ?></td>
                        <td><?= htmlspecialchars($row['descricao']) ?></td>
                        <td><?= htmlspecialchars($row['modelo']) ?></td>
                        <td><?= htmlspecialchars($row['fabricante']) ?></td>
                        <td><?= htmlspecialchars($row['localizacao']) ?></td>
                        <td>
                            <span class="status <?= $row['ativo'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $row['ativo'] ? 'Sim' : 'Não' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="<?= BASE_URL ?>SN/SN_editar.php?cod_sensor=<?= $row['cod_sensor'] ?>" class="btn-icon btn-edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= BASE_URL ?>SN/SN_excluir.php?cod_sensor=<?= $row['cod_sensor'] ?>" class="btn-icon btn-delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este sensor?');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="9" class="no-data">Nenhum sensor encontrado.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../struct/footer.php'; ?>
</body>
</html>