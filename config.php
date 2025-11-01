<?php
/**
 * config.php
 * Configuración principal del Panel Enshrouded
 */

// =====================================================
// 🧠 SESIÓN DE USUARIO
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_name('panel_pruebas');
    session_start();
}

// =====================================================
// 🔐 CREDENCIALES DE ADMINISTRADOR
// =====================================================
define('ADMIN_USER', getenv('ADMIN_USER') ?: 'Azzlaer');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: '35027595');

// =====================================================
// 💼 INFORMACIÓN GENERAL DEL PANEL
// =====================================================
define('FOOTER_TEXT', 'Panel Enshrouded © ' . date('Y'));

// =====================================================
// 🗺️ RUTAS PRINCIPALES DEL SERVIDOR
// =====================================================
$enshrouded_server_path = "D:\\Steam\\steamapps\\common\\EnshroudedServer"; // Ruta base del servidor
$steamcmd_path          = "D:\\Steam\\steamcmd.exe";                        // Ruta de SteamCMD

// =====================================================
// ⚙️ CONFIGURACIONES DEL SERVIDOR
// =====================================================
$server_port      = 15637;
$server_json_path = $enshrouded_server_path . "\\enshrouded_server.json";
$server_exe_path  = $enshrouded_server_path . "\\enshrouded_server.exe";
$server_log_path  = $enshrouded_server_path . "\\logs\\enshrouded_server.log";


// Carpeta de archivo de logs (para mover los logs comprimidos)
$log_archive_directory = __DIR__ . "\\log_archives";  // o ruta absoluta si lo prefieres


// ---- REINICIO PROGRAMADO ----
// Cada cuántas horas reiniciar automáticamente el servidor
$auto_restart_hours = 6;

// Cuántos segundos esperar entre "guardar" y "reiniciar"
$auto_restart_delay = 20;

// =====================================================
// 💾 CONFIGURACIÓN DE BACKUPS
// =====================================================

// Archivos y carpetas que se incluirán en cada backup
$backup_directory = __DIR__ . "\\backups";
$backup_sources = [
    $enshrouded_server_path . "\\savegame",
    $enshrouded_server_path . "\\enshrouded_server.json",
    $enshrouded_server_path . "\\profile"
];

// Límite máximo de backups almacenados
$backup_limit = 5;

// =====================================================
// ⏰ BACKUPS AUTOMÁTICOS
// =====================================================

// Intervalo de tiempo entre backups automáticos (en horas)
$auto_backup_interval_hours = 24;

// Archivo donde se guarda la hora del último backup automático
$auto_backup_timestamp_file = __DIR__ . "\\last_auto_backup.txt";



// =====================================================
// 🧩 FUNCIONES AUXILIARES
// =====================================================

/**
 * Redirige a una URL de forma segura.
 */
function redirect(string $url) {
    header("Location: " . $url);
    exit;
}

/**
 * Verifica si el usuario está logueado.
 */
function is_logged_in(): bool {
    return !empty($_SESSION['logged_in']);
}
