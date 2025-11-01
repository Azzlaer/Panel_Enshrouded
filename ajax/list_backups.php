<?php
require_once "../config.php";

header('Content-Type: application/json; charset=utf-8');

try {
    if (!is_dir($backup_directory)) {
        throw new Exception("No existe la carpeta de backups.");
    }

    $files = glob($backup_directory . "\\*.zip");
    usort($files, function($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });

    $list = [];
    foreach ($files as $f) {
        $list[] = [
            'name' => basename($f),
            'size' => round(filesize($f) / 1024 / 1024, 2) . ' MB'
        ];
    }

    echo json_encode([
        "status" => "success",
        "backups" => $list
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "❌ Error: " . $e->getMessage()
    ]);
}
