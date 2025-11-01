<?php
require_once "config.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Enshrouded</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: #121212;
      color: #eee;
      font-family: "Segoe UI", Arial, sans-serif;
    }

    .sidebar {
      min-height: 100vh;
      background: #1e1e1e;
    }

    .nav-link {
      color: #ccc !important;
      border-radius: 6px;
      margin-bottom: 4px;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.2s ease, color 0.2s ease;
    }

    .nav-link i {
      font-size: 1.2rem;
      color: #aaa;
      transition: color 0.2s ease;
    }

    .nav-link:hover {
      background: #2a2a2a;
      color: #fff !important;
    }

    .nav-link:hover i {
      color: #fff;
    }

    /* 💡 Estado activo sin color azul */
    .nav-link.active {
      background: #2a2a2a !important;
      color: #fff !important;
      font-weight: 600;
      box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.05);
    }

    .nav-link.active i {
      color: #fff !important;
    }

    main {
      padding: 20px;
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
      <h3 class="text-light mb-4">⚔️ Panel</h3>
      <div class="nav flex-column nav-pills">

        <a href="#" class="nav-link active" data-section="pages/dashboard">
          <i class="bi bi-bar-chart-fill"></i> Dashboard
        </a>
        <a href="#" class="nav-link" data-section="pages/server_autorestart">
          <i class="bi bi-arrow-repeat"></i> Auto Restart
        </a>
        <a href="#" class="nav-link" data-section="pages/server_control">
          <i class="bi bi-hdd-stack"></i> Server Control
        </a>
        <a href="#" class="nav-link" data-section="pages/server_settings">
          <i class="bi bi-gear-fill"></i> Server Settings
        </a>
        <a href="#" class="nav-link" data-section="pages/server_console">
          <i class="bi bi-terminal"></i> Server Console
        </a>
        <a href="#" class="nav-link" data-section="pages/server_updater">
          <i class="bi bi-cloud-arrow-up"></i> Server Updater
        </a>
        <a href="#" class="nav-link" data-section="pages/parse_log">
          <i class="bi bi-people"></i> Usuarios Online
        </a>
        <a href="#" class="nav-link" data-section="pages/user_groups">
          <i class="bi bi-shield-lock"></i> Grupos y Roles
        </a>
        <a href="#" class="nav-link" data-section="pages/server_tools">
          <i class="bi bi-box-seam"></i> Server Tools / Backups
        </a>
        <a href="#" class="nav-link" data-section="pages/ftp_manager">
          <i class="bi bi-globe2"></i> FTP Manager
        </a>
        <a href="logout.php" class="nav-link text-danger mt-3">
          <i class="bi bi-door-open-fill"></i> Cerrar Sesión
        </a>
      </div>
    </nav>

    <!-- Main content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="main">
      <div class="text-center p-5 text-light">
        👋 Bienvenido al Panel Enshrouded
      </div>

      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

      <script>
      $(function(){
        $('.sidebar .nav-link').on('click', function(e){
          const page = $(this).data('section') || $(this).data('page');
          if (!page) return;

          e.preventDefault();
          $('.sidebar .nav-link').removeClass('active');
          $(this).addClass('active');
          $('#main').html('<div class="p-5 text-center text-light">Cargando…</div>');

          const path = page.startsWith('pages/') ? page : 'pages/' + page;
          $('#main').load(path + '.php');
        });
      });
      </script>
    </main>
  </div>
</div>
</body>
</html>
