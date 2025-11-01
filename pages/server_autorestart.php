<?php require_once "../config.php"; ?>
<div class="container py-4">
  <h3 class="text-light mb-4">🔁 Reinicio Automático del Servidor</h3>

  <div class="card bg-dark text-light shadow-sm mb-3">
    <div class="card-body">
      <h5 class="card-title">⚙️ Configuración actual</h5>
      <p>Cada <strong><?= $auto_restart_hours ?></strong> horas se ejecutará un reinicio automático.</p>
      <p>Antes del reinicio se guarda el progreso y se reinicia tras <strong><?= $auto_restart_delay ?></strong> segundos.</p>
      <button id="manual-restart" class="btn btn-warning mt-3">🔁 Reiniciar ahora</button>
    </div>
  </div>

  <div id="restart-status" class="mt-3 text-center"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$('#manual-restart').on('click', function(){
  $('#restart-status').html('<div class="text-info">⏳ Ejecutando reinicio seguro...</div>');
  $.get('../ajax/auto_restart.php', function(data){
    if (data.status === 'success') {
      $('#restart-status').html('<div class="text-success">' + data.message + '</div>');
    } else {
      $('#restart-status').html('<div class="text-danger">❌ ' + (data.message || 'Error al reiniciar') + '</div>');
    }
  }, 'json').fail(function(){
    $('#restart-status').html('<div class="text-danger">❌ Error de conexión.</div>');
  });
});
</script>
