<?php
require_once "../../config.php";
header('Content-Type: application/json; charset=utf-8');

try {
    if (!file_exists($server_log_path)) {
        echo json_encode(["status" => "error", "message" => "No se encontró el archivo de log."]);
        exit;
    }

    if (file_put_contents($server_log_path, "") !== false) {
        echo json_encode(["status" => "success", "message" => "🧾 Log limpiado correctamente."]);
    } else {
        throw new Exception("No se pudo limpiar el log.");
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "❌ " . $e->getMessage()]);
}
