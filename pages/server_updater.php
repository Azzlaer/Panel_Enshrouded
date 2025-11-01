<?php
require_once __DIR__ . '/../config.php';

// --- Función auxiliar ---
function isServerRunning($exePath) {
    $output = [];
    exec('tasklist /FI "IMAGENAME eq ' . escapeshellarg(basename($exePath)) . '"', $output);
    foreach ($output as $line) {
        if (stripos($line, basename($exePath)) !== false) return true;
    }
    return false;
}

$isRunning = isServerRunning($server_exe_path);
?>

<div class="container-fluid text-light mt-4">
  <h2 class="mb-4">🧩 Server Updater - Enshrouded</h2>

  <div class="bg-dark p-4 rounded shadow mb-4">
    <p><strong>SteamCMD:</strong> <code><?= htmlspecialchars($steamcmd_path) ?></code></p>
    <p><strong>Directorio del servidor:</strong> <code><?= htmlspecialchars(dirname($server_exe_path)) ?></code></p>

    <div class="d-flex align-items-center mb-3">
      <h5 class="me-2">Estado actual:</h5>
      <h5 id="serverStatus"><?= $isRunning ? "<span class='text-success fw-bold'>🟢 En línea</span>" : "<span class='text-danger fw-bold'>🔴 Apagado</span>" ?></h5>
    </div>

    <div class="d-flex flex-wrap gap-2">
      <button id="btnCheckUpdate" class="btn btn-secondary btn-lg">🔍 Verificar Actualizaciones</button>
      <button id="btnUpdateServer" class="btn btn-primary btn-lg" <?= $isRunning ? 'disabled' : '' ?>>⬇️ Actualizar Servidor</button>
      <button id="btnValidateServer" class="btn btn-warning btn-lg" <?= $isRunning ? 'disabled' : '' ?>>🧠 Validar Archivos</button>
    </div>
  </div>

  <div id="updateOutput" class="bg-black p-3 rounded border border-secondary shadow"
       style="height:300px; overflow-y:auto; font-family:monospace; color:#00ff99;">
    <div>📋 Consola de actualización...</div>
  </div>
</div>

<script>
// 🔧 Aseguramos que el script solo se registre una vez
if (typeof window.serverUpdaterInit === 'undefined') {
  window.serverUpdaterInit = true;

  function sendUpdaterAction(action, message) {
    $('#updateOutput').html(`<div class="text-info p-3">⏳ ${message}...</div>`);
    $.post('pages/server_updater_action.php', { action }, function(res) {
      $('#updateOutput').html(res);
    }).fail(function(xhr) {
      $('#updateOutput').html('<div class="text-danger p-3">❌ Error AJAX (' + xhr.status + '): ' + xhr.statusText + '</div>');
    });
  }

  $(document).on('click', '#btnCheckUpdate', function() {
    sendUpdaterAction('check', 'Consultando SteamCMD para verificar actualizaciones');
  });

  $(document).on('click', '#btnUpdateServer', function() {
    if (!confirm('⚠️ El servidor debe estar APAGADO antes de actualizar. ¿Continuar?')) return;
    sendUpdaterAction('update', 'Iniciando actualización del servidor');
  });

  $(document).on('click', '#btnValidateServer', function() {
    if (!confirm('🔎 ¿Deseas validar los archivos del servidor mediante SteamCMD?')) return;
    sendUpdaterAction('validate', 'Ejecutando validación de archivos del servidor');
  });
}
</script>
