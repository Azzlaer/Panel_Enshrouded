<?php
/**
 * ajax/auto_backup.php
 * Ejecuta backups automáticos cada 24 horas o cuando se solicite manualmente.
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

// Archivo donde se guarda la hora del último backup
$timestamp_file = $backup_directory . "\\last_auto_backup.txt";
$interval_hours = 24;

// Verificar si ha pasado el tiempo
$now = time();
$last_backup_time = file_exists($timestamp_file) ? (int)file_get_contents($timestamp_file) : 0;
$hours_elapsed = ($now - $last_backup_time) / 3600;

if ($hours_elapsed >= $interval_hours) {
    // Ejecutar un nuevo backup
    include_once "backup_server.php";

    // Actualizar timestamp
    file_put_contents($timestamp_file, $now);

    echo json_encode([
        'status' => 'success',
        'message' => '✅ Backup automático completado correctamente.'
    ]);
} else {
    echo json_encode([
        'status' => 'waiting',
        'message' => '⌛ El último backup fue hace ' . round($hours_elapsed, 2) . ' horas. Próximo en ' . round($interval_hours - $hours_elapsed, 2) . ' horas.'
    ]);
}
?>
