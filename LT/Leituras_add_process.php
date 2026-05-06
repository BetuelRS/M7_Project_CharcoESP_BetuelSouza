<?php
require_once __DIR__ . '/../config.php';
include BASE_PATH . 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'LT/Leituras.php');
    exit();
}

// Recebe e valida dados
$cod_sensor = isset($_POST['cod_sensor']) ? (int)$_POST['cod_sensor'] : 0;
$valor_str = $_POST['valor'] ?? '';
$valor = floatval($valor_str);
$unidade = trim($_POST['unidade'] ?? '');
$data_hora = $_POST['data_hora'] ?? '';
$observacoes = trim($_POST['observacoes'] ?? '');

// Validações server-side
$erros = [];

if ($cod_sensor <= 0) {
    $erros[] = "Sensor inválido";
}
if ($valor_str === '' || $valor_str === null) {
    $erros[] = "Valor é obrigatório";
} elseif (!is_numeric($valor_str)) {
    $erros[] = "Valor deve ser um número";
} else {
    // Validação por unidade
    switch ($unidade) {
        case '%':
            if ($valor < 0 || $valor > 100) {
                $erros[] = "Percentagem deve estar entre 0 e 100%";
            }
            break;
        case '°C':
            if ($valor < -50 || $valor > 150) {
                $erros[] = "Temperatura deve estar entre -50°C e 150°C";
            }
            break;
        case 'cm':
        case 'm':
            if ($valor < 0) {
                $erros[] = "Comprimento não pode ser negativo";
            }
            break;
        case 'Lux':
            if ($valor < 0 || $valor > 100000) {
                $erros[] = "Iluminância deve estar entre 0 e 100.000 Lux";
            }
            break;
        case 'µg/m3':
            if ($valor < 0 || $valor > 500) {
                $erros[] = "Concentração deve estar entre 0 e 500 µg/m³";
            }
            break;
    }
}
if (empty($unidade)) {
    $erros[] = "Unidade é obrigatória";
}
if (empty($data_hora)) {
    $erros[] = "Data/Hora é obrigatória";
} else {
    $date_check = DateTime::createFromFormat('Y-m-d\TH:i', $data_hora);
    if (!$date_check) {
        $erros[] = "Data/Hora inválida";
    }
}

if (!empty($erros)) {
    $_SESSION['erros_validacao'] = $erros;
    header('Location: ' . BASE_URL . 'LT/Leituras_add.php?erro=validacao');
    exit();
}

// Prepared statement
$stmt = $conn->prepare("INSERT INTO leituras (cod_sensor, valor, unidade, data_hora, observacoes) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("idsss", $cod_sensor, $valor, $unidade, $data_hora, $observacoes);

if ($stmt->execute()) {
    // Sucesso
    header('Location: ' . BASE_URL . 'LT/Leituras.php?msg=adicionado');
} else {
    // Erro
    header('Location: ' . BASE_URL . 'LT/leituras_add.php?erro=bd');
}
$stmt->close();
$conn->close();