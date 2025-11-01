<?php
require_once __DIR__ . '/../config.php';

$ftp_server = "localhost";
$ftp_user = "enshrouded";
$ftp_pass = "35027595";
$file = $_REQUEST['file'] ?? '';
$mode = $_REQUEST['mode'] ?? 'read';

if (!$file) die("❌ Archivo no especificado.");

$conn = ftp_connect($ftp_server);
$login = ftp_login($conn, $ftp_user, $ftp_pass);
ftp_pasv($conn, true);

$temp = tempnam(sys_get_temp_dir(), 'ftp');
if ($mode === 'read') {
    if (ftp_get($conn, $temp, $file, FTP_ASCII)) {
        echo file_get_contents($temp);
    } else {
        echo "❌ No se pudo leer el archivo.";
    }
} elseif ($mode === 'save') {
    file_put_contents($temp, $_POST['content'] ?? '');
    if (ftp_put($conn, $file, $temp, FTP_ASCII)) {
        echo "✅ Guardado correctamente.";
    } else {
        echo "❌ Error al guardar.";
    }
}
ftp_close($conn);
@unlink($temp);
