<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$sensores = [];
$result = $conn->query("SELECT cod_sensor, nome, tipo FROM sensores WHERE ativo = 1 ORDER BY nome");
while ($row = $result->fetch_assoc()) {
    $sensores[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios e Downloads - DashBoard ESP</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="sidebar">
        <?php include __DIR__ . '/../struct/header.php'; ?>
    </header>
    <main class="conteudo">
        <div class="container">
            <h1 class="page-title"><i class="fas fa-download"></i> Relatórios e Downloads</h1>
            
            <div class="reports-grid">
                <div class="report-card">
                    <div class="report-icon" style="background: #3b82f6;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Leituras por Sensor</h3>
                    <p>Exportar histórico de leituras filtrado por sensor e período.</p>
                    <form action="gerar_relatorio.php" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="leituras_sensor">
                        <div class="form-group">
                            <label for="sensor_leituras">Sensor:</label>
                            <select name="cod_sensor" id="sensor_leituras" required>
                                <option value="">Selecionar sensor...</option>
                                <?php foreach ($sensores as $s): ?>
                                    <option value="<?= $s['cod_sensor'] ?>"><?= htmlspecialchars($s['nome']) ?> (<?= $s['tipo'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="formato_leituras">Formato:</label>
                            <select name="formato" id="formato_leituras">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Gerar</button>
                    </form>
                </div>

                <div class="report-card">
                    <div class="report-icon" style="background: #10b981;">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <h3>Inventário de Sensores</h3>
                    <p>Lista completa de sensores com detalhes técnicos e status.</p>
                    <form action="gerar_relatorio.php" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="inventario_sensores">
                        <div class="form-group">
                            <label for="formato_sensores">Formato:</label>
                            <select name="formato" id="formato_sensores">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Gerar</button>
                    </form>
                </div>

                <div class="report-card">
                    <div class="report-icon" style="background: #8b5cf6;">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3>Estatísticas de Leituras</h3>
                    <p>Média, mínimo, máximo e contagem por sensor.</p>
                    <form action="gerar_relatorio.php" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="estatisticas">
                        <div class="form-group">
                            <label for="sensor_estatisticas">Sensor:</label>
                            <select name="cod_sensor" id="sensor_estatisticas">
                                <option value="todos">Todos os sensores</option>
                                <?php foreach ($sensores as $s): ?>
                                    <option value="<?= $s['cod_sensor'] ?>"><?= htmlspecialchars($s['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="formato_estatisticas">Formato:</label>
                            <select name="formato" id="formato_estatisticas">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Gerar</button>
                    </form>
                </div>

                <div class="report-card">
                    <div class="report-icon" style="background: #f59e0b;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Leituras por Período</h3>
                    <p>Exportar todas as leituras dentro de um intervalo de datas.</p>
                    <form action="gerar_relatorio.php" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="leituras_periodo">
                        <div class="form-group">
                            <label for="data_inicio">Data Início:</label>
                            <input type="date" name="data_inicio" id="data_inicio" required>
                        </div>
                        <div class="form-group">
                            <label for="data_fim">Data Fim:</label>
                            <input type="date" name="data_fim" id="data_fim" required>
                        </div>
                        <div class="form-group">
                            <label for="formato_periodo">Formato:</label>
                            <select name="formato" id="formato_periodo">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Gerar</button>
                    </form>
                </div>

                <?php if ($_SESSION['user_admin'] == 1): ?>
                <div class="report-card">
                    <div class="report-icon" style="background: #ef4444;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Utilizadores do Sistema</h3>
                    <p>Lista de utilizadores registados e seus níveis de acesso.</p>
                    <form action="gerar_relatorio.php" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="utilizadores">
                        <div class="form-group">
                            <label for="formato_users">Formato:</label>
                            <select name="formato" id="formato_users">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Gerar</button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="report-card">
                    <div class="report-icon" style="background: #06b6d4;">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <h3>Todas as Leituras</h3>
                    <p>Exportar histórico completo de todas as leituras.</p>
                    <form action="gerar_relatorio.php" method="GET" target="_blank">
                        <input type="hidden" name="tipo" value="todas_leituras">
                        <div class="form-group">
                            <label for="formato_todas">Formato:</label>
                            <select name="formato" id="formato_todas">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Gerar</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <?php include __DIR__ . '/../struct/footer.php'; ?>
    </footer>
</body>
</html>
<style>
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.report-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}
.report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}
.report-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    margin-bottom: 16px;
}
.report-card h3 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 18px;
}
.report-card p {
    margin: 0 0 16px 0;
    color: #6b7280;
    font-size: 14px;
}
.form-group {
    margin-bottom: 12px;
}
.form-group label {
    display: block;
    margin-bottom: 4px;
    font-size: 13px;
    color: #374151;
    font-weight: 500;
}
.form-group select,
.form-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    background: white;
}
.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}
.btn-primary {
    background: #3b82f6;
    color: white;
}
.btn-primary:hover {
    background: #2563eb;
}
</style>