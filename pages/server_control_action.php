<?php
require_once __DIR__ . '/../config.php';

$action = $_POST['action'] ?? '';

function isServerRunning($exePath) {
    $output = [];
    exec('tasklist /FI "IMAGENAME eq ' . escapeshellarg(basename($exePath)) . '"', $output);
    foreach ($output as $line) {
        if (stripos($line, basename($exePath)) !== false) return true;
    }
    return false;
}

switch ($action) {
    case 'start':
        if (isServerRunning($server_exe_path)) {
            echo "<div class='text-warning p-3'>⚠️ El servidor ya está en ejecución.</div>";
            exit;
        }
        pclose(popen('start "" "' . $server_exe_path . '"', "r"));
        echo "<div class='text-success p-3'>✅ Servidor iniciado correctamente.</div>";
        break;

    case 'stop':
        if (!isServerRunning($server_exe_path)) {
            echo "<div class='text-warning p-3'>⚠️ El servidor ya está detenido.</div>";
            exit;
        }

        // Intentar enviar comando nativo “stop”
        try {
            $proc = proc_open(
                '"' . $server_exe_path . '"',
                [['pipe','r'], ['pipe','w'], ['pipe','w']],
                $pipes
            );
            if (is_resource($proc)) {
                fwrite($pipes[0], "stop\n");
                fflush($pipes[0]);
                fclose($pipes[0]);
                sleep(5);
                proc_close($proc);
                echo "<div class='text-success p-3'>🛑 Comando \"stop\" enviado al servidor.</div>";
            } else {
                echo "<div class='text-danger p-3'>❌ No se pudo comunicar con el proceso del servidor.</div>";
            }
        } catch (Exception $e) {
            echo "<div class='text-danger p-3'>❌ Error al enviar comando stop: " . $e->getMessage() . "</div>";
        }
        break;

    case 'terminate':
        if (!isServerRunning($server_exe_path)) {
            echo "<div class='text-warning p-3'>⚠️ El servidor ya estaba detenido.</div>";
            exit;
        }
        exec('taskkill /F /IM ' . escapeshellarg(basename($server_exe_path)), $out, $ret);
        if ($ret === 0) {
            echo "<div class='text-danger p-3'>💀 Proceso terminado forzosamente.</div>";
        } else {
            echo "<div class='text-danger p-3'>❌ Error al terminar el proceso.</div>";
        }
        break;

    default:
        echo "<div class='text-danger p-3'>❌ Acción no válida.</div>";
}
