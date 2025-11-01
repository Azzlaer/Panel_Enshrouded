<?php
require_once __DIR__ . '/../config.php';

if (!file_exists($server_log_path)) {
    echo "<div class='text-danger'>❌ Archivo de log no encontrado.</div>";
    exit;
}

$lines = @file($server_log_path);
$display_lines = $lines ? array_slice($lines, -200) : [];

foreach ($display_lines as $line) {
    echo htmlspecialchars(trim($line)) . "\n";
}
