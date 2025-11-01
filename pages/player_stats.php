<?php
require_once __DIR__ . '/../config.php';
$historyPath = __DIR__ . '/../data/players_history.json';

function calc_stats($data) {
    if (empty($data)) return null;

    $counts = array_column($data, 'count');
    sort($counts);
    $n = count($counts);
    $min = min($counts);
    $max = max($counts);
    $sum = array_sum($counts);
    $avg = $sum / $n;

    $median = $n % 2 === 0
        ? ($counts[$n/2 - 1] + $counts[$n/2]) / 2
        : $counts[floor($n/2)];

    $p90 = $counts[floor($n * 0.9)] ?? end($counts);
    $p95 = $counts[floor($n * 0.95)] ?? end($counts);

    return [
        'count' => $n,
        'min' => $min,
        'max' => $max,
        'avg' => round($avg, 2),
        'median' => $median,
        'p90' => $p90,
        'p95' => $p95
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📊 Estadísticas de Jugadores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { background:#121212; color:#eee; }
.stat-card { background:#1e1e1e; padding:15px; border-radius:10px; text-align:center; margin:10px 0; }
.stat-value { font-size:1.6rem; color:#0d6efd; }
</style>
</head>
<body>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center">
    <h2>📈 Estadísticas de Jugadores</h2>
    <small class="text-secondary" id="lastUpdated">Última actualización: --:--:--</small>
  </div>
  <p class="text-secondary">Análisis avanzado basado en el historial de conexiones del servidor.</p>

  <div id="statsContainer" class="row"></div>

  <div class="mt-4 bg-dark p-3 rounded">
    <canvas id="playersChart" height="120"></canvas>
  </div>

  <div class="mt-4 text-end">
    <a href="../ajax/get_players_history.php?format=json" class="btn btn-success btn-sm">Exportar JSON</a>
    <a href="../ajax/get_players_history.php?format=csv" class="btn btn-primary btn-sm">Exportar CSV</a>
  </div>
</div>

<script>
let chartInstance = null;

// Función para cargar y renderizar los datos
function loadPlayerStats(){
  $.getJSON('../data/players_history.json', function(data){
    if (!data || data.length === 0) {
      $('#statsContainer').html('<div class="alert alert-warning">No hay datos en el historial.</div>');
      return;
    }

    // Calcular estadísticas
    const counts = data.map(d=>d.count);
    counts.sort((a,b)=>a-b);
    const n = counts.length;
    const sum = counts.reduce((a,b)=>a+b,0);
    const min = counts[0];
    const max = counts[n-1];
    const avg = sum / n;
    const median = n%2===0 ? (counts[n/2-1]+counts[n/2])/2 : counts[Math.floor(n/2)];
    const p90 = counts[Math.floor(n*0.9)] || max;
    const p95 = counts[Math.floor(n*0.95)] || max;

    // Renderizar tarjetas
    const cards = `
      <div class="col-md-2 stat-card"><div>🧾 Muestras</div><div class="stat-value">${n}</div></div>
      <div class="col-md-2 stat-card"><div>🔼 Máximo</div><div class="stat-value">${max}</div></div>
      <div class="col-md-2 stat-card"><div>🔽 Mínimo</div><div class="stat-value">${min}</div></div>
      <div class="col-md-2 stat-card"><div>⚖️ Promedio</div><div class="stat-value">${avg.toFixed(2)}</div></div>
      <div class="col-md-2 stat-card"><div>📈 Mediana</div><div class="stat-value">${median}</div></div>
      <div class="col-md-2 stat-card"><div>🏁 P95</div><div class="stat-value">${p95}</div></div>`;
    $('#statsContainer').html(cards);

    // Actualizar hora
    const now = new Date();
    $('#lastUpdated').text('Última actualización: ' + now.toLocaleTimeString());

    // Preparar gráfico
    const labels = data.map(d => new Date(d.timestamp).toLocaleTimeString());
    const values = data.map(d => d.count);
    const avgLine = data.map(d => d.average || null);

    const ctx = document.getElementById('playersChart').getContext('2d');
    if (chartInstance) chartInstance.destroy();

    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'Jugadores conectados',
            data: values,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.2)',
            tension: 0.3,
            fill: true
          },
          {
            label: 'Promedio móvil',
            data: avgLine,
            borderColor: '#00c853',
            borderDash: [5,5],
            fill: false,
            tension: 0.3
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: '#fff' } }
        },
        scales: {
          x: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,0.1)' } },
          y: { ticks: { color: '#bbb' }, grid: { color: 'rgba(255,255,255,0.1)' }, beginAtZero:true }
        }
      }
    });
  });
}

// Cargar datos iniciales
loadPlayerStats();

// Recargar automáticamente cada 60 segundos
setInterval(loadPlayerStats, 60000);
</script>
</body>
</html>
