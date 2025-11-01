<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!isset($server_log_path) || empty($server_log_path)) {
    echo json_encode(["status" => "error", "message" => "Ruta del log no definida en config.php"]);
    exit;
}

if (!file_exists($server_log_path)) {
    echo json_encode(["status" => "error", "message" => "Archivo de log no encontrado: $server_log_path"]);
    exit;
}

if (!is_writable($server_log_path)) {
    echo json_encode(["status" => "error", "message" => "El archivo no tiene permisos de escritura."]);
    exit;
}

try {
    file_put_contents($server_log_path, "");
    echo json_encode(["status" => "success", "message" => "🧹 Log limpiado correctamente."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al limpiar el log: " . $e->getMessage()]);
}
