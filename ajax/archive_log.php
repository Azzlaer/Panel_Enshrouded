<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

// Verificar archivo de log
if (!isset($server_log_path) || !file_exists($server_log_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo de log no encontrado en: ' . $server_log_path]);
    exit;
}

// Verificar extensión ZIP
if (!class_exists('ZipArchive')) {
    echo json_encode(['status' => 'error', 'message' => 'La extensión ZIP no está habilitada en PHP.']);
    exit;
}

// Verificar carpeta de destino
if (!isset($log_archive_directory)) {
    $log_archive_directory = __DIR__ . '/../log_archives';
}

if (!is_dir($log_archive_directory)) {
    if (!mkdir($log_archive_directory, 0777, true)) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear carpeta de destino: ' . $log_archive_directory]);
        exit;
    }
}

// Nombre del ZIP
$timestamp = date('Y-m-d_H-i-s');
$zipName = "enshrouded_server_log_{$timestamp}.zip";
$zipPath = $log_archive_directory . DIRECTORY_SEPARATOR . $zipName;

// Crear ZIP
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo crear el archivo ZIP en: ' . $zipPath]);
    exit;
}

$zip->addFile($server_log_path, basename($server_log_path));
$zip->close();

// Vaciar el log original
file_put_contents($server_log_path, '');

// Enviar respuesta
echo json_encode([
    'status' => 'success',
    'message' => 'Log archivado correctamente.',
    'archive' => $zipName,
    'path' => $zipPath
]);
exit;
