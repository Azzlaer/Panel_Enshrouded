<?php
/**
 * ajax/cron_update_system.php
 * Verifica la última actualización de system_info.json y la regenera si es antigua
 */
header('Content-Type: application/json; charset=utf-8');
require_once "../config.php";

$data_dir = dirname(__DIR__) . "/data";
$json_file = "$data_dir/system_info.json";
$interval_hours = 2; // tiempo entre actualizaciones automáticas

// Crear carpeta data si no existe
if (!is_dir($data_dir)) mkdir($data_dir, 0777, true);

$needs_update = true;
if (file_exists($json_file)) {
    $last_modified = filemtime($json_file);
    $elapsed = time() - $last_modified;
    $needs_update = $elapsed > ($interval_hours * 3600);
}

if (!$needs_update) {
    echo json_encode(["status" => "ok", "message" => "Sin cambios, datos recientes."]);
    exit;
}

// Ejecutar generate_system_info.php
ob_start();
include "generate_system_info.php";
$output = ob_get_clean();

echo $output;
