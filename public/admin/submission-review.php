<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/Services/SubmissionService.php';
Auth::requireLogin();

$svc = new SubmissionService(db());
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sub = $svc->find($id);
if (!$sub) { http_response_code(404); exit('No encontrada'); }

$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf'] ?? null);
    $notes = trim($_POST['reviewer_notes'] ?? '');
    $action = $_POST['action'] ?? '';
    if ($action === 'approve') {
        $studyId = $svc->approve($id, $notes);
        header('Location: /admin/study-related.php?id=' . $studyId); exit;
    } elseif ($action === 'reject') {
        if ($notes === '') { $message = 'Indica motivo del rechazo.'; }
        else { $svc->reject($id, $notes); header('Location: /admin/submissions.php'); exit; }
    }
}

$freqs = json_decode($sub['frequencies_json'] ?? '[]', true) ?: [];
$outs  = json_decode($sub['outcomes_json']    ?? '[]', true) ?: [];
$risks = json_decode($sub['risks_json']       ?? '[]', true) ?: [];
$csrf = Security::csrfToken();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Revisar</title>
<style>table{border-collapse:collapse;margin:8px 0}td,th{border:1px solid #ccc;padding:4px 8px}</style></head>
<body style="font-family:system-ui;max-width:1000px;margin:2em auto;padding:0 1em">
<p><a href="/admin/submissions.php">← Pendientes</a></p>
<h1>Revisar propuesta #<?= (int)$sub['id'] ?></h1>
<?php if ($message): ?><p style="color:#c62828"><?= Security::e($message) ?></p><?php endif; ?>
<h2><?= Security::e($sub['title']) ?></h2>
<p><strong>Contribuyente:</strong> <?= Security::e($sub['submitter_name']) ?> &lt;<?= Security::e($sub['submitter_email']) ?>&gt;</p>
<p><strong>Autores:</strong> <?= Security::e($sub['authors']) ?> · <strong>DOI:</strong> <?= Security::e($sub['doi']) ?></p>
<p><strong>Resumen:</strong><br><?= nl2br(Security::e($sub['summary'])) ?></p>

<?php if ($freqs): ?><h3>Frecuencias</h3><table><tr><th>min</th><th>max</th><th>tipo</th><th>región</th><th>notas</th></tr>
<?php foreach ($freqs as $f): ?><tr><td><?= Security::e((string)($f['min'] ?? '')) ?></td><td><?= Security::e((string)($f['max'] ?? '')) ?></td><td><?= Security::e($f['type'] ?? '') ?></td><td><?= Security::e($f['region'] ?? '') ?></td><td><?= Security::e($f['notes'] ?? '') ?></td></tr><?php endforeach; ?></table><?php endif; ?>

<?php if ($outs): ?><h3>Outcomes</h3><table><tr><th>tipo</th><th>dir</th><th>tamaño</th><th>unidad</th><th>p</th></tr>
<?php foreach ($outs as $o): ?><tr><td><?= Security::e($o['type'] ?? '') ?></td><td><?= Security::e($o['direction'] ?? '') ?></td><td><?= Security::e((string)($o['size'] ?? '')) ?></td><td><?= Security::e($o['unit'] ?? '') ?></td><td><?= Security::e((string)($o['p_value'] ?? '')) ?></td></tr><?php endforeach; ?></table><?php endif; ?>

<?php if ($risks): ?><h3>Riesgos</h3><table><tr><th>rango</th><th>descripción</th><th>evidencia</th></tr>
<?php foreach ($risks as $r): ?><tr><td><?= Security::e($r['range'] ?? '') ?></td><td><?= Security::e($r['description'] ?? '') ?></td><td><?= Security::e($r['evidence'] ?? '') ?></td></tr><?php endforeach; ?></table><?php endif; ?>

<hr>
<form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>">
<p><label>Notas del curador (obligatorias al rechazar)<br><textarea name="reviewer_notes" rows="3" cols="80"><?= Security::e($sub['reviewer_notes'] ?? '') ?></textarea></label></p>
<button name="action" value="approve" style="color:#2e7d32">✓ Aprobar</button>
<button name="action" value="reject" style="color:#c62828" onclick="return confirm('¿Rechazar?')">✗ Rechazar</button>
</form>
</body></html>
