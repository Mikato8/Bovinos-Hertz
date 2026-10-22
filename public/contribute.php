<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Services/SubmissionService.php';

$ok = false; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf'] ?? null);
    if (!empty($_POST['website'])) {
        $ok = true;
    } else {
        $data = [
            'submitter_name' => trim($_POST['submitter_name'] ?? ''),
            'submitter_email' => trim($_POST['submitter_email'] ?? ''),
            'submitter_affiliation' => trim($_POST['submitter_affiliation'] ?? ''),
            'title' => trim($_POST['title'] ?? ''),
            'authors' => trim($_POST['authors'] ?? ''),
            'publication_year' => ($_POST['publication_year'] ?? '') !== '' ? (int)$_POST['publication_year'] : null,
            'doi' => trim($_POST['doi'] ?? ''),
            'url' => trim($_POST['url'] ?? ''),
            'source_type' => $_POST['source_type'] ?? 'paper',
            'species' => $_POST['species'] ?? 'dairy_cows',
            'context' => $_POST['context'] ?? 'other',
            'summary' => trim($_POST['summary'] ?? ''),
            'evidence_level' => $_POST['evidence_level'] ?? 'preliminary',
            'frequencies' => [], 'outcomes' => [], 'risks' => [],
        ];
        foreach (($_POST['freq'] ?? []) as $f) {
            if (($f['min'] ?? '') === '' && ($f['max'] ?? '') === '') continue;
            $data['frequencies'][] = [
                'min' => (float)($f['min'] ?? 0), 'max' => (float)($f['max'] ?? 0),
                'type'=> $f['type'] ?? 'mechanical_vibration',
                'duration' => trim($f['duration'] ?? ''), 'region' => trim($f['region'] ?? ''),
                'notes' => trim($f['notes'] ?? ''),
            ];
        }
        foreach (($_POST['outc'] ?? []) as $o) {
            if (($o['type'] ?? '') === '') continue;
            $data['outcomes'][] = [
                'type' => $o['type'], 'direction' => $o['direction'] ?? 'neutral',
                'size' => $o['size'] ?? '', 'unit' => trim($o['unit'] ?? ''),
                'p_value' => $o['p_value'] ?? '', 'notes' => trim($o['notes'] ?? ''),
            ];
        }
        foreach (($_POST['risk'] ?? []) as $r) {
            if (($r['range'] ?? '') === '' || ($r['description'] ?? '') === '') continue;
            $data['risks'][] = ['range' => trim($r['range']), 'description' => trim($r['description']), 'evidence' => $r['evidence'] ?? 'preliminary'];
        }
        if ($data['submitter_name'] === '' || !filter_var($data['submitter_email'], FILTER_VALIDATE_EMAIL) || $data['title'] === '') {
            $error = 'Nombre, email válido y título son obligatorios.';
        } else {
            $svc = new SubmissionService(db());
            $id = $svc->store($data);
            @mail($_ENV['ADMIN_EMAIL'] ?? 'admin@example.com', "[Hertz] Submission #$id", "Revisar /admin/submissions.php", 'From: noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $ok = true;
        }
    }
}
$csrf = Security::csrfToken();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Contribuir — Hertz Bovinos</title>
<style>fieldset{margin:1em 0;padding:1em;border:1px solid #ccc}.row{display:flex;gap:8px;margin:4px 0}input[type=text],input[type=email],input[type=number],input[type=url]{flex:1}.hint{color:#666;font-size:.9em}</style>
</head><body style="font-family:system-ui;max-width:900px;margin:2em auto;padding:0 1em">
<p><a href="/index.php">← Inicio</a></p>
<h1>Contribuir un estudio</h1>
<?php if ($ok): ?>
<p style="color:#2e7d32"><strong>¡Gracias!</strong> Tu propuesta fue recibida y será revisada antes de publicarse.</p>
<?php else: ?>
<?php if ($error): ?><p style="color:#c62828"><?= Security::e($error) ?></p><?php endif; ?>
<p class="hint">Sólo estudios con fuente verificable. Cada propuesta pasa por revisión editorial.</p>
<form method="post">
<input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
<fieldset><legend>Contribuyente</legend>
<div class="row"><input type="text" name="submitter_name" placeholder="Nombre *" required><input type="email" name="submitter_email" placeholder="Email *" required></div>
<div class="row"><input type="text" name="submitter_affiliation" placeholder="Institución (opcional)"></div>
</fieldset>
<fieldset><legend>Estudio</legend>
<div class="row"><input type="text" name="title" placeholder="Título *" required></div>
<div class="row"><input type="text" name="authors" placeholder="Autores"></div>
<div class="row"><input type="number" name="publication_year" placeholder="Año" min="1900" max="2100"><input type="text" name="doi" placeholder="DOI"><input type="url" name="url" placeholder="URL"></div>
<div class="row">
<select name="source_type"><option>paper</option><option>patent</option><option>review</option><option>thesis</option><option>report</option></select>
<select name="species"><option>dairy_cows</option><option>beef_cattle</option><option>calves</option><option>mixed</option></select>
<select name="context"><option>milking</option><option>feeding</option><option>transport</option><option>growth</option><option>reproduction</option><option>housing</option><option>other</option></select>
<select name="evidence_level"><option>preliminary</option><option>low</option><option>medium</option><option>high</option></select>
</div>
<div class="row"><textarea name="summary" rows="4" style="flex:1" placeholder="Resumen del hallazgo"></textarea></div>
</fieldset>
<fieldset><legend>Frecuencias (opcional)</legend>
<div class="row"><input type="number" step="0.001" name="freq[0][min]" placeholder="min Hz"><input type="number" step="0.001" name="freq[0][max]" placeholder="max Hz">
<select name="freq[0][type]"><option value="mechanical_vibration">mechanical_vibration</option><option value="audible_sound">audible_sound</option><option value="electromagnetic">electromagnetic</option><option value="mixed">mixed</option></select>
<input type="text" name="freq[0][duration]" placeholder="duración"><input type="text" name="freq[0][region]" placeholder="región"><input type="text" name="freq[0][notes]" placeholder="notas"></div>
</fieldset>
<fieldset><legend>Outcome (opcional)</legend>
<div class="row">
<select name="outc[0][type]"><option value="">— tipo —</option><option value="milk_yield">milk_yield</option><option value="udder_health">udder_health</option><option value="weight_gain">weight_gain</option><option value="stress_indicator">stress_indicator</option><option value="feed_intake">feed_intake</option><option value="reproduction">reproduction</option><option value="other">other</option></select>
<select name="outc[0][direction]"><option>positive</option><option>neutral</option><option>negative</option><option>mixed</option></select>
<input type="number" step="0.001" name="outc[0][size]" placeholder="tamaño"><input type="text" name="outc[0][unit]" placeholder="unidad"><input type="number" step="0.0001" name="outc[0][p_value]" placeholder="p-value"><input type="text" name="outc[0][notes]" placeholder="notas"></div>
</fieldset>
<fieldset><legend>Riesgo (opcional)</legend>
<div class="row"><input type="text" name="risk[0][range]" placeholder="rango (ej. 6000–8000)"><input type="text" name="risk[0][description]" placeholder="descripción"><select name="risk[0][evidence]"><option>preliminary</option><option>low</option><option>medium</option><option>high</option></select></div>
</fieldset>
<p><button>Enviar propuesta</button></p>
</form>
<?php endif; ?>
</body></html>
