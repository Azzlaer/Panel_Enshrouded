<?php
require_once "../config.php";

// URL base del proyecto
$base_url = "/esh";
?>
<div class="container py-4">
  <h3 class="text-light mb-4">📜 Consola del Servidor</h3>

  <div class="card bg-dark text-light shadow-sm mb-4 border-secondary">
    <div class="card-body">
      <h5 class="card-title text-white">🧠 Monitor en tiempo real (Streaming)</h5>
      <p class="text-white">
        Esta consola muestra las líneas nuevas del archivo de log en tiempo real.<br>
        Puedes iniciarla o pausarla sin afectar el resto del panel.
      </p>

      <div class="d-flex gap-2 mb-3 flex-wrap">
        <button id="start-log" class="btn btn-success btn-sm">▶️ Iniciar Lectura</button>
        <button id="stop-log" class="btn btn-warning btn-sm" disabled>⏸️ Detener</button>
        <button id="clear-log" class="btn btn-outline-danger btn-sm">🧹 Limpiar Log</button>
        <button id="archive-log" class="btn btn-info btn-sm">📦 Archivar Log</button>
      </div>

      <pre id="console-output" class="bg-black text-success p-3 rounded small"
           style="height: 450px; overflow-y: auto;">🕒 Esperando inicio...</pre>
    </div>
  </div>
</div>

<!-- Aseguramos jQuery cargado correctamente -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function () {

  const BASE_URL = '<?= $base_url ?>';
  let logInterval = null;
  let lastSize = 0;
  let isRunning = false;

  // --------- FUNCIONES ---------
  function appendToConsole(text) {
    const output = $('#console-output');
    output.append(text);
    output.scrollTop(output[0].scrollHeight);
  }

  function iniciarLectura() {
    if (isRunning) return;
    if (!confirm("¿Deseas iniciar la lectura del log en tiempo real?")) return;

    isRunning = true;
    $('#start-log').prop('disabled', true);
    $('#stop-log').prop('disabled', false);
    appendToConsole("\n▶️ Lectura iniciada...\n");
    leerIncremental();
    logInterval = setInterval(leerIncremental, 3000);
  }

  function detenerLectura() {
    if (!isRunning) return;
    if (!confirm("¿Deseas detener la lectura del log?")) return;

    clearInterval(logInterval);
    logInterval = null;
    isRunning = false;
    $('#start-log').prop('disabled', false);
    $('#stop-log').prop('disabled', true);
    appendToConsole("\n⏸️ Lectura detenida.\n");
  }

  function leerIncremental() {
    $.ajax({
      url: BASE_URL + '/ajax/read_log_incremental.php',
      method: 'GET',
      data: { offset: lastSize },
      dataType: 'json',
      success: function (data) {
        if (data.status === 'success') {
          if (data.new_content.length > 0) appendToConsole(data.new_content);
          lastSize = data.new_size;
        } else {
          appendToConsole("\n❌ " + data.message + "\n");
        }
      },
      error: function (xhr) {
        appendToConsole("\n❌ Error de conexión: " + (xhr.statusText || 'Servidor no disponible') + "\n");
        detenerLectura();
      }
    });
  }

  function limpiarLog() {
    if (!confirm('⚠️ ¿Seguro que deseas limpiar el log? Esto eliminará todo el contenido.')) return;
    appendToConsole("\n🧹 Limpiando log...\n");

    $.ajax({
      url: BASE_URL + '/ajax/clear_log.php',
      dataType: 'json',
      success: function (data) {
        if (data.status === 'success') {
          $('#console-output').text('🧹 Log limpiado correctamente.\n');
          lastSize = 0;
          alert("✅ Log limpiado correctamente.");
        } else {
          appendToConsole("\n❌ " + (data.message || 'Error al limpiar el log.') + "\n");
          alert("❌ Error al limpiar el log.");
        }
      },
      error: function () {
        appendToConsole("\n❌ Error al conectar con el servidor.\n");
        alert("❌ Error de conexión.");
      }
    });
  }

  function archivarLog() {
    if (!confirm('📦 ¿Deseas archivar el log actual?\nSe recomienda detener el servidor antes.')) return;
    appendToConsole("\n⏳ Archivando log, por favor espera...\n");

    $.ajax({
      url: BASE_URL + '/ajax/archive_log.php',
      dataType: 'json',
      success: function(data){
        if (data.status === 'success') {
          appendToConsole('\n✅ ' + data.message + '\nArchivo creado: ' + (data.archive || '') + '\n');
          alert("✅ Log archivado correctamente: " + data.archive);
        } else {
          appendToConsole('\n❌ ' + (data.message || 'Error al archivar el log.') + '\n');
          alert("❌ " + (data.message || "Error al archivar el log."));
        }
      },
      error: function(xhr){
        appendToConsole('\n❌ Error al conectar con el servidor.\n');
        alert("❌ Error al conectar con el servidor.");
      }
    });
  }

  // --------- EVENTOS ---------
  $('#start-log').on('click', iniciarLectura);
  $('#stop-log').on('click', detenerLectura);
  $('#clear-log').on('click', limpiarLog);
  $('#archive-log').on('click', archivarLog);
});
</script>
