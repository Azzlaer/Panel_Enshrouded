<?php
require_once __DIR__ . '/../config.php';

$action = $_POST['action'] ?? '';

function runSteamCmd($steamcmd_path, $server_dir, $extra = '') {
    $cmd = "\"$steamcmd_path\" +login anonymous +app_update 2278520 $extra +quit";
    $descriptorspec = [
        1 => ['pipe', 'w'], // STDOUT
        2 => ['pipe', 'w']  // STDERR
    ];
    $process = proc_open($cmd, $descriptorspec, $pipes, $server_dir);
    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]);
        $error  = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        $msg = nl2br(htmlspecialchars($output . "\n" . $error));
        $msg .= "<br><strong>Resultado:</strong> " . ($exit === 0 ? "✅ Correcto" : "⚠️ Código $exit");
        return "<pre style='color:#00ff99;'>$msg</pre>";
    }
    return "<div class='text-danger p-3'>❌ Error al ejecutar SteamCMD.</div>";
}

switch ($action) {
    case 'check':
        echo "<div class='p-3 text-info'>🔍 Verificando si hay actualizaciones disponibles...</div>";
        echo runSteamCmd($steamcmd_path, dirname($server_exe_path));
        break;

    case 'update':
        echo "<div class='p-3 text-warning'>⬇️ Iniciando actualización completa del servidor...</div>";
        echo runSteamCmd($steamcmd_path, dirname($server_exe_path), "validate");
        break;

    case 'validate':
        echo "<div class='p-3 text-warning'>🧠 Validando integridad de los archivos del servidor...</div>";
        echo runSteamCmd($steamcmd_path, dirname($server_exe_path), "validate");
        break;

    default:
        echo "<div class='text-danger p-3'>❌ Acción no válida.</div>";
}
