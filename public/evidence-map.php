<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Repositories/StudyRepository.php';

$rows = (new StudyRepository(db()))->allForMap();
$points = [];
foreach ($rows as $r) {
    $points[] = [
        'x' => (float)$r['frequency_min_hz'],
        'y' => (float)$r['frequency_max_hz'],
        'label' => $r['title'],
        'direction' => $r['effect_direction'] ?? 'neutral',
    ];
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Mapa de evidencia</title></head>
<body style="font-family:system-ui;max-width:1000px;margin:2em auto;padding:0 1em">
<h1>Mapa de evidencia — frecuencia (Hz) vs. efecto</h1>
<p><a href="/index.php">← Inicio</a></p>
<canvas id="map" width="900" height="500"></canvas>
<script src="/assets/js/chart.min.js"></script>
<script>
const points = <?= json_encode($points, JSON_UNESCAPED_UNICODE) ?>;
const colors = { positive: '#2e7d32', neutral: '#616161', negative: '#c62828', mixed: '#f9a825' };
const datasets = Object.keys(colors).map(dir => ({
    label: dir,
    data: points.filter(p => p.direction === dir),
    backgroundColor: colors[dir],
    pointRadius: 6
}));
new Chart(document.getElementById('map'), {
    type: 'scatter',
    data: { datasets },
    options: {
        scales: {
            x: { type: 'logarithmic', title: { display: true, text: 'Frecuencia mín. (Hz, log)' } },
            y: { type: 'logarithmic', title: { display: true, text: 'Frecuencia máx. (Hz, log)' } }
        },
        plugins: { tooltip: { callbacks: { label: ctx => ctx.raw.label } } }
    }
});
</script>
</body></html>
