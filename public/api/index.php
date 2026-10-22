<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>API Hertz Bovinos</title></head>
<body style="font-family:system-ui;max-width:900px;margin:2em auto;line-height:1.6">
<h1>API pública — Hertz en bovinos</h1>
<p><code>GET /api/studies.php</code> · licencia CC-BY-4.0</p>
<h2>Parámetros</h2>
<table border="1" cellpadding="6"><tr><th>Parámetro</th><th>Tipo</th><th>Descripción</th></tr>
<tr><td><code>min</code></td><td>float</td><td>Frecuencia mínima Hz</td></tr>
<tr><td><code>max</code></td><td>float</td><td>Frecuencia máxima Hz</td></tr>
<tr><td><code>context</code></td><td>string</td><td>milking, feeding, transport, growth, reproduction, housing, other</td></tr>
<tr><td><code>species</code></td><td>string</td><td>dairy_cows, beef_cattle, calves, mixed</td></tr>
<tr><td><code>evidence</code></td><td>string</td><td>high, medium, low, preliminary</td></tr>
<tr><td><code>q</code></td><td>string</td><td>Búsqueda libre</td></tr>
<tr><td><code>limit</code></td><td>int</td><td>1–200 (50)</td></tr>
<tr><td><code>offset</code></td><td>int</td><td>Paginación (0)</td></tr></table>
<h2>Ejemplos</h2>
<ul>
<li><a href="/api/studies.php?min=15&max=60">?min=15&max=60</a></li>
<li><a href="/api/studies.php?context=milking">?context=milking</a></li>
<li><a href="/api/studies.php?min=6000&max=8000">?min=6000&max=8000</a> (zona de riesgo)</li>
</ul>
</body></html>
