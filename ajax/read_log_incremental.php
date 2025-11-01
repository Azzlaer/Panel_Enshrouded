<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : (isset($_POST['offset']) ? intval($_POST['offset']) : 0);

if (!file_exists($server_log_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Archivo de log no encontrado.']);
    exit;
}

$filesize = filesize($server_log_path);

if ($filesize < $offset) {
    // El archivo fue truncado o limpiado
    $offset = 0;
}

$fp = fopen($server_log_path, 'rb');
if (!$fp) {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el log.']);
    exit;
}

fseek($fp, $offset);
$newData = fread($fp, $filesize - $offset);
fclose($fp);

echo json_encode([
    'status' => 'success',
    'new_content' => $newData,
    'new_size' => $filesize
]);
