<?php
require_once __DIR__ . '/../config.php';

$jsonPath = $server_json_path;
if (!file_exists($jsonPath)) {
    echo "<div class='alert alert-danger'>Archivo de configuración no encontrado: <code>$jsonPath</code></div>";
    exit;
}

$config = json_decode(file_get_contents($jsonPath), true);
if ($config === null) {
    echo "<div class='alert alert-danger'>Error leyendo JSON de configuración.</div>";
    exit;
}

$userGroups = $config['userGroups'] ?? [];

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ---- GUARDAR o EDITAR GRUPO ----
    if (in_array($action, ['add', 'edit'])) {
        $name = trim($_POST['name']);
        $password = trim($_POST['password']);
        $reservedSlots = intval($_POST['reservedSlots']);
        $flags = [
            'canKickBan' => isset($_POST['canKickBan']),
            'canAccessInventories' => isset($_POST['canAccessInventories']),
            'canEditBase' => isset($_POST['canEditBase']),
            'canExtendBase' => isset($_POST['canExtendBase'])
        ];

        if ($name === '') $errors[] = "El nombre no puede estar vacío.";

        if (empty($errors)) {
            $group = [
                'name' => $name,
                'password' => $password,
                'reservedSlots' => $reservedSlots
            ] + $flags;

            if ($action === 'add') $group['members'] = [];

            if ($action === 'add') {
                $userGroups[] = $group;
            } elseif ($action === 'edit') {
                $idx = intval($_POST['index']);
                if (isset($userGroups[$idx])) {
                    $group['members'] = $userGroups[$idx]['members'] ?? [];
                    $userGroups[$idx] = $group;
                }
            }

            if (empty($errors)) {
                $bak = $jsonPath . '.bak_' . date('Ymd_His');
                copy($jsonPath, $bak);
                $config['userGroups'] = $userGroups;
                file_put_contents($jsonPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $success = "Configuración guardada correctamente.";
            }
        }
    }

    // ---- ELIMINAR GRUPO ----
    elseif ($action === 'delete') {
        $idx = intval($_POST['index']);
        if (isset($userGroups[$idx])) {
            array_splice($userGroups, $idx, 1);
            $bak = $jsonPath . '.bak_' . date('Ymd_His');
            copy($jsonPath, $bak);
            $config['userGroups'] = $userGroups;
            file_put_contents($jsonPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $success = "Grupo eliminado.";
        }
    }

    // ---- AÑADIR / QUITAR JUGADORES ----
    elseif ($action === 'add_member' || $action === 'remove_member') {
        $idx = intval($_POST['index']);
        if (!isset($userGroups[$idx])) {
            $errors[] = "Grupo inválido.";
        } else {
            if ($action === 'add_member') {
                $steamid = trim($_POST['steamid']);
                $player = trim($_POST['player']);
                if ($steamid === '') $errors[] = "SteamID no puede estar vacío.";
                if ($player === '') $errors[] = "Nombre del jugador no puede estar vacío.";

                if (empty($errors)) {
                    if (!isset($userGroups[$idx]['members'])) $userGroups[$idx]['members'] = [];
                    $userGroups[$idx]['members'][] = ['steamid' => $steamid, 'player' => $player];
                    $success = "Jugador agregado al grupo.";
                }
            } else {
                $steamid = $_POST['steamid'];
                $userGroups[$idx]['members'] = array_values(array_filter($userGroups[$idx]['members'], fn($m) => $m['steamid'] !== $steamid));
                $success = "Jugador eliminado del grupo.";
            }

            $bak = $jsonPath . '.bak_' . date('Ymd_His');
            copy($jsonPath, $bak);
            $config['userGroups'] = $userGroups;
            file_put_contents($jsonPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Roles y Jugadores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#121212;color:#eee}.table-dark td,.table-dark th{vertical-align:middle}</style>
</head>
<body>
<div class="container mt-4">
  <h2>👥 Grupos de Usuario / Roles y Contraseñas</h2>

  <?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php foreach($errors as $e): ?><div class="alert alert-danger"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

  <table class="table table-dark table-striped table-bordered mt-4 align-middle">
    <thead>
      <tr>
        <th>#</th><th>Nombre</th><th>Contraseña</th><th>Kick/Ban</th><th>Inventario</th><th>Base</th><th>Extender</th><th>Slots</th><th>Miembros</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($userGroups as $i=>$g): ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= htmlspecialchars($g['name']) ?></td>
        <td><?= htmlspecialchars($g['password']) ?></td>
        <td><?= $g['canKickBan']?'✔️':'✖️' ?></td>
        <td><?= $g['canAccessInventories']?'✔️':'✖️' ?></td>
        <td><?= $g['canEditBase']?'✔️':'✖️' ?></td>
        <td><?= $g['canExtendBase']?'✔️':'✖️' ?></td>
        <td><?= intval($g['reservedSlots']) ?></td>
        <td>
          <?php if (!empty($g['members'])): ?>
            <ul class="mb-1">
              <?php foreach($g['members'] as $m): ?>
                <li>
                  <?= htmlspecialchars($m['player']) ?> 
                  <small class="text-secondary">(<?= $m['steamid'] ?>)</small>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="remove_member">
                    <input type="hidden" name="index" value="<?= $i ?>">
                    <input type="hidden" name="steamid" value="<?= htmlspecialchars($m['steamid']) ?>">
                    <button class="btn btn-sm btn-danger ms-1">x</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <span class="text-secondary">Sin miembros</span>
          <?php endif; ?>

          <!-- Añadir jugador -->
          <form method="post" class="mt-2 add-member-form">
  <input type="hidden" name="action" value="add_member">
  <input type="hidden" name="index" value="<?= $i ?>">
  <div class="input-group input-group-sm mb-1">
    <select class="form-select recent-select" name="recent_player">
      <option value="">-- Elegir jugador detectado --</option>
    </select>
    <button class="btn btn-outline-info btn-sm" type="button" onclick="loadRecentPlayers(this)">🧠 Detectar</button>
  </div>
  <div class="input-group input-group-sm">
    <input type="text" class="form-control" name="player" placeholder="Nombre" required>
    <input type="text" class="form-control" name="steamid" placeholder="SteamID" required>
    <button class="btn btn-success btn-sm">+</button>
  </div>
</form>

        </td>
        <td>
          <button class="btn btn-sm btn-warning" onclick="editGroup(<?= $i ?>)">Editar</button>
          <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="index" value="<?= $i ?>">
            <button class="btn btn-sm btn-danger">Eliminar</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <hr>
  <h4 id="formTitle">➕ Añadir nuevo grupo</h4>
  <form method="post" class="bg-dark p-4 rounded shadow">
    <input type="hidden" name="action" id="formAction" value="add">
    <input type="hidden" name="index" id="formIndex" value="">
    <div class="row mb-3">
      <div class="col-md-6"><label>Nombre</label><input type="text" class="form-control" name="name" id="inputName" required></div>
      <div class="col-md-6"><label>Contraseña</label><input type="text" class="form-control" name="password" id="inputPassword"></div>
    </div>
    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="canKickBan" id="inputKickBan"><label class="form-check-label" for="inputKickBan">Kick/Ban</label></div>
    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="canAccessInventories" id="inputAccessInv"><label class="form-check-label" for="inputAccessInv">Acceder Inventarios</label></div>
    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="canEditBase" id="inputEditBase"><label class="form-check-label" for="inputEditBase">Editar Base</label></div>
    <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="canExtendBase" id="inputExtendBase"><label class="form-check-label" for="inputExtendBase">Extender Base</label></div>
    <div class="mb-3"><label>Slots Reservados</label><input type="number" class="form-control" name="reservedSlots" id="inputReserved" value="0" min="0"></div>
    <button class="btn btn-primary">Guardar</button>
    <button type="button" class="btn btn-secondary" onclick="resetForm()">Cancelar</button>
  </form>
</div>

<script>
function editGroup(i){
  const g = <?= json_encode($userGroups) ?>[i];
  document.getElementById('formTitle').innerText='Editar grupo: '+g.name;
  document.getElementById('formAction').value='edit';
  document.getElementById('formIndex').value=i;
  document.getElementById('inputName').value=g.name;
  document.getElementById('inputPassword').value=g.password;
  document.getElementById('inputKickBan').checked=g.canKickBan;
  document.getElementById('inputAccessInv').checked=g.canAccessInventories;
  document.getElementById('inputEditBase').checked=g.canEditBase;
  document.getElementById('inputExtendBase').checked=g.canExtendBase;
  document.getElementById('inputReserved').value=g.reservedSlots;
}
function resetForm(){
  document.getElementById('formTitle').innerText='➕ Añadir nuevo grupo';
  document.getElementById('formAction').value='add';
  document.getElementById('formIndex').value='';
  document.querySelectorAll('#inputName,#inputPassword').forEach(e=>e.value='');
  ['inputKickBan','inputAccessInv','inputEditBase','inputExtendBase'].forEach(id=>document.getElementById(id).checked=false);
  document.getElementById('inputReserved').value=0;
}

</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function loadRecentPlayers(btn){
  const select = $(btn).closest('.add-member-form').find('.recent-select');
  select.html('<option>Cargando...</option>');
  $.get('../ajax/get_recent_players.php', function(res){
    if(res.status==='success'){
      select.empty().append('<option value="">-- Seleccionar jugador detectado --</option>');
      res.players.forEach(p=>{
        select.append(`<option value="${p.steamid}|${p.player}">${p.player} (${p.steamid})</option>`);
      });
    }else{
      select.html('<option>'+res.message+'</option>');
    }
  });
}

// Cuando seleccionas un jugador del dropdown, se rellenan los campos
$(document).on('change','.recent-select',function(){
  const val = $(this).val();
  if(!val) return;
  const [sid,name] = val.split('|');
  const form = $(this).closest('.add-member-form');
  form.find('input[name="player"]').val(name);
  form.find('input[name="steamid"]').val(sid);
});
</script>


</body>
</html>
