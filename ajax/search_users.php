<?php
require_once __DIR__ . '/../config.php';

$stateFile = __DIR__ . '/../data/online_state.json';
header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim(strtolower($_GET['q'])) : '';

if (!file_exists($stateFile)) {
    echo json_encode(['status' => 'error', 'message' => 'No hay datos de usuarios online']);
    exit;
}

$users = json_decode(file_get_contents($stateFile), true) ?? [];

if ($q !== '') {
    $users = array_filter($users, function($u) use ($q) {
        return str_contains(strtolower($u['name']), $q)
            || str_contains(strtolower($u['platform']), $q)
            || str_contains(strtolower($u['steamid']), $q);
    });
}

// Armar tabla HTML
ob_start();
?>
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
  <?php if (empty($users)): ?>
    <tr><td colspan="4" class="text-center text-secondary">No se encontraron coincidencias.</td></tr>
  <?php else: ?>
    <?php foreach ($users as $info): 
      $secs = time() - strtotime($info['loginTime']);
      $h = floor($secs/3600); $m = floor(($secs%3600)/60); $s = $secs%60;
    ?>
      <tr class="<?= ($h>=3?'table-danger':($h>=1?'table-warning':'')) ?>">
        <td class="fw-bold"><?= htmlspecialchars($info['name']) ?></td>
        <td><?= htmlspecialchars($info['platform']) ?></td>
        <td><?= htmlspecialchars($info['steamid']) ?></td>
        <td><?= sprintf("%02dh %02dm %02ds", $h, $m, $s) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>
<?php
$html = ob_get_clean();

echo json_encode(['status' => 'success', 'html' => $html]);
