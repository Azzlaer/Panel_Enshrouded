<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

if (!file_exists($server_log_path)) {
    echo json_encode(["status" => "error", "message" => "Archivo de log no encontrado."]);
    exit;
}

$content = file_get_contents($server_log_path);
if ($content === false) {
    echo json_encode(["status" => "error", "message" => "No se pudo leer el log."]);
    exit;
}

$lines = explode("\n", $content);
$lastLines = array_slice($lines, -400);
echo json_encode([
    "status" => "success",
    "content" => implode("\n", $lastLines)
]);
