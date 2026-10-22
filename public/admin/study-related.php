<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$pdo = db();
$studyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($studyId <= 0) { header('Location: /admin/index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM studies WHERE id = :id');
$stmt->execute([':id' => $studyId]);
$study = $stmt->fetch();
if (!$study) { http_response_code(404); exit('No encontrado'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf'] ?? null);
    $section = $_POST['section'] ?? '';
    $action  = $_POST['action'] ?? 'create';
    if ($action === 'delete') {
        $table = ['frequency'=>'frequencies','outcome'=>'outcomes','risk'=>'risk_flags'][$section] ?? null;
        if ($table) {
            $pdo->prepare("DELETE FROM $table WHERE id = :id AND study_id = :sid")
                ->execute([':id' => (int)($_POST['record_id'] ?? 0), ':sid' => $studyId]);
        }
    } elseif ($section === 'frequency') {
        $pdo->prepare('INSERT INTO frequencies (study_id, frequency_min_hz, frequency_max_hz, frequency_type, waveform, exposure_duration, body_region, notes) VALUES (:sid, :min, :max, :type, :wf, :dur, :region, :notes)')
            ->execute([':sid'=>$studyId, ':min'=>(float)($_POST['frequency_min_hz'] ?? 0), ':max'=>(float)($_POST['frequency_max_hz'] ?? 0), ':type'=>$_POST['frequency_type'] ?? 'mechanical_vibration', ':wf'=>trim($_POST['waveform'] ?? '') ?: null, ':dur'=>trim($_POST['exposure_duration'] ?? '') ?: null, ':region'=>trim($_POST['body_region'] ?? '') ?: null, ':notes'=>trim($_POST['notes'] ?? '') ?: null]);
    } elseif ($section === 'outcome') {
        $pdo->prepare('INSERT INTO outcomes (study_id, outcome_type, effect_direction, effect_size, effect_unit, p_value, notes) VALUES (:sid, :type, :dir, :size, :unit, :p, :notes)')
            ->execute([':sid'=>$studyId, ':type'=>$_POST['outcome_type'] ?? 'other', ':dir'=>$_POST['effect_direction'] ?? 'neutral', ':size'=>($_POST['effect_size'] ?? '') !== '' ? (float)$_POST['effect_size'] : null, ':unit'=>trim($_POST['effect_unit'] ?? '') ?: null, ':p'=>($_POST['p_value'] ?? '') !== '' ? (float)$_POST['p_value'] : null, ':notes'=>trim($_POST['notes'] ?? '') ?: null]);
    } elseif ($section === 'risk') {
        $pdo->prepare('INSERT INTO risk_flags (study_id, frequency_range_hz, risk_description, evidence_level) VALUES (:sid, :range, :desc, :ev)')
            ->execute([':sid'=>$studyId, ':range'=>trim($_POST['frequency_range_hz'] ?? ''), ':desc'=>trim($_POST['risk_description'] ?? ''), ':ev'=>$_POST['evidence_level'] ?? 'preliminary']);
    }
    header('Location: /admin/study-related.php?id=' . $studyId); exit;
}

$f = $pdo->prepare('SELECT * FROM frequencies WHERE study_id = :id ORDER BY id'); $f->execute([':id'=>$studyId]); $freqs = $f->fetchAll();
$o = $pdo->prepare('SELECT * FROM outcomes WHERE study_id = :id ORDER BY id'); $o->execute([':id'=>$studyId]); $outs = $o->fetchAll();
$r = $pdo->prepare('SELECT * FROM risk_flags WHERE study_id = :id ORDER BY id'); $r->execute([':id'=>$studyId]); $risks = $r->fetchAll();
$csrf = Security::csrfToken();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Registros asociados</title>
<style>table{border-collapse:collapse;margin-bottom:1em}td,th{border:1px solid #ccc;padding:4px 8px}fieldset{margin-bottom:1.5em;padding:1em;border:1px solid #ccc}</style></head>
<body style="font-family:system-ui;max-width:1200px;margin:2em auto;padding:0 1em">
<p><a href="/admin/index.php">← Volver</a></p>
<h1><?= Security::e($study['title']) ?></h1>

<fieldset><legend>Frecuencias</legend>
<table><tr><th>ID</th><th>Rango (Hz)</th><th>Tipo</th><th>Región</th><th>Notas</th><th></th></tr>
<?php foreach ($freqs as $row): ?><tr>
<td><?= (int)$row['id'] ?></td><td><?= Security::e($row['frequency_min_hz']) ?> – <?= Security::e($row['frequency_max_hz']) ?></td>
<td><?= Security::e($row['frequency_type']) ?></td><td><?= Security::e($row['body_region']) ?></td><td><?= Security::e($row['notes']) ?></td>
<td><form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="section" value="frequency"><input type="hidden" name="action" value="delete"><input type="hidden" name="record_id" value="<?= (int)$row['id'] ?>"><button>Eliminar</button></form></td>
</tr><?php endforeach; ?></table>
<form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="section" value="frequency">
min <input name="frequency_min_hz" type="number" step="0.001" required>
max <input name="frequency_max_hz" type="number" step="0.001" required>
tipo <select name="frequency_type"><option>mechanical_vibration</option><option>audible_sound</option><option>electromagnetic</option><option>mixed</option></select>
duración <input name="exposure_duration"> región <input name="body_region"> notas <input name="notes"> <button>Añadir</button></form>
</fieldset>

<fieldset><legend>Outcomes</legend>
<table><tr><th>ID</th><th>Tipo</th><th>Dirección</th><th>Tamaño</th><th>Unidad</th><th>p</th><th></th></tr>
<?php foreach ($outs as $row): ?><tr>
<td><?= (int)$row['id'] ?></td><td><?= Security::e($row['outcome_type']) ?></td><td><?= Security::e($row['effect_direction']) ?></td>
<td><?= Security::e((string)$row['effect_size']) ?></td><td><?= Security::e($row['effect_unit']) ?></td><td><?= Security::e((string)$row['p_value']) ?></td>
<td><form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="section" value="outcome"><input type="hidden" name="action" value="delete"><input type="hidden" name="record_id" value="<?= (int)$row['id'] ?>"><button>Eliminar</button></form></td>
</tr><?php endforeach; ?></table>
<form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="section" value="outcome">
tipo <select name="outcome_type"><option>milk_yield</option><option>udder_health</option><option>weight_gain</option><option>stress_indicator</option><option>feed_intake</option><option>reproduction</option><option>other</option></select>
dirección <select name="effect_direction"><option>positive</option><option>neutral</option><option>negative</option><option>mixed</option></select>
tamaño <input name="effect_size" type="number" step="0.001"> unidad <input name="effect_unit"> p <input name="p_value" type="number" step="0.0001"> <button>Añadir</button></form>
</fieldset>

<fieldset><legend>Banderas de riesgo</legend>
<table><tr><th>ID</th><th>Rango (Hz)</th><th>Descripción</th><th>Evidencia</th><th></th></tr>
<?php foreach ($risks as $row): ?><tr>
<td><?= (int)$row['id'] ?></td><td><?= Security::e($row['frequency_range_hz']) ?></td><td><?= Security::e($row['risk_description']) ?></td><td><?= Security::e($row['evidence_level']) ?></td>
<td><form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar?')"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="section" value="risk"><input type="hidden" name="action" value="delete"><input type="hidden" name="record_id" value="<?= (int)$row['id'] ?>"><button>Eliminar</button></form></td>
</tr><?php endforeach; ?></table>
<form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="section" value="risk">
rango <input name="frequency_range_hz" placeholder="6000–8000" required> descripción <input name="risk_description" required>
evidencia <select name="evidence_level"><option>high</option><option>medium</option><option>low</option><option>preliminary</option></select> <button>Añadir</button></form>
</fieldset>
</body></html>
