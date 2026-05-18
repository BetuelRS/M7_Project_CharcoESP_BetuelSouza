<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . 'includes/functions.php';
include BASE_PATH . 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['user_admin']) {
    header('Location: ' . BASE_URL . 'index.php?erro=admin');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'LT/importar.php');
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['import_result'] = ['sucesso' => 0, 'erros_count' => 1, 'total' => 0, 'erros' => ['Erro de validação.']];
    header('Location: ' . BASE_URL . 'LT/importar.php');
    exit;
}

$file = $_FILES['ficheiro'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['import_result'] = ['sucesso' => 0, 'erros_count' => 1, 'total' => 0, 'erros' => ['Erro ao enviar ficheiro.']];
    header('Location: ' . BASE_URL . 'LT/importar.php');
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$content = file_get_contents($file['tmp_name']);

if ($ext === 'csv') {
    $rows = parse_csv($content);
} elseif ($ext === 'json') {
    $rows = json_decode($content, true);
    if (!is_array($rows)) {
        $_SESSION['import_result'] = ['sucesso' => 0, 'erros_count' => 1, 'total' => 0, 'erros' => ['JSON inválido.']];
        header('Location: ' . BASE_URL . 'LT/importar.php');
        exit;
    }
} else {
    $_SESSION['import_result'] = ['sucesso' => 0, 'erros_count' => 1, 'total' => 0, 'erros' => ['Formato não suportado. Use CSV ou JSON.']];
    header('Location: ' . BASE_URL . 'LT/importar.php');
    exit;
}

$sucesso = 0;
$erros = [];

$stmt = $conn->prepare("INSERT INTO leituras (cod_sensor, valor, unidade, data_hora, observacoes) VALUES (?, ?, ?, ?, ?)");

foreach ($rows as $i => $row) {
    $linha = $i + 1;
    $cod_sensor = (int)($row['cod_sensor'] ?? 0);
    $valor_str = $row['valor'] ?? '';
    $unidade = trim($row['unidade'] ?? '');
    $data_hora = $row['data_hora'] ?? '';
    $observacoes = trim($row['observacoes'] ?? '');

    $linha_erros = [];

    if ($cod_sensor <= 0) $linha_erros[] = "Sensor inválido";
    if ($valor_str === '' || $valor_str === null) $linha_erros[] = "Valor obrigatório";
    elseif (!is_numeric($valor_str)) $linha_erros[] = "Valor não é número";
    if (empty($unidade)) $linha_erros[] = "Unidade obrigatória";
    if (empty($data_hora)) $linha_erros[] = "Data/Hora obrigatória";

    if (empty($linha_erros)) {
        $valor = (float)$valor_str;
        $val_erros = validar_valor_unidade($valor, $unidade);
        if (!empty($val_erros)) {
            $linha_erros = array_merge($linha_erros, $val_erros);
        }
    }

    if (!empty($linha_erros)) {
        $erros[] = "Linha $linha: " . implode('; ', $linha_erros);
        continue;
    }

    $stmt->bind_param("idsss", $cod_sensor, $valor, $unidade, $data_hora, $observacoes);
    if ($stmt->execute()) {
        $sucesso++;
    } else {
        $erros[] = "Linha $linha: erro BD ao inserir";
    }
}

$stmt->close();
$conn->close();

$_SESSION['import_result'] = [
    'sucesso' => $sucesso,
    'erros_count' => count($erros),
    'total' => count($rows),
    'erros' => $erros
];

if ($sucesso > 0) {
    registrar_auditoria($conn ?? null, $_SESSION['user_id'], 'importar', 'leitura', 0, "Importadas $sucesso leituras de " . $file['name']);
}

header('Location: ' . BASE_URL . 'LT/importar.php');
exit;

function parse_csv($content) {
    $lines = explode("\n", trim($content));
    if (empty($lines)) return [];
    $headers = str_getcsv(array_shift($lines), ';');
    $headers = array_map('trim', $headers);
    $rows = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $vals = str_getcsv($line, ';');
        $row = [];
        foreach ($headers as $i => $h) {
            $row[$h] = $vals[$i] ?? '';
        }
        $rows[] = $row;
    }
    return $rows;
}
