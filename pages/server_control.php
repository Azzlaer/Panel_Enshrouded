<?php
require_once __DIR__ . '/../config.php';

// --- Funciones auxiliares ---
function isServerRunning($exePath) {
    $output = [];
    exec('tasklist /FI "IMAGENAME eq ' . escapeshellarg(basename($exePath)) . '"', $output);
    foreach ($output as $line) {
        if (stripos($line, basename($exePath)) !== false) return true;
    }
    return false;
}

function getServerStatusText($isRunning) {
    return $isRunning
        ? "<span class='text-success fw-bold'>🟢 En línea</span>"
        : "<span class='text-danger fw-bold'>🔴 Apagado</span>";
}

$isRunning = isServerRunning($server_exe_path);
?>

<div class="container-fluid text-light mt-4">
  <h2 class="mb-4">⚙️ Control del Servidor Enshrouded</h2>

  <div class="bg-dark p-4 rounded shadow mb-4">
    <p><strong>Ruta ejecutable:</strong> <code><?= htmlspecialchars($server_exe_path) ?></code></p>
    <p><strong>Puerto:</strong> <?= htmlspecialchars($server_port) ?></p>
    <div class="d-flex align-items-center mb-3">
      <h5 class="me-2">Estado actual:</h5>
      <h5 id="serverStatus"><?= getServerStatusText($isRunning) ?></h5>
    </div>

    <div class="d-flex flex-wrap gap-2">
      <button id="btnStartServer" class="btn btn-success btn-lg" <?= $isRunning ? 'disabled' : '' ?>>🚀 Iniciar Servidor</button>
      <button id="btnStopServer" class="btn btn-warning btn-lg" <?= !$isRunning ? 'disabled' : '' ?>>🛑 Stop (Apagado Limpio)</button>
      <button id="btnTerminateServer" class="btn btn-danger btn-lg" <?= !$isRunning ? 'disabled' : '' ?>>💀 Terminate (Forzar Cierre)</button>
      <button id="btnRefreshStatus" class="btn btn-secondary btn-lg">🔄 Actualizar Estado</button>
    </div>
  </div>

  <div id="serverControlLog" class="bg-black p-3 rounded border border-secondary shadow"
       style="height:300px; overflow-y:auto; font-family:monospace; color:#00ff99;">
    <div>📋 Consola del servidor...</div>
  </div>
</div>

<script>
if (typeof window.serverControlInit === 'undefined') {
  window.serverControlInit = true;

  $(document).on('click', '#btnStartServer', function() {
    $('#serverControlLog').html('<div class="text-info p-3">⏳ Iniciando servidor...</div>');
    $.post('pages/server_control_action.php', { action: 'start' }, function(r) {
      $('#serverControlLog').html(r);
      refreshStatus();
    });
  });

  $(document).on('click', '#btnStopServer', function() {
    $('#serverControlLog').html('<div class="text-warning p-3">⏳ Enviando comando "stop" al servidor...</div>');
    $.post('pages/server_control_action.php', { action: 'stop' }, function(r) {
      $('#serverControlLog').html(r);
      refreshStatus();
    });
  });

  $(document).on('click', '#btnTerminateServer', function() {
    if (!confirm('⚠️ Esto cerrará el proceso forzosamente. ¿Continuar?')) return;
    $('#serverControlLog').html('<div class="text-danger p-3">⏳ Terminando proceso del servidor...</div>');
    $.post('pages/server_control_action.php', { action: 'terminate' }, function(r) {
      $('#serverControlLog').html(r);
      refreshStatus();
    });
  });

  $(document).on('click', '#btnRefreshStatus', function() {
    refreshStatus();
  });

  function refreshStatus() {
    $.get('pages/server_control_status.php', function(r) {
      $('#serverStatus').html(r);
    });
  }
}
</script>
