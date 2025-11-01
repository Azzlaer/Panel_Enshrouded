<?php

// Indica la ruta real local donde se encuentran los archivos
$base_dir = "C:\\Games\\mta\\mods\\deathmatch\\resources";

// Asegura que vengan parámetros válidos
if (!isset($_GET['action']) || !isset($_GET['file'])) {
    die("<div class='alert alert-danger'>❌ Error: Parámetros inválidos.</div>");
}

$action = $_GET['action'];
$ftp_file = urldecode($_GET['file']); // Ej: "/mods/deathmatch/resources/[gameplay]/deathmessages.zip"

// Quita la barra inicial
$ftp_file = ltrim($ftp_file, '/');

// Si detecta el prefijo "mods/deathmatch/resources", lo eliminamos de la ruta
$buscar_prefijo = "mods/deathmatch/resources/";
if (stripos($ftp_file, $buscar_prefijo) === 0) {
    // recortar "mods/deathmatch/resources/" del inicio
    $ftp_file = substr($ftp_file, strlen($buscar_prefijo));
}

// Convertir "/" a "\" para Windows
$ftp_file = str_replace("/", "\\", $ftp_file);

// Construir ruta local
$file_local = $base_dir . "\\" . $ftp_file;
$file_real  = realpath($file_local);

// Verificar que el archivo existe
if (!$file_real || !file_exists($file_real)) {
    die("<div class='alert alert-danger'>❌ Error: El archivo no existe. Ruta: " 
        . htmlspecialchars($file_local) . "</div>");
}

// Determinar carpeta del archivo
$file_dir = dirname($file_real);
$ext      = strtolower(pathinfo($file_real, PATHINFO_EXTENSION));

// Extraer o Comprimir
if ($action === 'extract') {
    if ($ext === 'zip') {
        $zip = new ZipArchive();
        if ($zip->open($file_real) === TRUE) {
            $zip->extractTo($file_dir);
            $zip->close();
            echo "<script>alert('✅ Archivo ZIP extraído correctamente.'); 
                  window.location.href='ftp_manager.php';</script>";
        } else {
            echo "<div class='alert alert-danger'>❌ Error al extraer ZIP.</div>";
        }
    } elseif ($ext === 'rar') {
        // Extensión rar en PHP (si la tienes habilitada)
        if (function_exists('rar_open')) {
            $rar = rar_open($file_real);
            if ($rar) {
                $entries = rar_list($rar);
                foreach ($entries as $entry) {
                    $entry->extract($file_dir);
                }
                rar_close($rar);
                echo "<script>alert('✅ Archivo RAR extraído correctamente.'); 
                      window.location.href='ftp_manager.php';</script>";
            } else {
                echo "<div class='alert alert-danger'>❌ Error al extraer RAR.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>
                    ❌ Extensión RAR no disponible en PHP (o usa WinRAR via shell_exec).
                  </div>";
        }
    } else {
        echo "<div class='alert alert-danger'>❌ Formato no válido para extraer.</div>";
    }
}
elseif ($action === 'compress') {
    // Crear un ZIP
    $zip_name = basename($file_real) . '.zip';
    $dest_zip = $file_dir . "\\" . $zip_name;

    $zip = new ZipArchive();
    if ($zip->open($dest_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        if (is_dir($file_real)) {
            // Comprimir carpeta entera
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($file_real, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $info) {
                $localPath = $iterator->getSubPathName();
                if ($info->isDir()) {
                    $zip->addEmptyDir($localPath);
                } else {
                    $zip->addFile($info->getRealPath(), $localPath);
                }
            }
        } else {
            // Comprimir archivo individual
            $zip->addFile($file_real, basename($file_real));
        }
        $zip->close();
        echo "<script>alert('✅ Archivo comprimido: " . basename($file_real) . ".zip'); 
              window.location.href='ftp_manager.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>❌ No se pudo crear el archivo ZIP.</div>";
    }
}
else {
    echo "<div class='alert alert-danger'>❌ Acción desconocida.</div>";
}
?>
