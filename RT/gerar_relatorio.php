<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Acesso negado';
    exit;
}

$tipo = $_GET['tipo'] ?? '';
$formato = $_GET['formato'] ?? 'csv';

function outputCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]), ';');
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }
    fclose($output);
    exit;
}

function outputJSON($data, $filename) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function outputPDF($html, $title, $filename) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>' . $title . ' - DashBoard ESP</title>
        <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        </script>
    </head>
    <body onload="window.print()">' . $html . '</body>
    </html>';
    exit;
}

function gerarNomeArquivo($tipo, $extensao) {
    $date = date('Y-m-d_His');
    return $tipo . '_' . $date . '.' . $extensao;
}

function getTituloRelatorio($tipo) {
    $titulos = [
        'leituras_sensor' => 'Leituras por Sensor',
        'inventario_sensores' => 'Inventário de Sensores',
        'estatisticas' => 'Estatísticas de Leituras',
        'leituras_periodo' => 'Leituras por Período',
        'utilizadores' => 'Utilizadores do Sistema',
        'todas_leituras' => 'Todas as Leituras'
    ];
    return $titulos[$tipo] ?? 'Relatório';
}

switch ($tipo) {
    case 'leituras_sensor':
        $cod_sensor = (int) $_GET['cod_sensor'];
        $stmt = $conn->prepare("
            SELECT l.cod_leituras, l.valor, l.unidade, l.data_hora, l.observacoes, s.nome as sensor_nome, s.tipo as sensor_tipo
            FROM leituras l
            JOIN sensores s ON l.cod_sensor = s.cod_sensor
            WHERE l.cod_sensor = ?
            ORDER BY l.data_hora DESC
        ");
        $stmt->bind_param('i', $cod_sensor);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Codigo' => $row['cod_leituras'],
                'Sensor' => $row['sensor_nome'],
                'Tipo' => $row['sensor_tipo'],
                'Valor' => $row['valor'],
                'Unidade' => $row['unidade'],
                'Data_Hora' => $row['data_hora'],
                'Observacoes' => $row['observacoes'] ?? ''
            ];
        }
        $filename = gerarNomeArquivo('leituras_sensor', $formato);
        break;

    case 'inventario_sensores':
        $result = $conn->query("
            SELECT cod_sensor, nome, tipo, descricao, localizacao, modelo, fabricante, data_instalacao, ativo
            FROM sensores
            ORDER BY nome
        ");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Codigo' => $row['cod_sensor'],
                'Nome' => $row['nome'],
                'Tipo' => $row['tipo'],
                'Descricao' => $row['descricao'] ?? '',
                'Localizacao' => $row['localizacao'] ?? '',
                'Modelo' => $row['modelo'] ?? '',
                'Fabricante' => $row['fabricante'] ?? '',
                'Data_Instalacao' => $row['data_instalacao'],
                'Ativo' => $row['ativo'] ? 'Sim' : 'Nao'
            ];
        }
        $filename = gerarNomeArquivo('inventario_sensores', $formato);
        break;

    case 'estatisticas':
        $cod_sensor = $_GET['cod_sensor'] ?? 'todos';
        if ($cod_sensor === 'todos') {
            $result = $conn->query("
                SELECT 
                    s.cod_sensor, s.nome, s.tipo,
                    COUNT(l.cod_leituras) as total_leituras,
                    AVG(l.valor) as media,
                    MIN(l.valor) as minimo,
                    MAX(l.valor) as maximo,
                    MIN(l.data_hora) as primeira_leitura,
                    MAX(l.data_hora) as ultima_leitura
                FROM sensores s
                LEFT JOIN leituras l ON s.cod_sensor = l.cod_sensor
                GROUP BY s.cod_sensor
                ORDER BY s.nome
            ");
        } else {
            $stmt = $conn->prepare("
                SELECT 
                    s.cod_sensor, s.nome, s.tipo,
                    COUNT(l.cod_leituras) as total_leituras,
                    AVG(l.valor) as media,
                    MIN(l.valor) as minimo,
                    MAX(l.valor) as maximo,
                    MIN(l.data_hora) as primeira_leitura,
                    MAX(l.data_hora) as ultima_leitura
                FROM sensores s
                LEFT JOIN leituras l ON s.cod_sensor = l.cod_sensor
                WHERE s.cod_sensor = ?
                GROUP BY s.cod_sensor
            ");
            $stmt->bind_param('i', $cod_sensor);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Sensor' => $row['nome'],
                'Tipo' => $row['tipo'],
                'Total_Leituras' => $row['total_leituras'],
                'Media' => $row['media'] ? round($row['media'], 2) : 'N/A',
                'Minimo' => $row['minimo'] ? round($row['minimo'], 2) : 'N/A',
                'Maximo' => $row['maximo'] ? round($row['maximo'], 2) : 'N/A',
                'Primeira_Leitura' => $row['primeira_leitura'] ?? 'N/A',
                'Ultima_Leitura' => $row['ultima_leitura'] ?? 'N/A'
            ];
        }
        $filename = gerarNomeArquivo('estatisticas', $formato);
        break;

    case 'leituras_periodo':
        $data_inicio = $_GET['data_inicio'];
        $data_fim = $_GET['data_fim'];
        $stmt = $conn->prepare("
            SELECT l.cod_leituras, l.valor, l.unidade, l.data_hora, l.observacoes, s.nome as sensor_nome, s.tipo as sensor_tipo
            FROM leituras l
            JOIN sensores s ON l.cod_sensor = s.cod_sensor
            WHERE l.data_hora BETWEEN ? AND ?
            ORDER BY l.data_hora DESC
        ");
        $data_fim_full = $data_fim . ' 23:59:59';
        $stmt->bind_param('ss', $data_inicio, $data_fim_full);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Codigo' => $row['cod_leituras'],
                'Sensor' => $row['sensor_nome'],
                'Tipo' => $row['sensor_tipo'],
                'Valor' => $row['valor'],
                'Unidade' => $row['unidade'],
                'Data_Hora' => $row['data_hora'],
                'Observacoes' => $row['observacoes'] ?? ''
            ];
        }
        $filename = gerarNomeArquivo('leituras_periodo', $formato);
        break;

    case 'utilizadores':
        if ($_SESSION['user_admin'] != 1) {
            http_response_code(403);
            echo 'Acesso negado';
            exit;
        }
        $result = $conn->query("SELECT cod_utilizador, username, email, nome_completo, ADMIN FROM utilizadores ORDER BY username");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Codigo' => $row['cod_utilizador'],
                'Username' => $row['username'],
                'Email' => $row['email'],
                'Nome_Completo' => $row['nome_completo'] ?? '',
                'Admin' => $row['ADMIN'] ? 'Sim' : 'Nao'
            ];
        }
        $filename = gerarNomeArquivo('utilizadores', $formato);
        break;

    case 'todas_leituras':
        $result = $conn->query("
            SELECT l.cod_leituras, l.cod_sensor, l.valor, l.unidade, l.data_hora, l.observacoes, s.nome as sensor_nome, s.tipo as sensor_tipo
            FROM leituras l
            JOIN sensores s ON l.cod_sensor = s.cod_sensor
            ORDER BY l.data_hora DESC
        ");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Codigo' => $row['cod_leituras'],
                'Sensor' => $row['sensor_nome'],
                'Tipo' => $row['sensor_tipo'],
                'Valor' => $row['valor'],
                'Unidade' => $row['unidade'],
                'Data_Hora' => $row['data_hora'],
                'Observacoes' => $row['observacoes'] ?? ''
            ];
        }
        $filename = gerarNomeArquivo('todas_leituras', $formato);
        break;

    default:
        http_response_code(400);
        echo 'Tipo de relatório inválido';
        exit;
}

if ($formato === 'json') {
    outputJSON($data, $filename);
} elseif ($formato === 'pdf') {
    $titulo = getTituloRelatorio($tipo);
    $html = gerarHTMLPDF($titulo, $data, $tipo);
    outputPDF($html, $titulo, $filename);
} else {
    outputCSV($data, $filename);
}

function gerarHTMLPDF($titulo, $data, $tipo) {
    $html = '<!DOCTYPE html>
    <html>
    <head>
    <meta charset="utf-8">
    <title>DashBoard ESP - ' . $titulo . '</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap");
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Inter", Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; padding: 20px; max-width: 210mm; margin: 0 auto; background: white; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 3px solid #3b82f6; padding-bottom: 15px; }
        .header-logo { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 10px; }
        .header-logo i { font-size: 28px; color: #10b981; }
        .header h1 { margin: 0; color: #1f2937; font-size: 22px; font-weight: 600; }
        .header .meta { margin: 8px 0 0; color: #6b7280; font-size: 10px; }
        .header .meta span { margin: 0 10px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .stats-box { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 10px; padding: 18px; margin: 12px 0; }
        .stats-box h3 { margin: 0 0 12px 0; color: #0369a1; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
        .stats-grid { display: flex; flex-wrap: wrap; gap: 15px; }
        .stat-item { background: white; padding: 10px 14px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); min-width: 100px; }
        .stat-label { display: block; color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .stat-value { display: block; color: #1f2937; font-weight: 600; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 12px 10px; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background: #f9fafb; }
        tr:hover { background: #f3f4f6; }
        .footer { margin-top: 25px; text-align: center; color: #9ca3af; font-size: 9px; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .footer .brand { color: #3b82f6; font-weight: 600; }
        .no-data { text-align: center; padding: 40px; color: #6b7280; font-style: italic; }
        @media print {
            body { padding: 0; }
            .stats-box { break-inside: avoid; }
            table { break-inside: avoid; }
        }
    </style>
    </head>
    <body>
    <div class="header">
        <div class="header-logo"><i class="fas fa-leaf"></i><span style="font-size:20px;font-weight:600;color:#10b981;">DashBoard ESP</span></div>
        <h1>' . $titulo . '</h1>
        <div class="meta"><span>Gerado: ' . date('d/m/Y \à\s H:i') . '</span></div>
    </div>';
    
    if ($tipo === 'estatisticas' && !empty($data)) {
        $html .= '<div class="section">';
        foreach ($data as $sensor) {
            $html .= '<div class="stats-box">
                <h3><i class="fas fa-microchip"></i> ' . htmlspecialchars($sensor['Sensor']) . ' (' . htmlspecialchars($sensor['Tipo']) . ')</h3>
                <div class="stats-grid">
                    <div class="stat-item"><span class="stat-label">Total Leituras</span><span class="stat-value">' . $sensor['Total_Leituras'] . '</span></div>
                    <div class="stat-item"><span class="stat-label">Média</span><span class="stat-value">' . $sensor['Media'] . '</span></div>
                    <div class="stat-item"><span class="stat-label">Mínimo</span><span class="stat-value">' . $sensor['Minimo'] . '</span></div>
                    <div class="stat-item"><span class="stat-label">Máximo</span><span class="stat-value">' . $sensor['Maximo'] . '</span></div>
                    <div class="stat-item"><span class="stat-label">Período</span><span class="stat-value">' . ($sensor['Primeira_Leitura'] !== 'N/A' ? date('d/m/Y', strtotime($sensor['Primeira_Leitura'])) . ' - ' . date('d/m/Y', strtotime($sensor['Ultima_Leitura'])) : 'N/A') . '</span></div>
                </div>
            </div>';
        }
        $html .= '</div>';
    } elseif (!empty($data)) {
        $headers = array_keys($data[0]);
        $html .= '<table><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $h))) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $val) {
                $html .= '<td>' . htmlspecialchars($val) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    } else {
        $html .= '<div class="no-data">Sem dados disponíveis para este relatório.</div>';
    }
    
    $html .= '<div class="footer"><span class="brand">DashBoard ESP</span> - Relatório Automático</div></body></html>';
    return $html;
}