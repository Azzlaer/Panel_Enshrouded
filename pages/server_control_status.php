<?php
require_once __DIR__ . '/../config.php';
function isServerRunning($exePath) {
    $output = [];
    exec('tasklist /FI "IMAGENAME eq ' . escapeshellarg(basename($exePath)) . '"', $output);
    foreach ($output as $line) {
        if (stripos($line, basename($exePath)) !== false) return true;
    }
    return false;
}
$isRunning = isServerRunning($server_exe_path);
echo $isRunning
  ? "<span class='text-success fw-bold'>🟢 En línea</span>"
  : "<span class='text-danger fw-bold'>🔴 Apagado</span>";
