<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$logPath = $server_log_path;

if (!file_exists($logPath)) {
    echo json_encode(['status'=>'error','message'=>"Archivo de log no encontrado"]);
    exit;
}

$lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines) {
    echo json_encode(['status'=>'error','message'=>"No se pudo leer el log"]);
    exit;
}

$players = [];
foreach (array_reverse($lines) as $line) {
    if (preg_match("/Player '(.+?)'.*?SteamID[:=]\s*(\d{17})/i", $line, $m)) {
        $name = trim($m[1]);
        $id = trim($m[2]);
        if (!isset($players[$id])) {
            $players[$id] = $name;
            if (count($players) >= 50) break;
        }
    }
}

if (empty($players)) {
    echo json_encode(['status'=>'empty','message'=>'No se detectaron jugadores recientes']);
} else {
    $data = [];
    foreach ($players as $sid => $n) $data[] = ['steamid'=>$sid, 'player'=>$n];
    echo json_encode(['status'=>'success','players'=>$data]);
}
