<?php
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
