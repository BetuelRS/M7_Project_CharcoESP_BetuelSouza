<?php
function load_env($file = null) {
    if ($file === null) {
        $file = __DIR__ . '/../.env';
    }
    if (!file_exists($file)) return;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    return $value !== false && $value !== null ? $value : $default;
}

function validar_valor_unidade($valor, $unidade) {
    $erros = [];
    if (!is_numeric($valor) && $valor !== '') {
        $erros[] = "Valor deve ser um número";
        return $erros;
    }
    $num = (float)$valor;
    switch ($unidade) {
        case '%':
            if ($num < 0 || $num > 100) $erros[] = "Percentagem deve estar entre 0 e 100%";
            break;
        case '°C':
            if ($num < -50 || $num > 150) $erros[] = "Temperatura deve estar entre -50°C e 150°C";
            break;
        case 'cm':
        case 'm':
            if ($num < 0) $erros[] = "Valor não pode ser negativo";
            break;
        case 'lux':
            if ($num < 0 || $num > 100000) $erros[] = "Iluminância deve estar entre 0 e 100.000 lux";
            break;
        case 'µg/m3':
            if ($num < 0 || $num > 500) $erros[] = "Concentração deve estar entre 0 e 500 µg/m³";
            break;
    }
    return $erros;
}

function tipo_para_unidade($tipo) {
    $mapa = [
        'Temperatura' => '°C',
        'Humidade' => '%',
        'Luminosidade' => 'lux',
        'Qualidade do Ar' => 'µg/m3',
        'Nível da Água' => 'cm'
    ];
    return $mapa[$tipo] ?? '';
}

function tipo_para_icone($tipo) {
    $mapa = [
        'Temperatura' => 'fa-thermometer-half',
        'Humidade' => 'fa-tint',
        'Luminosidade' => 'fa-lightbulb',
        'Qualidade do Ar' => 'fa-wind',
        'Nível da Água' => 'fa-water'
    ];
    return $mapa[$tipo] ?? 'fa-chart-line';
}

function tipos_ordenados() {
    return ['Temperatura', 'Humidade', 'Luminosidade', 'Qualidade do Ar', 'Nível da Água'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function totp_gerar_secreto() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < 32; $i++) {
        $secret .= $chars[random_int(0, 31)];
    }
    return $secret;
}

function totp_calcular($secret, $time = null) {
    if ($time === null) $time = time();
    $counter = pack('N*', 0) . pack('N*', (int)($time / 30));
    $secret_decoded = totp_base32_decode($secret);
    $hash = hash_hmac('sha1', $counter, $secret_decoded, true);
    $offset = ord($hash[19]) & 0xf;
    $code = (
        ((ord($hash[$offset]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff)
    ) % 1000000;
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

function totp_verificar($secret, $code) {
    $time = time();
    for ($i = -1; $i <= 1; $i++) {
        if (hash_equals(totp_calcular($secret, $time + $i * 30), $code)) {
            return true;
        }
    }
    return false;
}

function totp_base32_decode($input) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $input = strtoupper($input);
    $output = '';
    $buffer = 0;
    $bitsLeft = 0;
    for ($i = 0; $i < strlen($input); $i++) {
        $val = strpos($alphabet, $input[$i]);
        if ($val === false) continue;
        $buffer = ($buffer << 5) | $val;
        $bitsLeft += 5;
        if ($bitsLeft >= 8) {
            $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xff);
            $bitsLeft -= 8;
        }
    }
    return $output;
}

function registrar_auditoria($conn, $utilizador_id, $acao, $entidade, $entidade_id, $detalhes = null) {
    $stmt = $conn->prepare("INSERT INTO auditoria (utilizador_id, acao, entidade, entidade_id, detalhes, ip) VALUES (?, ?, ?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt->bind_param("ississ", $utilizador_id, $acao, $entidade, $entidade_id, $detalhes, $ip);
    $stmt->execute();
    $stmt->close();
}
