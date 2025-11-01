<?php
require_once __DIR__ . '/../config.php';

$logPath = $server_log_path;
$stateFile = __DIR__ . '/../data/online_state.json';
$previousState = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];

$users = [];
$logHistory = [];

if (file_exists($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match("/Player '(.+)' logged in/i", $line, $m)) {
            $users[$m[1]] = [
                'status' => 'online',
                'loginTime' => $previousState[$m[1]]['loginTime'] ?? date('Y-m-d H:i:s'),
            ];
            $logHistory[] = ['time' => date('H:i:s'), 'user' => $m[1], 'event' => 'connected'];
        } elseif (preg_match("/Remove Player '(.+)'/i", $line, $m)) {
            unset($users[$m[1]]);
            $logHistory[] = ['time' => date('H:i:s'), 'user' => $m[1], 'event' => 'disconnected'];
        }
    }
}

// Guardar estado actual
file_put_contents($stateFile, json_encode($users, JSON_PRETTY_PRINT));

// HTML para la tabla
ob_start();
if (empty($users)) {
    echo "<div class='text-center text-secondary p-3'>❌ No hay jugadores conectados actualmente.</div>";
} else {
    echo "<table class='table table-dark table-striped table-bordered align-middle'>";
    echo "<thead><tr><th>Jugador</th><th>Estado</th><th>Tiempo Conectado</th></tr></thead><tbody>";
    foreach ($users as $name => $info) {
        $timeOnline = strtotime(date('Y-m-d H:i:s')) - strtotime($info['loginTime']);
        $h = floor($timeOnline / 3600);
        $m = floor(($timeOnline % 3600) / 60);
        $s = $timeOnline % 60;
        echo "<tr>
                <td class='fw-bold'>" . htmlspecialchars($name) . "</td>
                <td class='text-success'>🟢 En línea</td>
                <td>" . sprintf("%02dh %02dm %02ds", $h, $m, $s) . "</td>
              </tr>";
    }
    echo "</tbody></table>";
}
$html = ob_get_clean();

// Historial (últimos 10 eventos)
ob_start();
foreach (array_slice(array_reverse($logHistory), 0, 10) as $event) {
    echo "<div>[{$event['time']}] " . htmlspecialchars($event['user']) . " " .
         ($event['event'] === 'connected' ? "<span class='text-success'>se conectó 🟢</span>" : "<span class='text-danger'>se desconectó 🔴</span>") .
         "</div>";
}
$history = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
  'html'      => $html,
  'history'   => $history,
  'time'      => date('H:i:s'),
  'usernames' => array_keys($users),
  'point'     => $point,
'alertPlayers' => array_reduce(array_keys($users), function($carry, $name) use ($users) {
    $secs = time() - strtotime($users[$name]['loginTime']);
    if ($secs >= 10800) {
        $carry[$name] = sprintf("%02dh %02dm %02ds", floor($secs/3600), floor(($secs%3600)/60), $secs%60);
    }
    return $carry;
}, []),
