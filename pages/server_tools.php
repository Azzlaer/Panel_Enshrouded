<?php
require_once "../config.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🧰 Server Tools - Enshrouded Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { background: #121212; color: #f5f5f5; font-family: "Segoe UI", sans-serif; }
  .card {
      background: #1c1c1c;
      border: 1px solid #2a2a2a;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0,0,0,0.3);
      transition: all 0.2s ease;
      color: #f5f5f5;
  }
  .card:hover { transform: translateY(-2px); box-shadow: 0 0 12px rgba(0,0,0,0.5); }
  .card-header {
      background: #242424;
      border-bottom: 1px solid #333;
      font-weight: 500;
      color: #ffffff;
  }
  .btn { border-radius: 8px; }
  .btn-success, .btn-warning, .btn-danger { border: none; }
  .backup-list-item {
      background: #1b1b1b;
      border: 1px solid #2d2d2d;
      border-radius: 8px;
      padding: 10px 15px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #fff;
  }
  .backup-list-item:hover { background: #262626; }
  .progress {
      background-color: #2b2b2b;
      height: 8px;
      border-radius: 5px;
  }
  .progress-bar { background-color: #00a8ff; }

  /* Colores de texto personalizados */
  p, li, ul, strong {
      color: #ffffff !important;
  }
  .text-muted {
      color: #ffffff !important;
  }
  code {
      color: #7dd3fc;
      background: rgba(255,255,255,0.05);
      padding: 2px 5px;
      border-radius: 4px;
  }
</style>

</head>
<body>

<div class="container mt-4">
  <h2 class="mb-4 text-light"><i class="bi bi-hammer"></i> Herramientas del Servidor</h2>

  <!-- CREAR BACKUP -->
  <div class="card mb-4">
    <div class="card-header"><i class="bi bi-archive"></i> Crear Backup Manual</div>
    <div class="card-body">
      <p>Guarda una copia comprimida del servidor con los archivos principales:</p>
      <ul>
        <li><code>savegame</code></li>
        <li><code>profile</code></li>
        <li><code>enshrouded_server.json</code></li>
      </ul>
      <button id="runBackup" class="btn btn-success"><i class="bi bi-cloud-arrow-up"></i> Crear Backup</button>
      <div class="progress mt-3" style="display:none;">
        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%;"></div>
      </div>
      <div id="backupResult" class="mt-3"></div>
    </div>
  </div>

  <!-- BACKUP AUTOMÁTICO -->
  <div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history"></i> Backup Automático</div>
    <div class="card-body">
      <p>El sistema crea automáticamente un backup cada <strong><?= $auto_backup_interval_hours ?></strong> horas.</p>
      <button id="runAutoBackup" class="btn btn-warning"><i class="bi bi-arrow-repeat"></i> Ejecutar Backup Automático</button>
      <div id="autoBackupResult" class="mt-3"></div>
    </div>
  </div>

  <!-- HISTORIAL DE BACKUPS -->
  <div class="card mb-4">
    <div class="card-header"><i class="bi bi-folder-check"></i> Historial de Backups</div>
    <div class="card-body" id="backupList">
      <p class="text-muted">Cargando lista de backups...</p>
    </div>
  </div>

  <!-- LIMPIAR BACKUPS -->
  <div class="card mb-5">
    <div class="card-header bg-danger text-white"><i class="bi bi-trash3"></i> Limpiar Backups</div>
    <div class="card-body">
      <p>Se conservarán solo los últimos <strong><?= $backup_limit ?></strong> backups. Puedes eliminar todos manualmente:</p>
      <button id="clearBackups" class="btn btn-danger"><i class="bi bi-x-circle"></i> Borrar Todos los Backups</button>
      <div id="clearBackupResult" class="mt-3"></div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function() {

  // 📍 Detectar ruta base dinámica (para rutas AJAX)
  const BASE_URL = window.location.pathname.includes('/pages/')
    ? '../'
    : './';

  function showProgress(percent) {
    $('.progress').show();
    $('.progress-bar').css('width', percent + '%');
  }

  // 📦 Crear Backup Manual
  $('#runBackup').on('click', function() {
    if (!confirm('¿Deseas crear un backup manual ahora?')) return;
    $('#backupResult').html('<div class="text-info">⏳ Creando backup...</div>');
    showProgress(20);

    $.get(BASE_URL + 'ajax/backup_server.php', function(data) {
      try {
        if (typeof data === 'string') data = JSON.parse(data);
      } catch (e) {
        $('#backupResult').html('<div class="text-danger">❌ Respuesta no válida del servidor.</div>');
        console.error('Respuesta:', data);
        return;
      }

      showProgress(100);
      setTimeout(() => $('.progress').fadeOut(), 800);

      if (data.status === 'success') {
        $('#backupResult').html('<div class="text-success">✅ ' + data.message + '</div>');
        loadBackupList();
      } else {
        $('#backupResult').html('<div class="text-danger">❌ ' + (data.message || 'Error desconocido.') + '</div>');
      }
    }).fail((xhr) => {
      $('#backupResult').html('<div class="text-danger">❌ Error al conectar con el servidor (' + xhr.status + ').</div>');
    });
  });

  // ♻️ Backup Automático
  $('#runAutoBackup').on('click', function() {
    if (!confirm('¿Ejecutar el proceso de backup automático ahora?')) return;
    $('#autoBackupResult').html('<div class="text-info">⏳ Ejecutando backup automático...</div>');

    $.get(BASE_URL + 'ajax/backup_server.php', function(data) {
      try {
        if (typeof data === 'string') data = JSON.parse(data);
      } catch (e) {
        $('#autoBackupResult').html('<div class="text-danger">❌ Respuesta no válida del servidor.</div>');
        return;
      }

      if (data.status === 'success') {
        $('#autoBackupResult').html('<div class="text-success">✅ ' + data.message + '</div>');
        loadBackupList();
      } else {
        $('#autoBackupResult').html('<div class="text-danger">❌ ' + (data.message || 'Error desconocido.') + '</div>');
      }
    }).fail(() => {
      $('#autoBackupResult').html('<div class="text-danger">❌ Error al conectar con el servidor.</div>');
    });
  });

  // 🧹 Limpiar Backups
  $('#clearBackups').on('click', function() {
    if (!confirm('⚠️ ¿Seguro que deseas eliminar TODOS los backups?')) return;
    $('#clearBackupResult').html('<div class="text-info">🧹 Eliminando backups...</div>');

    $.get(BASE_URL + 'ajax/clear_backups.php', function(data) {
      try {
        if (typeof data === 'string') data = JSON.parse(data);
      } catch (e) {
        $('#clearBackupResult').html('<div class="text-danger">❌ Respuesta no válida del servidor.</div>');
        return;
      }

      if (data.status === 'success') {
        $('#clearBackupResult').html('<div class="text-success">✅ ' + data.message + '</div>');
        loadBackupList();
      } else {
        $('#clearBackupResult').html('<div class="text-danger">❌ ' + (data.message || 'Error desconocido.') + '</div>');
      }
    }).fail(() => {
      $('#clearBackupResult').html('<div class="text-danger">❌ Error al conectar con el servidor.</div>');
    });
  });

  // 📁 Cargar lista de backups
  function loadBackupList() {
    $('#backupList').html('<p class="text-info">🔄 Cargando lista de backups...</p>');

    $.get(BASE_URL + 'ajax/list_backups.php', function(data) {
      try {
        if (typeof data === 'string') data = JSON.parse(data);
      } catch (e) {
        $('#backupList').html('<div class="text-danger">❌ Respuesta no válida del servidor.</div>');
        return;
      }

      if (data.status === 'success') {
        if (data.backups.length === 0) {
          $('#backupList').html('<p class="text-muted">No hay backups disponibles.</p>');
          return;
        }
        let html = '';
        data.backups.forEach(file => {
          html += `
            <div class="backup-list-item">
              <span><i class="bi bi-file-zip"></i> ${file.name}</span>
              <span class="badge bg-secondary">${file.size}</span>
            </div>`;
        });
        $('#backupList').html(html);
      } else {
        $('#backupList').html('<div class="text-danger">❌ ' + (data.message || 'Error al obtener la lista de backups.') + '</div>');
      }
    }).fail(() => {
      $('#backupList').html('<div class="text-danger">❌ Error al conectar con el servidor.</div>');
    });
  }

  // Inicializar
  loadBackupList();
});
</script>

</body>
</html>
