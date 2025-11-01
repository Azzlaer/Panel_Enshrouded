<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!file_exists($server_log_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo de log no encontrado.']);
    exit;
}

if (file_put_contents($server_log_path, '') !== false) {
    echo json_encode(['status' => 'success', 'message' => 'Log limpiado correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo limpiar el log.']);
}
