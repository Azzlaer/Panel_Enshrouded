<?php
require_once __DIR__ . '/../config.php';

$logPath = isset($_GET['file']) ? $_GET['file'] : $server_log_path;
$result = ['players' => [], 'events' => []];

if (!file_exists($logPath)) {
    $error = "❌ No se encontró el archivo de log en: $logPath";
} else {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $users = [];
    $steamLinks = [];
    $lastSteam = null;

    foreach ($lines as $line) {
        // SteamID autenticado
        if (preg_match("/Client '([0-9]+)' authenticated by steam/i", $line, $m)) {
            $lastSteam = $m[1];
            $steamLinks[$lastSteam] = $steamLinks[$lastSteam] ?? null;
        }

        // Jugador conectado
        elseif (preg_match("/Player '(.+)' logged in/i", $line, $m)) {
            $name = trim($m[1]);
            if (preg_match('/^\d+\(\d+\)$/', $name)) continue;

            $steamId = $lastSteam ?? '';
            if ($steamId && !isset($steamLinks[$steamId])) {
                $steamLinks[$steamId] = $name;
            }

            $users[$steamId] = [
                'name' => $name,
                'platform' => $steamId ? 'Steam' : 'Unknown',
                'steamid' => $steamId,
                'loginTime' => date('Y-m-d H:i:s'),
                'status' => isset($users[$steamId]) ? 'reconnected' : 'connected',
            ];

            $result['events'][] = [
                'time' => date('H:i:s'),
                'user' => $name,
                'steamid' => $steamId,
                'event' => $users[$steamId]['status'],
            ];

            $lastSteam = null;
        }

        // Jugador desconectado
        elseif (preg_match("/Remove Player '(.+)'/i", $line, $m)) {
            $name = trim($m[1]);
            if (preg_match('/^\d+\(\d+\)$/', $name)) continue;

            $steamId = array_search($name, $steamLinks) ?: '';

            if (isset($users[$steamId])) {
                $users[$steamId]['status'] = 'disconnected';
            }

            $result['events'][] = [
                'time' => date('H:i:s'),
                'user' => $name,
                'steamid' => $steamId,
                'event' => 'disconnected',
            ];
        }
    }

    $result['players'] = $users;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Analizador de Log - Enshrouded</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#121212; color:#eee; }
    .container { max-width:1200px; }
    pre { background:#000; color:#0f0; padding:10px; border-radius:8px; }
    .table-dark th { background:#2a2a2a; }
  </style>
</head>
<body>
<div class="container mt-4">
  <h2 class="text-info mb-3">🧠 Analizador de Log - Enshrouded</h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php else: ?>

    <h4 class="mt-4">👥 Jugadores detectados</h4>
    <?php if (empty($result['players'])): ?>
      <div class="text-secondary">No se detectaron jugadores en el log.</div>
    <?php else: ?>
      <table class="table table-dark table-striped table-bordered mt-3">
        <thead>
          <tr>
            <th>Jugador</th>
            <th>Plataforma</th>
            <th>ID Steam</th>
            <th>Tiempo conectado (simulado)</th>
            <th>Último evento</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($result['players'] as $p): 
          $secs = rand(60, 3600);
          $h = floor($secs/3600);
          $m = floor(($secs%3600)/60);
          $s = $secs%60;
          $statusText = match($p['status']) {
            'connected' => '🟢 Conectado',
            'reconnected' => '🟡 Reconectado',
            'disconnected' => '🔴 Desconectado',
            default => '⚪ Desconocido',
          };
        ?>
          <tr>
            <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['platform']) ?></td>
            <td><?= htmlspecialchars($p['steamid']) ?></td>
            <td><?= sprintf("%02dh %02dm %02ds", $h,$m,$s) ?></td>
            <td><?= $statusText ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h4 class="mt-5">📜 Eventos detectados</h4>
    <?php if (empty($result['events'])): ?>
      <div class="text-secondary">No se detectaron eventos de conexión o desconexión.</div>
    <?php else: ?>
      <pre>
<?php foreach ($result['events'] as $e): 
    $steamTxt = $e['steamid'] ? " ({$e['steamid']})" : '';
    $icon = match($e['event']) {
        'connected' => '🟢',
        'reconnected' => '🟡',
        'disconnected' => '🔴',
        default => '⚪'
    };
?>
[<?= $e['time'] ?>] <?= htmlspecialchars($e['user']) . $steamTxt ?> <?= $e['event']==='disconnected'?'se desconectó':'se conectó' ?> <?= $icon ?> 
<?php endforeach; ?>
      </pre>
    <?php endif; ?>

  <?php endif; ?>
</div>
</body>
</html>
