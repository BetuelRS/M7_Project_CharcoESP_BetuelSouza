<?php
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../db.php';

$conn->query("TRUNCATE TABLE leituras");

$startDate = new DateTime('2026-02-11 08:00:00');
$endDate = new DateTime('2026-05-11 20:00:00');

$sensores = [
    1 => ['unidade' => '°C', 'min' => 12, 'max' => 28],
    2 => ['unidade' => '%', 'min' => 55, 'max' => 95],
    3 => ['unidade' => 'lux', 'min' => 0, 'max' => 1200],
    4 => ['unidade' => 'µg/m3', 'min' => 5, 'max' => 45],
    5 => ['unidade' => 'cm', 'min' => 35, 'max' => 45]
];

$stmt = $conn->prepare("INSERT INTO leituras (cod_sensor, valor, unidade, data_hora, observacoes) VALUES (?, ?, ?, ?, ?)");

$cod = 1;
while ($startDate <= $endDate) {
    $hour = (int)$startDate->format('H');
    $dayOfYear = (int)$startDate->format('z');
    
    foreach ($sensores as $cod_sensor => $config) {
        $baseValue = match($cod_sensor) {
            1 => 18 + 5 * sin(($hour - 6) * pi() / 12) + ($dayOfYear % 30 - 15) * 0.1,
            2 => 75 - 10 * sin(($hour - 6) * pi() / 12) - ($dayOfYear % 30 - 15) * 0.15,
            3 => ($hour >= 6 && $hour <= 20) ? max(0, 800 * sin(($hour - 6) * pi() / 14)) : 0,
            4 => 20 + sin($dayOfYear * 0.1) * 8,
            5 => 42 - ($dayOfYear - 42) * 0.02,
        };
        
        $valor = round(max($config['min'], min($config['max'], $baseValue + rand(-3, 3))), 2);
        $data = $startDate->format('Y-m-d H:i:s');
        
        $obs = match($cod_sensor) {
            1 => $valor < 15 ? 'Água fria' : ($valor > 25 ? 'Água quente' : null),
            2 => $valor > 85 ? 'Humidade alta' : ($valor < 60 ? 'Ar seco' : null),
            3 => $hour >= 11 && $hour <= 15 && $valor > 500 ? 'Pico solar' : ($hour < 7 || $hour > 19 ? 'Noite' : null),
            4 => $valor > 30 ? 'Qualidade moderada' : ($valor < 15 ? 'Bom' : null),
            5 => $valor > 44 ? 'Nível alto' : ($valor < 36 ? 'Nível baixo' : null),
            default => null
        };
        
        $stmt->bind_param("idsss", $cod_sensor, $valor, $config['unidade'], $data, $obs);
        $stmt->execute();
        $cod++;
    }
    
    $startDate->add(new DateInterval('PT8H'));
}

echo "Geradas " . ($cod - 1) . " Leituras!";