<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$historyPath = __DIR__ . '/../data/players_history.json';
if (!file_exists(dirname($historyPath))) mkdir(dirname($historyPath), 0777, true);

$timestamp = $_POST['timestamp'] ?? null;
$count = isset($_POST['count']) ? intval($_POST['count']) : null;

if (!$timestamp) { echo json_encode(['status'=>'error','message'=>'timestamp missing']); exit; }

// Leer historial existente
$data = [];
if (file_exists($historyPath)) {
    $json = file_get_contents($historyPath);
    $data = json_decode($json, true) ?: [];
}

// Calcular promedio móvil (últimos 10 puntos)
function calculate_moving_average($arr, $window = 10) {
    $total = count($arr);
    if ($total == 0) return 0;
    $slice = array_slice($arr, max(0, $total - $window), $window);
    $sum = 0;
    foreach ($slice as $a) $sum += $a['count'];
    return round($sum / count($slice), 2);
}

$average = calculate_moving_average($data, 10);
$entry = [
    'timestamp' => $timestamp,
    'count' => $count,
    'average' => $average
];

$data[] = $entry;

// Limitar tamaño a últimos 500 registros (~4h si intervalo 30s)
if (count($data) > 500) $data = array_slice($data, -500);

// Guardar historial con formato bonito
file_put_contents($historyPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(['status'=>'ok','average'=>$average]);
