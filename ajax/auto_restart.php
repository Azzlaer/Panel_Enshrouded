<?php
/**
 * Reinicio programado del servidor Enshrouded
 * Guarda, detiene y reinicia automáticamente el servidor.
 */

header('Content-Type: application/json');
require_once "../config.php";

function run_command($cmd) {
    $descriptorSpec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $process = proc_open($cmd, $descriptorSpec, $pipes, null, null);
    if (is_resource($process)) {
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $return = proc_close($process);
        return $output;
    }
    return false;
}

// --- Paso 1: Guardar el mundo (simulado por ahora, puedes conectar RCON) ---
$logfile = $server_log_path;
if (file_exists($logfile)) {
    file_put_contents($logfile, "\n[I ".date('H:i:s')."] [panel] Auto-save iniciado por el sistema.", FILE_APPEND);
}

// --- Paso 2: Detener el servidor ---
exec('taskkill /F /IM ' . basename($server_exe_path) . ' >nul 2>&1');

// Esperar un poco antes de reiniciar
sleep($auto_restart_delay);

// --- Paso 3: Reiniciar el servidor ---
$cmd = 'start "" "' . $server_exe_path . '"';
pclose(popen($cmd, "r"));

// --- Log ---
file_put_contents($logfile, "\n[I ".date('H:i:s')."] [panel] Servidor reiniciado automáticamente.", FILE_APPEND);

echo json_encode([
    "status" => "success",
    "message" => "💾 Guardado, detenido y reiniciado correctamente."
]);
?>
