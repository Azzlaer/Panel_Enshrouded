<?php
require_once "../../config.php";
header('Content-Type: application/json');

$backup_dir = __DIR__ . "/../../backups/";
$file = basename($_POST['file'] ?? '');

if (!$file || !file_exists($backup_dir . $file)) {
    echo json_encode(["status" => "error", "message" => "❌ Archivo no encontrado."]);
    exit;
}

if (unlink($backup_dir . $file)) {
    echo json_encode(["status" => "success", "message" => "🗑 Backup eliminado correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ No se pudo eliminar el archivo."]);
}
