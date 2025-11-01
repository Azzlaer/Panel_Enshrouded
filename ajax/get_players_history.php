<?php
require_once __DIR__ . '/../config.php';

$historyPath = __DIR__ . '/../data/players_history.json';
if (!file_exists($historyPath)) {
    die("No hay historial disponible.");
}

$format = $_GET['format'] ?? 'json';
$json = json_decode(file_get_contents($historyPath), true) ?: [];

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="players_history.csv"');
    echo "timestamp,count,average\n";
    foreach ($json as $row) {
        echo "{$row['timestamp']},{$row['count']},{$row['average']}\n";
    }
} else {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="players_history.json"');
    echo json_encode($json, JSON_PRETTY_PRINT);
}
