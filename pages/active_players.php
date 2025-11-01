<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Jugadores Activos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #121212; color: #eee; }
.table-dark td, .table-dark th { vertical-align: middle; }
#chartContainer { background:#1e1e1e; padding:20px; border-radius:8px; }
label, select { color:#ccc; }
.btn-sm { font-size: 0.85rem; }
</style>
</head>
<body>
<div class="container mt-4">
  <h2>🧍 Jugadores Activos</h2>
  <p class="text-secondary">Monitoreo en tiempo real del servidor Enshrouded.</p>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      ⏱️ Intervalo de actualización:
      <select id="intervalSelector" class="form-select form-select-sm d-inline-block w-auto">
        <option value="15000">15 s</option>
        <option value="30000" selected>30 s</option>
        <option value="60000">60 s</option>
      </select>
    </div>
    <div>
      💾 Exportar:
      <button class="btn btn-success btn-sm" id="exportJson">JSON</button>
      <button class="btn btn-primary btn-sm" id="exportCsv">CSV</button>
    </div>
  </div>

  <div id="playerTable" class="mt-3">
    <div class="spinner-border text-light" role="status"></div>
    <p class="mt-2">Cargando jugadores...</p>
  </div>

  <div id="chartContainer" class="mt-5">
    <h5 class="text-light mb-3">📈 Conexiones en los últimos minutos</h5>
    <canvas id="playersChart" height="120"></canvas>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartLabels = [];
let chartData = [];
let avgData = [];
let refreshInterval = 30000;
let timer;

// función para calcular media móvil (últimos 10 puntos)
function calcMovingAverage(data, windowSize = 10) {
  if (data.length < windowSize) return Array(data.length).fill(null);
  let avg = [];
  for (let i = 0; i < data.length; i++) {
    if (i < windowSize) {
      avg.push(null);
    } else {
      const slice = data.slice(i - windowSize, i);
      const sum = slice.reduce((a,b)=>a+b,0);
      avg.push(sum / windowSize);
    }
  }
  return avg;
}

// inicializar gráfico
const ctx = document.getElementById('playersChart').getContext('2d');
const playersChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: chartLabels,
    datasets: [
      {
        label: 'Jugadores conectados',
        data: chartData,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.2)',
        tension: 0.3,
        fill: true
      },
      {
        label: 'Promedio (últimos 10 puntos)',
        data: avgData,
        borderColor: '#00c853',
        backgroundColor: 'transparent',
        borderDash: [5,5],
        tension: 0.3,
        fill: false
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { labels: { color: '#ddd' } }
    },
    scales: {
      x: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,0.1)' } },
      y: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,0.1)' }, beginAtZero:true, precision:0 }
    }
  }
});

function loadActivePlayers(saveHistory = true){
  $.get('../ajax/get_active_players.php', function(res){
    if(res.status === 'success' || res.status === 'empty'){
      const players = res.players || [];
      let html = `
      <table class="table table-dark table-bordered table-striped mt-3">
        <thead><tr><th>#</th><th>Nombre</th><th>SteamID</th><th>Grupo</th><th>Tiempo conectado</th></tr></thead><tbody>`;
      players.forEach((p,i)=>{
        html += `<tr><td>${i+1}</td><td>${p.player}</td><td>${p.steamid}</td><td>${p.group || '<span class="text-secondary">N/A</span>'}</td><td>${p.time_online}</td></tr>`;
      });
      if(players.length === 0) html += `<tr><td colspan="5" class="text-center text-secondary">Sin jugadores conectados</td></tr>`;
      html += `</tbody></table>`;
      $('#playerTable').html(html);

      const now = new Date();
      const label = now.toLocaleTimeString();
      chartLabels.push(label);
      chartData.push(players.length);
      if(chartLabels.length > 120){ chartLabels.shift(); chartData.shift(); }

      avgData = calcMovingAverage(chartData, 10);
      playersChart.update();

      if(saveHistory){
        $.post('../ajax/save_players_history.php', { timestamp: now.toISOString(), count: players.length });
      }
    } else {
      $('#playerTable').html('<div class="alert alert-danger">'+res.message+'</div>');
    }
  }).fail(()=>{
    $('#playerTable').html('<div class="alert alert-danger">Error al conectar con el servidor.</div>');
  });
}

function startTimer(){
  if(timer) clearInterval(timer);
  timer = setInterval(()=>loadActivePlayers(true), refreshInterval);
}

// inicial
loadActivePlayers(true);
startTimer();

// cambio de intervalo
$('#intervalSelector').on('change', function(){
  refreshInterval = parseInt($(this).val());
  startTimer();
});

// exportar JSON
$('#exportJson').on('click', ()=>window.location.href='../ajax/get_players_history.php?format=json');
// exportar CSV
$('#exportCsv').on('click', ()=>window.location.href='../ajax/get_players_history.php?format=csv');
</script>
</body>
</html>
