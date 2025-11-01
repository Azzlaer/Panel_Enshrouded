<?php
/**
 * ajax/clear_backups.php
 * Elimina todos los archivos de backup en la carpeta configurada.
 */

header('Content-Type: application/json');
require_once "../config.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode([
        'status' => 'error',
        'message' => '⛔ Acceso no autorizado.'
    ]);
    exit;
}

try {
    if (!is_dir($backup_directory)) {
        throw new Exception("La carpeta de backups no existe: {$backup_directory}");
    }

    $files = glob($backup_directory . DIRECTORY_SEPARATOR . "*.zip");
    $count = 0;

    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }

    if ($count > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "🧹 Se eliminaron {$count} archivos de backup."
        ]);
    } else {
        echo json_encode([
            'status' => 'info',
            'message' => "📂 No hay backups para eliminar."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Error al limpiar backups: ' . $e->getMessage()
    ]);
}
?>
