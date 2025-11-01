<?php
require_once "../config.php";

// ==========================
//  Cargar información del sistema
// ==========================
$system_info_file = __DIR__ . "/../data/system_info.json";
$system_info = [];

if (file_exists($system_info_file)) {
    $json = file_get_contents($system_info_file);
    $system_info = json_decode($json, true);
}

// Valores por defecto para evitar warnings si faltan claves
$defaults = [
    "hostname" => "N/A",
    "ip_local" => "N/A",
    "os_info" => "N/A",
    "cpu_cores" => "N/A",
    "ram_total" => "N/A",
    "php_version" => phpversion(),
    "disk_total_gb" => "0",
    "disk_free_gb" => "0",
    "disk_used_percent" => "0",
    "generated_at" => "No disponible"
];
$system_info = array_merge($defaults, (array)$system_info);

// ==========================
//  Estado del servidor Enshrouded
// ==========================
$server_status = "Offline";
$connection = @fsockopen("127.0.0.1", $server_port, $errno, $errstr, 1);
if ($connection) {
    $server_status = "Online";
    fclose($connection);
}

// ==========================
//  Backups recientes
// ==========================
$backups = [];
if (is_dir($backup_directory)) {
    $files = scandir($backup_directory, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if (str_ends_with($file, ".zip")) {
            $backups[] = [
                "name" => $file,
                "size" => round(filesize($backup_directory . "\\" . $file) / 1048576, 2) . " MB",
                "date" => date("d/m/Y H:i", filemtime($backup_directory . "\\" . $file))
            ];
        }
    }
}
?>

<div class="container-fluid py-4">
  <h2 class="text-light mb-4">Panel General</h2>

  <div class="row g-3">
    <!-- Estado del servidor -->
    <div class="col-md-4">
      <div class="card bg-dark border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title text-light">Estado del Servidor</h5>
          <p class="fs-5 mt-3">
            <span class="badge bg-<?php echo ($server_status === "Online" ? "success" : "danger"); ?>">
              <?php echo $server_status; ?>
            </span>
          </p>
          <p class="text-secondary small mb-0">Puerto: <span class="text-light"><?php echo $server_port; ?></span></p>
          <p class="text-secondary small">IP Local: <span class="text-light"><?php echo htmlspecialchars($system_info['ip_local']); ?></span></p>
        </div>
      </div>
    </div>

    <!-- Información del sistema -->
    <div class="col-md-4">
      <div class="card bg-dark border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title text-light"><i class="bi bi-cpu"></i> Información del Host</h5>
          <p class="text-secondary small mb-1">Hostname: <span id="hostname" class="text-light"><?= htmlspecialchars($system_info['hostname']) ?></span></p>
          <p class="text-secondary small mb-1">Sistema: <span id="os_info" class="text-light"><?= htmlspecialchars($system_info['os_info']) ?></span></p>
          <p class="text-secondary small mb-1">CPU Núcleos: <span id="cpu_cores" class="text-light"><?= htmlspecialchars($system_info['cpu_cores']) ?></span></p>
          <p class="text-secondary small mb-1">RAM Total: <span id="ram_total" class="text-light"><?= htmlspecialchars($system_info['ram_total']) ?> GB</span></p>
		  <p class="text-secondary small mb-1">PHP: <span id="php_version" class="text-light"><?= htmlspecialchars($system_info['php_version']) ?></span></p>
          <!-- Disco D -->
          <p class="text-secondary small mb-1">
            Disco D: 
            <span id="disk_info" class="text-light">
              <?= htmlspecialchars($system_info['disk_free_gb']) ?> GB libres /
              <?= htmlspecialchars($system_info['disk_total_gb']) ?> GB totales
            </span>
          </p>

          <div class="progress mt-1" style="height: 10px; background-color:#2a2a2a;">
            <div id="disk_bar" class="progress-bar bg-<?php
              $p = (float)$system_info['disk_used_percent'];
              echo ($p < 70 ? "success" : ($p < 90 ? "warning" : "danger"));
            ?>" role="progressbar"
              style="width: <?= htmlspecialchars($system_info['disk_used_percent']) ?>%;"
              aria-valuenow="<?= htmlspecialchars($system_info['disk_used_percent']) ?>" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>
          <small class="text-muted">
            Uso del disco: <span id="disk_percent"><?= htmlspecialchars($system_info['disk_used_percent']) ?>%</span>
          </small>

          <hr>
          
          <p class="text-secondary small text-muted mt-2">Actualizado: <span id="generated_at"><?= htmlspecialchars($system_info['generated_at']) ?></span></p>

          <div class="mt-3">
            <button id="btnRefreshSysInfo" class="btn btn-outline-info btn-sm">
              <i class="bi bi-arrow-repeat"></i> Actualizar información
            </button>
            <span id="sysinfo-status" class="ms-2 small text-muted"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Backups recientes -->
    <div class="col-md-4">
      <div class="card bg-dark border-0 shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title text-light"><i class="bi bi-archive"></i> Últimos Backups</h5>
          <?php if (count($backups) > 0): ?>
            <ul class="list-group list-group-flush">
              <?php foreach (array_slice($backups, 0, 3) as $b): ?>
                <li class="list-group-item bg-transparent text-light d-flex justify-content-between align-items-center">
                  <span><?php echo $b['name']; ?></span>
                  <small class="text-secondary"><?php echo $b['date']; ?></small>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="mt-3">
              <button class="btn btn-sm btn-outline-light" onclick="$('#main').load('pages/server_tools.php')">
                Ver todos
              </button>
            </div>
          <?php else: ?>
            <p class="text-secondary small mt-3">No hay backups disponibles.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Resumen general -->
  <div class="row mt-4">
    <div class="col-md-12">
      <div class="card bg-dark border-0 shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-light mb-3">Resumen general</h5>
          <ul class="text-secondary small">
            <li>Ubicación del servidor: <span class="text-light"><?php echo $enshrouded_server_path; ?></span></li>
            <li>Archivo de configuración: <span class="text-light"><?php echo basename($server_json_path); ?></span></li>
            <li>Ruta de logs: <span class="text-light"><?php echo $server_log_path; ?></span></li>
            <li>Carpeta de backups: <span class="text-light"><?php echo $backup_directory; ?></span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$('#btnRefreshSysInfo').on('click', function(){
  const btn = $(this);
  const status = $('#sysinfo-status');
  
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Actualizando...');
  status.text('Consultando sistema...');

  $.get('ajax/generate_system_info.php', function(data){
    if (data.status === 'success') {
      status.text('Datos actualizados correctamente.');

      // Recargar JSON actualizado
      $.getJSON('data/system_info.json', function(info){
        $('#hostname').text(info.hostname);
        $('#os_info').text(info.os_info);
        $('#cpu_cores').text(info.cpu_cores);
        $('#ram_total').text(info.ram_total + ' GB');
        $('#php_version').text(info.php_version);
        $('#generated_at').text(info.generated_at);
        $('#disk_info').text(info.disk_free_gb + ' GB libres / ' + info.disk_total_gb + ' GB totales');
        $('#disk_percent').text(info.disk_used_percent + '%');

        // Actualizar barra y color
        let bar = $('#disk_bar');
        bar.css('width', info.disk_used_percent + '%');
        bar.attr('aria-valuenow', info.disk_used_percent);

        if (info.disk_used_percent < 70) {
          bar.removeClass().addClass('progress-bar bg-success');
        } else if (info.disk_used_percent < 90) {
          bar.removeClass().addClass('progress-bar bg-warning');
        } else {
          bar.removeClass().addClass('progress-bar bg-danger');
        }
      });
    } else {
      status.text('Error: ' + (data.message || 'Error al actualizar.'));
    }
  }, 'json')
  .fail(() => status.text('Error al conectar con el servidor.'))
  .always(() => {
    btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat"></i> Actualizar información');
  });
});
</script>
