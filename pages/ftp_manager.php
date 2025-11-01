<?php
require_once __DIR__ . '/../config.php';

// Configuración del servidor FTP
$ftp_server = "localhost";
$ftp_user = "enshrouded";
$ftp_pass = "35027595";

// Conexión FTP
$ftp_conn = @ftp_connect($ftp_server) or die("<div class='alert alert-danger'>❌ No se pudo conectar al servidor FTP.</div>");
$login = @ftp_login($ftp_conn, $ftp_user, $ftp_pass);

if (!$login) {
    die("<div class='alert alert-danger'>❌ Error de autenticación en el servidor FTP.</div>");
}

ftp_pasv($ftp_conn, true);

// Directorio actual
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : "/";
$current_dir = rtrim($current_dir, '/');

// Acciones FTP
if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $target_file = ($current_dir ?: "/") . "/" . $filename;
    if (ftp_put($ftp_conn, $target_file, $_FILES['file']['tmp_name'], FTP_BINARY)) {
        echo "<div class='alert alert-success mt-2'>✅ Archivo <b>$filename</b> subido correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger mt-2'>❌ Error al subir el archivo.</div>";
    }
}

if (isset($_POST['create_folder']) && !empty($_POST['folder_name'])) {
    $folder_name = trim($_POST['folder_name'], '/');
    $new_folder = ($current_dir ?: "/") . "/" . $folder_name;
    if (ftp_mkdir($ftp_conn, $new_folder)) {
        echo "<div class='alert alert-success mt-2'>📁 Carpeta <b>$folder_name</b> creada correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger mt-2'>❌ Error al crear la carpeta.</div>";
    }
}

if (isset($_POST['delete_file'])) {
    $delete_path = $_POST['delete_file'];
    if (@ftp_delete($ftp_conn, $delete_path)) {
        echo "<div class='alert alert-success mt-2'>🗑️ Archivo eliminado.</div>";
    } elseif (@ftp_rmdir($ftp_conn, $delete_path)) {
        echo "<div class='alert alert-success mt-2'>🗑️ Carpeta eliminada.</div>";
    } else {
        echo "<div class='alert alert-danger mt-2'>❌ No se pudo eliminar.</div>";
    }
}

// Obtener listado
$file_list = ftp_rawlist($ftp_conn, $current_dir);
ftp_close($ftp_conn);
?>

<div class="container-fluid text-light mt-3">
  <h2 class="mb-3">🌐 Gestor de Archivos FTP</h2>

  <div class="bg-dark p-3 rounded shadow-sm mb-4">
    <p>Directorio actual: <strong><?= htmlspecialchars($current_dir ?: "/"); ?></strong></p>
    <a href="#" class="btn btn-secondary btn-sm mb-3" onclick="loadFtpDir('/'); return false;">🏠 Ir a raíz</a>

    <!-- Subir archivo -->
    <form method="post" enctype="multipart/form-data" class="row g-2 mb-3">
      <div class="col-md-5">
        <input type="file" name="file" required class="form-control bg-secondary text-light">
      </div>
      <div class="col-md-3">
        <button type="submit" name="upload" class="btn btn-success w-100">⬆️ Subir Archivo</button>
      </div>
    </form>

    <!-- Crear carpeta -->
    <form method="post" class="row g-2">
      <div class="col-md-5">
        <input type="text" name="folder_name" placeholder="Nombre de la carpeta" required class="form-control bg-secondary text-light">
      </div>
      <div class="col-md-3">
        <button type="submit" name="create_folder" class="btn btn-warning w-100">📁 Crear Carpeta</button>
      </div>
    </form>
  </div>

  <!-- Listado -->
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle text-center">
      <thead class="table-secondary text-dark">
        <tr>
          <th>Nombre</th>
          <th>Tamaño</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($file_list)): ?>
        <?php foreach ($file_list as $fileinfo):
          $parts = preg_split('/\s+/', $fileinfo, 9);
          $file_name = $parts[8] ?? '';
          $is_dir = (substr($parts[0], 0, 1) === 'd');
          $file_size = $parts[4] ?? '';
          $file_date = ($parts[5] ?? '') . " " . ($parts[6] ?? '') . " " . ($parts[7] ?? '');
          $full_path_ftp = rtrim($current_dir, '/') . '/' . $file_name;
          $full_path_ftp = ltrim($full_path_ftp, '/');
        ?>
          <tr>
            <td class="text-start">
              <?php if ($is_dir): ?>
                <a href="#" onclick="loadFtpDir('<?= "/" . $full_path_ftp ?>'); return false;" class="text-info text-decoration-none">
                  📁 <?= htmlspecialchars($file_name) ?>
                </a>
              <?php else: ?>
                <?= htmlspecialchars($file_name) ?>
              <?php endif; ?>
            </td>
            <td><?= $is_dir ? '—' : number_format($file_size / 1024, 2) . ' KB' ?></td>
            <td><?= htmlspecialchars($file_date) ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="delete_file" value="<?= "/" . $full_path_ftp; ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑️ Eliminar</button>
              </form>

              <?php if (!$is_dir): ?>
                <button class="btn btn-warning btn-sm" onclick="openEditModal('<?= htmlspecialchars('/' . $full_path_ftp) ?>')">✏️ Editar</button>
                <?php $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); ?>
                <?php if (in_array($ext, ['zip','rar'])): ?>
                  <a href="./pages/ftp_compress.php?action=extract&file=<?= urlencode("/" . $full_path_ftp); ?>" class="btn btn-info btn-sm">🗜️ Descomprimir</a>
                <?php endif; ?>
                <a href="./pages/ftp_compress.php?action=compress&file=<?= urlencode("/" . $full_path_ftp); ?>" class="btn btn-secondary btn-sm">📦 Comprimir</a>
                <a href="./pages/ftp_download.php?file=<?= urlencode($current_dir . '/' . $file_name); ?>" class="btn btn-primary btn-sm">⬇️ Descargar</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4">No se encontraron archivos en este directorio.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de edición -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-dark text-light border-0">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="editModalLabel">✏️ Editar archivo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea id="fileContent" class="form-control bg-secondary text-light" rows="20"></textarea>
      </div>
      <div class="modal-footer border-secondary">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="saveChanges">💾 Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<script>
// Navegar entre carpetas sin salir del panel
function loadFtpDir(path) {
  $('#main').html('<div class="p-5 text-center text-light">Cargando...</div>');
  $('#main').load('pages/ftp_manager.php?dir=' + encodeURIComponent(path));
}

// Abrir modal de edición
function openEditModal(filePath) {
  $('#editModalLabel').text('✏️ Editar: ' + filePath);
  $('#fileContent').val('Cargando contenido...');
  const modal = new bootstrap.Modal(document.getElementById('editModal'));
  modal.show();

  // Cargar contenido del archivo
  $.get('pages/ftp_edit.php', { file: filePath, mode: 'read' }, function(data) {
    $('#fileContent').val(data);
    $('#saveChanges').off('click').on('click', function() {
      saveFileChanges(filePath, modal);
    });
  }).fail(function() {
    $('#fileContent').val('❌ Error al cargar el archivo.');
  });
}

// Guardar cambios
function saveFileChanges(filePath, modal) {
  const newContent = $('#fileContent').val();
  $.post('pages/ftp_edit.php', { file: filePath, mode: 'save', content: newContent }, function(resp) {
    alert('✅ Archivo guardado correctamente.');
    modal.hide();
  }).fail(function() {
    alert('❌ Error al guardar los cambios.');
  });
}
</script>
