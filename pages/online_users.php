<?php
require_once __DIR__ . '/../config.php';

$logPath      = $server_log_path;
$stateFile    = __DIR__ . '/../data/online_state.json';
$historyFile  = __DIR__ . '/../data/online_history.json';

if (!file_exists(dirname($stateFile))) {
    mkdir(dirname($stateFile), 0777, true);
}

$prevState = file_exists($stateFile)
    ? json_decode(@file_get_contents($stateFile), true)
    : [];

// Array donde guardaremos usuarios conectados actuales
$users = [];
$logEvents = [];

// Leemos el log
if (file_exists($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Detectar SteamID autenticado
        if (preg_match("/Client '([0-9]+)' authenticated by steam/i", $line, $m)) {
            $steamId = $m[1];
            $key = $steamId;
            $users[$key] = [
                'name'       => $prevState[$key]['name']       ?? $steamId,
                'platform'   => 'Steam',
                'steamid'    => $steamId,
                'loginTime'  => $prevState[$key]['loginTime']  ?? date('Y-m-d H:i:s')
            ];
            $logEvents[] = ['time'=>date('H:i:s'),'user'=>$steamId,'event'=>'connected'];
        }
        // Detectar Player 'nombre' logged in
        elseif (preg_match("/Player '(.+)' logged in/i", $line, $m)) {
            $name = $m[1];
            // No tenemos SteamId explícito, así que usamos nombre como key
            $key = $name;
            $users[$key] = [
                'name'       => $name,
                'platform'   => 'Unknown',
                'steamid'    => $prevState[$key]['steamid'] ?? '',
                'loginTime'  => $prevState[$key]['loginTime'] ?? date('Y-m-d H:i:s')
            ];
            $logEvents[] = ['time'=>date('H:i:s'),'user'=>$name,'event'=>'connected'];
        }
        // Detectar desconexión del jugador
        elseif (preg_match("/Remove Player '(.+)'/i", $line, $m)) {
            $name = $m[1];
            // buscar tanto por SteamId como por nombre
            if (isset($users[$name])) {
                unset($users[$name]);
            } else {
                // En prevState podría estar
                foreach ($users as $k => $info) {
                    if ($info['name'] === $name) {
                        unset($users[$k]);
                        break;
                    }
                }
            }
            $logEvents[] = ['time'=>date('H:i:s'),'user'=>$name,'event'=>'disconnected'];
        }
    }
}

// Guardar estado actual
file_put_contents($stateFile, json_encode($users, JSON_PRETTY_PRINT));

// Preparar historial (aunque no lo utilizaremos en la tabla por ahora)
$series = file_exists($historyFile)
    ? json_decode(@file_get_contents($historyFile), true)
    : [];

$labels = array_column($series, 't');
$counts = array_column($series, 'count');

// Detectar alertas (más de 3h)
$alertPlayers = [];
foreach ($users as $key => $info) {
    $secs = time() - strtotime($info['loginTime']);
    if ($secs >= 10800) { // 3 horas
        $h = floor($secs/3600);
        $m = floor(($secs%3600)/60);
        $s = $secs%60;
        $alertPlayers[$info['name']] = sprintf("%02dh %02dm %02ds", $h, $m, $s);
    }
}
?>
<div class="container-fluid text-light mt-4 position-relative">
  <div id="alertBanner" class="alert alert-danger fw-bold text-center shadow-lg" 
       style="display:none; position:fixed; top:0; left:0; width:100%; z-index:1050;">
    ⚠️ <span id="alertPlayer"></span>
  </div>

  <h2 class="mb-3 mt-5">👥 Usuarios Online</h2>

  <div class="bg-dark p-3 rounded shadow-sm mb-3">
    <p><strong>Archivo log:</strong> <code><?= htmlspecialchars($logPath) ?></code></p>
    <p>Última actualización: <span id="lastUpdate"><?= date('H:i:s') ?></span></p>
  </div>

  <div class="bg-black p-3 rounded border border-secondary shadow mb-3" 
       style="max-height:380px; overflow:auto;">
    <?php if (empty($users)): ?>
      <div class="text-center text-secondary">❌ No hay jugadores conectados actualmente.</div>
    <?php else: ?>
      <table class="table table-dark table-striped table-bordered align-middle mb-0">
        <thead>
          <tr>
            <th>Jugador</th>
            <th>Plataforma</th>
            <th>ID Steam</th>
            <th>Tiempo conectado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $key => $info): 
            $secs = time() - strtotime($info['loginTime']);
            $h = floor($secs/3600); $m = floor(($secs%3600)/60); $s = $secs%60;
          ?>
            <tr class="<?= ($h >= 3 ? 'table-danger' : ($h >= 1 ? 'table-warning' : '')) ?>">
              <td class="fw-bold"><?= htmlspecialchars($info['name']) ?></td>
              <td><?= htmlspecialchars($info['platform']) ?></td>
              <td><?= htmlspecialchars($info['steamid']) ?></td>
              <td><?= sprintf("%02dh %02dm %02ds", $h, $m, $s) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="bg-dark p-3 rounded shadow-sm mb-4">
    <h5 class="mb-2">📜 Historial de eventos recientes</h5>
    <div id="historyContent" style="font-family:monospace; max-height:180px; overflow:auto;">
      <?php foreach (array_slice(array_reverse($logEvents), 0, 20) as $e): ?>
        <div>[<?= $e['time'] ?>] <?= htmlspecialchars($e['user']) ?>
          <?= $e['event']==='connected'
            ? "<span class='text-success'>se conectó 🟢</span>"
            : "<span class='text-danger'>se desconectó 🔴</span>"
          ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="text-center mt-3">
    <button id="btnRefresh" class="btn btn-primary btn-lg">🔄 Actualizar Ahora</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

$('#btnSearch').on('click', function() {
  const q = $('#searchUser').val().trim();
  $.get('ajax/search_users.php', { q }, function(res) {
    if (res.status === 'success') {
      $('#usersTable').html(res.html);
    } else {
      alert(res.message);
    }
  }, 'json');
});

$('#searchUser').on('keyup', function(e) {
  if (e.key === 'Enter') $('#btnSearch').click();
});
</script>

<script>
if (typeof window.onlineUsersHighlightInit === 'undefined') {
  window.onlineUsersHighlightInit = true;

  let previousUsers = <?= json_encode(array_keys($users)) ?>;
  let lastAlerted   = <?= json_encode(array_keys($alertPlayers)) ?>;

  const ctx   = document.getElementById('onlineChart').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($labels ?: []) ?>,
      datasets: [{
        label: 'Usuarios online',
        data: <?= json_encode($counts ?: []) ?>,
        tension: 0.3,
        fill: false,
        borderColor: '#4caf50'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { labels: { color: '#ddd' } } },
      scales: {
        x: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,0.1)' } },
        y: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,0.1)' }, beginAtZero: true, precision: 0 }
      }
    }
  });

  function triggerAlert(players) {
    const names = Object.keys(players);
    if (names.length === 0) {
      $('#alertBanner').slideUp(500);
      lastAlerted = [];
      return;
    }

    const newAlerts = names.filter(n => !lastAlerted.includes(n));
    if (newAlerts.length > 0) {
      document.getElementById('soundAlert').play();
    }
    lastAlerted = names;

    const html = names.map(n => `${n} — ${players[n]}`).join('<br>');
    $('#alertPlayer').html(html);
    $('#alertBanner').slideDown(300);
  }

  function applyRefresh(res) {
    $('#lastUpdate').text(res.time);
    // actualizar tabla HTML...
    // (Se podría reconstruir la tabla igual al generado en PHP)
    previousUsers = res.usernames;
    // actualizar gráfico
    if (res.point) {
      chart.data.labels.push(res.point.t);
      chart.data.datasets[0].data.push(res.point.count);
      if (chart.data.labels.length > 120) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
      }
      chart.update('none');
    }
    triggerAlert(res.alertPlayers || {});
  }

  $('#btnRefresh').on('click', () => {
    $.get('pages/online_users_refresh.php', res => applyRefresh(res), 'json');
  });
  setInterval(() => {
    $('#btnRefresh').trigger('click');
  }, 30000);

  triggerAlert(<?= json_encode($alertPlayers) ?>);
}
</script>
