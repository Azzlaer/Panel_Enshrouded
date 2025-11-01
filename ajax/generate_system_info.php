<?php
/**
 * ajax/generate_system_info.php
 * Obtiene información del sistema (host, CPU, RAM, Disco, etc.)
 * Compatible con Windows 10/11 (sin depender de WMIC)
 */

header('Content-Type: application/json');
require_once "../config.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '⛔ Acceso no autorizado.']);
    exit;
}

try {
    $info = [];

    // Hostname e IP local
    $info['hostname'] = gethostname();
    $info['ip_local'] = gethostbyname($info['hostname']);
    $info['os_info'] = php_uname('s') . " " . php_uname('r');
    $info['php_version'] = phpversion();

    // === CPU NÚCLEOS ===
    $cpu_cores = null;

    // Intentar con WMIC
    $cpu_output = shell_exec('wmic cpu get NumberOfCores /value 2>nul');
    if ($cpu_output && preg_match('/NumberOfCores=(\d+)/i', $cpu_output, $m)) {
        $cpu_cores = (int)$m[1];
    }

    // Si WMIC falla, usar PowerShell
    if (!$cpu_cores) {
        $ps_output = shell_exec('powershell -Command "(Get-CimInstance Win32_Processor).NumberOfCores" 2>nul');
        $ps_output = trim($ps_output);
        if (is_numeric($ps_output)) {
            $cpu_cores = (int)$ps_output;
        }
    }

    // Si aún no se obtiene, usar fallback de PHP
    if (!$cpu_cores) {
        $cpu_cores = function_exists('shell_exec') ? (int)trim(shell_exec("echo %NUMBER_OF_PROCESSORS%")) : "N/A";
    }

    $info['cpu_cores'] = $cpu_cores ?: "N/A";

    // === RAM TOTAL ===
    $ram_total = null;

    // Intentar con WMIC
    $ram_output = shell_exec('wmic computersystem get TotalPhysicalMemory /value 2>nul');
    if ($ram_output && preg_match('/TotalPhysicalMemory=(\d+)/i', $ram_output, $m)) {
        $ram_total = round($m[1] / 1073741824, 2);
    }

    // Si WMIC falla, usar PowerShell
    if (!$ram_total) {
        $ps_ram = shell_exec('powershell -Command "(Get-CimInstance Win32_ComputerSystem).TotalPhysicalMemory" 2>nul');
        if (is_numeric(trim($ps_ram))) {
            $ram_total = round(((float)trim($ps_ram)) / 1073741824, 2);
        }
    }

    // Si no hay nada, dejar como N/A
    $info['ram_total'] = $ram_total ?: "N/A";

    // === DISCO ===
    $drive = 'D:';
    if (!is_dir($drive)) $drive = 'C:';

    $total = disk_total_space($drive);
    $free = disk_free_space($drive);
    $used = $total - $free;

    $info['disk_total_gb'] = round($total / 1073741824, 2);
    $info['disk_free_gb'] = round($free / 1073741824, 2);
    $info['disk_used_percent'] = round(($used / $total) * 100, 1);

    // Fecha
    $info['generated_at'] = date('d/m/Y H:i:s');

    // === Guardar JSON ===
    $data_dir = __DIR__ . '/../data';
    if (!is_dir($data_dir)) mkdir($data_dir, 0777, true);
    $file = $data_dir . '/system_info.json';
    file_put_contents($file, json_encode($info, JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'success', 'message' => '✅ Información del sistema actualizada correctamente.']);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '❌ Error al obtener la información: ' . $e->getMessage()
    ]);
}
