<?php

// Datos de conexión al FTP
$ftp_server = "localhost";
$ftp_user = "enshrouded";
$ftp_pass = "35027595";

// Validar que venga el parámetro 'file'
if (!isset($_GET['file'])) {
    die("Error: Parámetro 'file' no especificado.");
}

// Conectar al FTP
$ftp_conn = ftp_connect($ftp_server) or die("No se pudo conectar al servidor FTP");
$login    = ftp_login($ftp_conn, $ftp_user, $ftp_pass);
if (!$login) {
    die("Error de autenticación en el servidor FTP");
}

// Activar modo pasivo
ftp_pasv($ftp_conn, true);

// Nombre remoto del archivo
$file_ftp_path = urldecode($_GET['file']);
// Nombre local (descargado) para el usuario
$filename = basename($file_ftp_path);

// Crear un archivo temporal donde guardar el contenido antes de enviarlo
$tmp_path = tempnam(sys_get_temp_dir(), 'ftp_');

// Descargar archivo desde el FTP al archivo temporal
$handle = fopen($tmp_path, 'w');
if (@ftp_fget($ftp_conn, $handle, $file_ftp_path, FTP_BINARY, 0)) {
    // Cerrar archivo temporal
    fclose($handle);

    // Ajustar encabezados para forzar la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($tmp_path));

    // Leer el archivo temporal y enviarlo al output
    readfile($tmp_path);

    // Eliminar el archivo temporal
    unlink($tmp_path);
} else {
    // Error al descargar desde FTP
    fclose($handle);
    unlink($tmp_path);
    die("Error al descargar el archivo desde FTP: $file_ftp_path");
}

// Cerrar la conexión FTP
ftp_close($ftp_conn);
?>
