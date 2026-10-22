<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Repositories/StudyRepository.php';

$repo = new StudyRepository(db());
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$study = $repo->find($id);
if (!$study) { http_response_code(404); exit('Estudio no encontrado'); }
$freqs = $repo->frequenciesOf($id);
$outs  = $repo->outcomesOf($id);
$risks = $repo->risksOf($id);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title><?= Security::e($study['title']) ?></title>
<style>table{border-collapse:collapse;margin:1em 0}td,th{border:1px solid #ccc;padding:6px}</style>
</head><body style="font-family:system-ui;max-width:900px;margin:2em auto;padding:0 1em">
<p><a href="/index.php">← Inicio</a></p>
<h1><?= Security::e($study['title']) ?></h1>
<p><strong>Autores:</strong> <?= Security::e($study['authors']) ?><br>
   <strong>Año:</strong> <?= Security::e((string)$study['publication_year']) ?> ·
   <strong>DOI:</strong> <?= Security::e($study['doi']) ?></p>
<p><?= nl2br(Security::e($study['summary'])) ?></p>

<?php if ($freqs): ?>
<h2>Frecuencias</h2>
<table><tr><th>min</th><th>max</th><th>tipo</th><th>duración</th><th>región</th><th>notas</th></tr>
<?php foreach ($freqs as $f): ?><tr>
<td><?= Security::e((string)$f['frequency_min_hz']) ?></td>
<td><?= Security::e((string)$f['frequency_max_hz']) ?></td>
<td><?= Security::e($f['frequency_type']) ?></td>
<td><?= Security::e($f['exposure_duration']) ?></td>
<td><?= Security::e($f['body_region']) ?></td>
<td><?= Security::e($f['notes']) ?></td>
</tr><?php endforeach; ?></table>
<?php endif; ?>

<?php if ($outs): ?>
<h2>Outcomes</h2>
<table><tr><th>tipo</th><th>dirección</th><th>tamaño</th><th>unidad</th><th>p</th></tr>
<?php foreach ($outs as $o): ?><tr>
<td><?= Security::e($o['outcome_type']) ?></td><td><?= Security::e($o['effect_direction']) ?></td>
<td><?= Security::e((string)$o['effect_size']) ?></td><td><?= Security::e($o['effect_unit']) ?></td>
<td><?= Security::e((string)$o['p_value']) ?></td>
</tr><?php endforeach; ?></table>
<?php endif; ?>

<?php if ($risks): ?>
<h2>Riesgos</h2>
<table><tr><th>rango</th><th>descripción</th><th>evidencia</th></tr>
<?php foreach ($risks as $r): ?><tr>
<td><?= Security::e($r['frequency_range_hz']) ?></td>
<td><?= Security::e($r['risk_description']) ?></td>
<td><?= Security::e($r['evidence_level']) ?></td>
</tr><?php endforeach; ?></table>
<?php endif; ?>
</body></html>
