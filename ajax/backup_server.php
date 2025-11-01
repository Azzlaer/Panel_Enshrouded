<?php
/**
 * ajax/backup_server.php
 * Crea un backup comprimido del servidor Enshrouded
 */

header('Content-Type: application/json');
require_once "../config.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '⛔ Acceso no autorizado.']);
    exit;
}

try {
    // Verificar existencia de directorios
    if (!is_dir($backup_directory)) {
        mkdir($backup_directory, 0777, true);
    }

    $timestamp = date('Y-m-d_H-i-s');
    $zip_filename = $backup_directory . "\\backup_" . $timestamp . ".zip";

    $zip = new ZipArchive();
    if ($zip->open($zip_filename, ZipArchive::CREATE) !== true) {
        throw new Exception('No se pudo crear el archivo ZIP.');
    }

    // Agregar los archivos y carpetas definidos en $backup_sources
    foreach ($backup_sources as $source) {
        if (file_exists($source)) {
            $sourceReal = realpath($source);
            if (is_dir($sourceReal)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($sourceReal, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($files as $file) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($sourceReal) + 1);
                    if ($file->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            } else {
                $zip->addFile($sourceReal, basename($sourceReal));
            }
        }
    }

    $zip->close();

    // Limitar a los últimos 5 backups
    $backups = glob($backup_directory . "\\backup_*.zip");
    rsort($backups);
    if (count($backups) > 5) {
        foreach (array_slice($backups, 5) as $old) {
            unlink($old);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => '✅ Backup creado correctamente: ' . basename($zip_filename)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Error al crear backup: ' . $e->getMessage()
    ]);
}
?>
