<?php
require_once "../../config.php";
header('Content-Type: application/json; charset=utf-8');

/**
 * Intenta obtener métricas del sistema (CPU, RAM, Disco, Temperaturas)
 * Compatible con Windows y OpenHardwareMonitor si está activo
 */

try {
    // CPU %
    $cpu_usage = shell_exec('wmic cpu get loadpercentage /value');
    preg_match('/\d+/', $cpu_usage, $cpuMatch);
    $cpu = $cpuMatch[0] ?? 0;

    // RAM %
    $ram_total = shell_exec('wmic computersystem get TotalPhysicalMemory /value');
    $ram_free = shell_exec('wmic os get FreePhysicalMemory /value');
    preg_match('/\d+/', $ram_total, $tMatch);
    preg_match('/\d+/', $ram_free, $fMatch);
    $ram_total_mb = round(($tMatch[0] ?? 1) / 1048576, 2);
    $ram_free_mb = round(($fMatch[0] ?? 1) / 1024, 2);
    $ram_used_mb = round($ram_total_mb - $ram_free_mb, 2);
    $ram_percent = round(($ram_used_mb / $ram_total_mb) * 100, 1);

    // Disco (Unidad D:)
    $disk = shell_exec('wmic logicaldisk where "DeviceID=\'D:\'" get Size,FreeSpace /value');
    preg_match_all('/\d+/', $disk, $matches);
    if (count($matches[0]) >= 2) {
        $disk_free = round($matches[0][0] / (1024 ** 3), 2);
        $disk_total = round($matches[0][1] / (1024 ** 3), 2);
    } else {
        $disk_free = $disk_total = 0;
    }
    $disk_used = $disk_total - $disk_free;
    $disk_percent = ($disk_total > 0) ? round(($disk_used / $disk_total) * 100, 1) : 0;

    // Temperaturas (requiere OpenHardwareMonitor en ejecución)
    $cpu_temp = null;
    $gpu_temp = null;

    // OpenHardwareMonitor genera un archivo con métricas si activas el "remote web server"
    $monitor_json = "C:\\Program Files (x86)\\OpenHardwareMonitor\\OpenHardwareMonitorReport.txt";
    $monitor_json_alt = "C:\\ProgramData\\OpenHardwareMonitor\\OpenHardwareMonitorReport.txt";

    $temp_output = '';
    if (file_exists($monitor_json)) {
        $temp_output = file_get_contents($monitor_json);
    } elseif (file_exists($monitor_json_alt)) {
        $temp_output = file_get_contents($monitor_json_alt);
    }

    if ($temp_output) {
        // Buscar temperaturas con expresiones regulares
        if (preg_match('/CPU Core #1:.*?([0-9]+)\s*C/', $temp_output, $c)) $cpu_temp = (int)$c[1];
        if (preg_match('/GPU Core:.*?([0-9]+)\s*C/', $temp_output, $g)) $gpu_temp = (int)$g[1];
    } else {
        // Fallback: usa WMIC (aunque no siempre reporta temperatura)
        $temp_raw = shell_exec('wmic /namespace:\\\\root\\wmi PATH MSAcpi_ThermalZoneTemperature get CurrentTemperature');
        preg_match('/\d+/', $temp_raw, $t);
        if (!empty($t[0])) $cpu_temp = round(($t[0] / 10) - 273.15, 1);
    }

    $cpu_temp = $cpu_temp ?? 0;
    $gpu_temp = $gpu_temp ?? 0;

    // Resultado textual para consola
    $data = "
🔧 Estado del Sistema:
━━━━━━━━━━━━━━━━━━━━━━━
🖥 CPU: {$cpu}% de uso
💾 RAM: {$ram_used_mb} MB / {$ram_total_mb} MB ({$ram_percent}%)
📂 Disco D:: {$disk_used} GB / {$disk_total} GB ({$disk_percent}%)
🌡️ Temp CPU: {$cpu_temp} °C
🎮 Temp GPU: {$gpu_temp} °C
⏰ Hora: " . date('Y-m-d H:i:s');

    echo json_encode([
        "status" => "success",
        "data" => $data,
        "cpu" => (float)$cpu,
        "ram" => (float)$ram_percent,
        "disk" => (float)$disk_percent,
        "cpu_temp" => (float)$cpu_temp,
        "gpu_temp" => (float)$gpu_temp
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "data" => "❌ " . $e->getMessage()]);
}
