<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

// ========== CONFIGURAÇÃO ==========
$items_per_page = 30;

// ========== VALIDAÇÃO DOS PARÂMETROS ==========
// Ordenação
$allowed_orders = ['data_hora', 'sensor_nome', 'valor', 'unidade', 'cod_leituras'];
$order = $_GET['order'] ?? 'data_hora';
$order_dir = ($order === 'data_hora') ? 'DESC' : 'ASC';
if (!in_array($order, $allowed_orders)) {
    $order = 'data_hora';
    $order_dir = 'DESC';
}
$order_column = ($order === 'sensor_nome') ? 's.nome' : "l.$order";

// Filtros
$filter_sensor = isset($_GET['sensor']) && $_GET['sensor'] !== '' ? (int)$_GET['sensor'] : null;
$filter_unidade = isset($_GET['unidade']) && $_GET['unidade'] !== '' ? trim($_GET['unidade']) : null;
$filter_valor_min = isset($_GET['valor_min']) && $_GET['valor_min'] !== '' ? (float)$_GET['valor_min'] : null;
$filter_valor_max = isset($_GET['valor_max']) && $_GET['valor_max'] !== '' ? (float)$_GET['valor_max'] : null;
$filter_date_start = isset($_GET['date_start']) && $_GET['date_start'] !== '' ? $_GET['date_start'] : null;
$filter_date_end = isset($_GET['date_end']) && $_GET['date_end'] !== '' ? $_GET['date_end'] : null;

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// ========== CONSTRUÇÃO DA QUERY COM FILTROS ==========
$where_conditions = [];
$params = [];
$types = '';

if ($filter_sensor) {
    $where_conditions[] = "l.cod_sensor = ?";
    $params[] = $filter_sensor;
    $types .= 'i';
}
if ($filter_unidade) {
    $where_conditions[] = "l.unidade = ?";
    $params[] = $filter_unidade;
    $types .= 's';
}
if ($filter_valor_min !== null) {
    $where_conditions[] = "l.valor >= ?";
    $params[] = $filter_valor_min;
    $types .= 'd';
}
if ($filter_valor_max !== null) {
    $where_conditions[] = "l.valor <= ?";
    $params[] = $filter_valor_max;
    $types .= 'd';
}
if ($filter_date_start) {
    $where_conditions[] = "DATE(l.data_hora) >= ?";
    $params[] = $filter_date_start;
    $types .= 's';
}
if ($filter_date_end) {
    $where_conditions[] = "DATE(l.data_hora) <= ?";
    $params[] = $filter_date_end;
    $types .= 's';
}

$where_sql = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

// ========== TOTAL DE REGISTROS (para paginação) ==========
$count_sql = "SELECT COUNT(*) as total 
              FROM leituras l
              INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
              $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);
$count_stmt->close();

// ========== CONSULTA PRINCIPAL COM PAGINAÇÃO ==========
$sql = "SELECT l.cod_leituras, s.nome AS sensor_nome, l.valor, l.unidade, l.data_hora
        FROM leituras l
        INNER JOIN sensores s ON l.cod_sensor = s.cod_sensor
        $where_sql
        ORDER BY $order_column $order_dir
        LIMIT ? OFFSET ?";

$params[] = $items_per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ========== BUSCAR DADOS PARA OS FILTROS (sensores e unidades) ==========
$sensors = [];
$sensor_result = $conn->query("SELECT cod_sensor, nome FROM sensores ORDER BY nome");
while ($row = $sensor_result->fetch_assoc()) {
    $sensors[] = $row;
}

$units = [];
$unit_result = $conn->query("SELECT DISTINCT unidade FROM leituras ORDER BY unidade");
while ($row = $unit_result->fetch_assoc()) {
    $units[] = $row['unidade'];
}

// ========== FUNÇÃO PARA MANTER FILTROS NAS URLs ==========
function build_query_string($exclude = ['page'], $extra = []) {
    $params = $_GET;
    foreach ($exclude as $key) {
        unset($params[$key]);
    }
    $params = array_merge($params, $extra);
    return http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Todas as Leituras - Sistema de Monitoramento</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
</head>
<body>
    <header>
        <?php include '../struct/header.php'; ?>
    </header>

    <main class="readings-container">
        <h1 class="page-title">
            <i class="fas fa-chart-line" style="margin-right: 0.5rem;"></i>
            Todas as Leituras
        </h1>

        <!-- Painel de Filtros Avançados -->
        <div class="filters-card">
            <form method="GET" action="" id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label><i class="fas fa-microchip"></i> Sensor</label>
                        <select name="sensor">
                            <option value="">Todos os sensores</option>
                            <?php foreach ($sensors as $sensor): ?>
                                <option value="<?= $sensor['cod_sensor'] ?>" <?= ($filter_sensor == $sensor['cod_sensor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sensor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-ruler"></i> Unidade</label>
                        <select name="unidade">
                            <option value="">Todas as unidades</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= htmlspecialchars($unit) ?>" <?= ($filter_unidade == $unit) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($unit) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-chart-simple"></i> Valor mínimo</label>
                        <input type="number" step="any" name="valor_min" placeholder="Ex: 0" value="<?= htmlspecialchars($filter_valor_min ?? '') ?>">
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-chart-simple"></i> Valor máximo</label>
                        <input type="number" step="any" name="valor_max" placeholder="Ex: 100" value="<?= htmlspecialchars($filter_valor_max ?? '') ?>">
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-calendar-alt"></i> Data inicial</label>
                        <input type="date" name="date_start" value="<?= htmlspecialchars($filter_date_start ?? '') ?>">
                    </div>

                    <div class="filter-group">
                        <label><i class="fas fa-calendar-alt"></i> Data final</label>
                        <input type="date" name="date_end" value="<?= htmlspecialchars($filter_date_end ?? '') ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="?<?= build_query_string(['sensor', 'unidade', 'valor_min', 'valor_max', 'date_start', 'date_end', 'page']) ?>" class="btn-filter btn-clear">
                            <i class="fas fa-eraser"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Ordenação e estatísticas -->
        <div class="action-buttons" style="justify-content: space-between; flex-wrap: wrap;">
            <div class="btn-group">
                <span class="btn-label">Ordenar por:</span>
                <a href="?<?= build_query_string(['order', 'page'], ['order' => 'data_hora']) ?>" class="btn btn-secondary <?= $order == 'data_hora' ? 'active' : '' ?>">Data/Hora</a>
                <a href="?<?= build_query_string(['order', 'page'], ['order' => 'sensor_nome']) ?>" class="btn btn-secondary <?= $order == 'sensor_nome' ? 'active' : '' ?>">Sensor</a>
                <a href="?<?= build_query_string(['order', 'page'], ['order' => 'valor']) ?>" class="btn btn-secondary <?= $order == 'valor' ? 'active' : '' ?>">Valor</a>
                <a href="?<?= build_query_string(['order', 'page'], ['order' => 'unidade']) ?>" class="btn btn-secondary <?= $order == 'unidade' ? 'active' : '' ?>">Unidade</a>
                <a href="?<?= build_query_string(['order', 'page'], ['order' => 'cod_leituras']) ?>" class="btn btn-secondary <?= $order == 'cod_leituras' ? 'active' : '' ?>">Código</a>
            </div>
            <div class="result-stats">
                <i class="fas fa-database"></i> <?= $total_rows ?> leitura(s) encontrada(s)
                <?php if ($total_pages > 1): ?>
                    | Página <?= $page ?> de <?= $total_pages ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabela de Leituras -->
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['cod_leituras'] ?></td>
                            <td><?= htmlspecialchars($row['sensor_nome']) ?></td>
                            <td><?= htmlspecialchars($row['valor']) ?></td>
                            <td><?= htmlspecialchars($row['unidade']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['data_hora'])) ?></td>
                            <td>
                                <a href="leituras_editar.php?cod_leituras=<?= $row['cod_leituras'] ?>" class="action-link edit"><i class="fas fa-edit"></i></a>
                                <a href="leituras_excluir.php?cod_leituras=<?= $row['cod_leituras'] ?>" class="action-link delete" onclick="return confirm('Tem certeza que deseja excluir esta leitura?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <i class="fas fa-info-circle"></i> Nenhuma leitura encontrada com os filtros aplicados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= build_query_string(['page'], ['page' => $page - 1]) ?>">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-left"></i> Anterior</span>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                if ($start_page > 1): ?>
                    <a href="?<?= build_query_string(['page'], ['page' => 1]) ?>">1</a>
                    <?php if ($start_page > 2): ?>
                        <span>...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= build_query_string(['page'], ['page' => $i]) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span>...</span>
                    <?php endif; ?>
                    <a href="?<?= build_query_string(['page'], ['page' => $total_pages]) ?>"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?<?= build_query_string(['page'], ['page' => $page + 1]) ?>">
                        Próximo <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled">Próximo <i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons" style="margin-top: 1.5rem;">
            <a href="Leituras.php" class="btn btn-water"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </main>

    <footer>
        <?php include '../struct/footer.php'; ?>
    </footer>
</body>
</html>