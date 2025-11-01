<?php
require_once "../config.php";
header('Content-Type: application/json');

try {
    // Comprobar si el proceso existe
    $processCheck = shell_exec('tasklist /FI "IMAGENAME eq enshrouded_server.exe" 2>NUL');
    $isRunning = strpos($processCheck, "enshrouded_server.exe") !== false;

    if (!$isRunning) {
        echo json_encode([
            'status' => 'offline',
            'message' => 'El servidor no está en ejecución.'
        ]);
        exit;
    }

    // Leer uso de CPU con WMIC
    $cpuRaw = shell_exec('wmic cpu get loadpercentage /value');
    preg_match('/LoadPercentage=(\d+)/', $cpuRaw, $cpuMatch);
    $cpuUsage = isset($cpuMatch[1]) ? (int)$cpuMatch[1] : 0;

    // Leer memoria con WMIC
    $ramRaw = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
    preg_match('/FreePhysicalMemory=(\d+)/', $ramRaw, $freeMatch);
    preg_match('/TotalVisibleMemorySize=(\d+)/', $ramRaw, $totalMatch);

    $free = (int)($freeMatch[1] ?? 0);
    $total = (int)($totalMatch[1] ?? 1);
    $ramUsage = $total > 0 ? round((1 - ($free / $total)) * 100, 2) : 0;

    // Prevenir valores fuera de rango
    if ($ramUsage < 0 || $ramUsage > 100) $ramUsage = 0;

    echo json_encode([
        'status' => 'online',
        'cpu' => $cpuUsage,
        'ram' => $ramUsage
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo contactar al servidor.',
        'debug' => $e->getMessage()
    ]);
}
?>
