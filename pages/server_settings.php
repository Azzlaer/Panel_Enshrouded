<?php
require_once __DIR__ . '/../config.php';

if (!file_exists($server_json_path)) {
    die("<div class='alert alert-danger'>❌ No se encontró el archivo de configuración en <b>$server_json_path</b></div>");
}

$json_data = file_get_contents($server_json_path);
$config = json_decode($json_data, true);

// Si se envía el formulario (AJAX POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar todos los valores del JSON dinámicamente
    foreach ($_POST as $key => $value) {
        // Detectar claves anidadas (gameSettings[...], userGroups[0][...])
        if (preg_match('/^([^\[]+)\[(.+)\]$/', $key, $matches)) {
            $mainKey = $matches[1];
            $subKeys = explode('][', trim($matches[2], '[]'));
            $ref =& $config[$mainKey];
            foreach ($subKeys as $subKey) {
                if (!isset($ref[$subKey])) $ref[$subKey] = [];
                $ref =& $ref[$subKey];
            }
            // Convertir tipos de datos
            if ($value === "true") $value = true;
            elseif ($value === "false") $value = false;
            elseif (is_numeric($value)) $value += 0;
            $ref = $value;
        } else {
            if ($value === "true") $value = true;
            elseif ($value === "false") $value = false;
            elseif (is_numeric($value)) $value += 0;
            $config[$key] = $value;
        }
    }

    // Guardar archivo
    file_put_contents($server_json_path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "<div class='alert alert-success mt-3'>✅ Configuración guardada correctamente.</div>";
    exit;
}
?>

<div class="container mt-4 text-light">
  <h2 class="mb-4">⚙️ Enshrouded Server Settings</h2>

  <form id="serverSettingsForm" method="POST" class="bg-dark p-4 rounded shadow">

    <!-- Sección General -->
    <div class="mb-4">
      <h4>🖥️ Configuración General</h4>
      <div class="row">
        <?php foreach ($config as $key => $value): ?>
          <?php if (!is_array($value) && $key !== 'userGroups'): ?>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold"><?= htmlspecialchars($key) ?></label>
              <?php if (is_bool($value)): ?>
                <select class="form-select bg-secondary text-light" name="<?= $key ?>">
                  <option value="true" <?= $value ? 'selected' : '' ?>>true</option>
                  <option value="false" <?= !$value ? 'selected' : '' ?>>false</option>
                </select>
              <?php else: ?>
                <input type="text" class="form-control bg-secondary text-light" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <hr class="border-light">

    <!-- Game Settings -->
    <div class="mb-4">
      <h4>🎮 Game Settings</h4>
      <div class="row">
        <?php foreach ($config['gameSettings'] as $key => $value): ?>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold"><?= htmlspecialchars($key) ?></label>
            <?php if (is_bool($value)): ?>
              <select class="form-select bg-secondary text-light" name="gameSettings[<?= $key ?>]">
                <option value="true" <?= $value ? 'selected' : '' ?>>true</option>
                <option value="false" <?= !$value ? 'selected' : '' ?>>false</option>
              </select>
            <?php else: ?>
              <input type="text" class="form-control bg-secondary text-light" name="gameSettings[<?= $key ?>]" value="<?= htmlspecialchars($value) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <hr class="border-light">

    <!-- User Groups -->
    <div class="mb-4">
      <h4>👥 User Groups</h4>
      <div class="table-responsive">
        <table class="table table-dark table-striped align-middle text-center">
          <thead>
            <tr>
              <?php foreach (array_keys($config['userGroups'][0]) as $header): ?>
                <th><?= htmlspecialchars($header) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($config['userGroups'] as $index => $group): ?>
              <tr>
                <?php foreach ($group as $key => $value): ?>
                  <td>
                    <?php if (is_bool($value)): ?>
                      <select class="form-select bg-secondary text-light" name="userGroups[<?= $index ?>][<?= $key ?>]">
                        <option value="true" <?= $value ? 'selected' : '' ?>>true</option>
                        <option value="false" <?= !$value ? 'selected' : '' ?>>false</option>
                      </select>
                    <?php else: ?>
                      <input type="text" class="form-control bg-secondary text-light text-center" 
                             name="userGroups[<?= $index ?>][<?= $key ?>]" 
                             value="<?= htmlspecialchars($value) ?>">
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <button type="submit" class="btn btn-success w-100 fw-bold py-2">💾 Guardar Cambios</button>
  </form>
</div>

<script>
$(function(){
  // Enviar formulario por AJAX sin recargar
  $('#serverSettingsForm').on('submit', function(e){
    e.preventDefault();
    const formData = $(this).serialize();
    
    $.ajax({
      url: 'pages/server_settings.php',
      method: 'POST',
      data: formData,
      success: function(response){
        // Mostrar mensaje sin recargar el panel
        const msg = $('<div class="alert alert-success mt-3">✅ Configuración guardada correctamente.</div>');
        $('#serverSettingsForm').prepend(msg);
        setTimeout(()=>msg.fadeOut(500, ()=>msg.remove()), 2500);
      },
      error: function(){
        const msg = $('<div class="alert alert-danger mt-3">❌ Error al guardar la configuración.</div>');
        $('#serverSettingsForm').prepend(msg);
        setTimeout(()=>msg.fadeOut(500, ()=>msg.remove()), 2500);
      }
    });
  });
});
</script>
