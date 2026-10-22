<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/Services/SubmissionService.php';
Auth::requireLogin();
$svc = new SubmissionService(db());
$pending = $svc->pending();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Propuestas</title>
<style>table{border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px}</style></head>
<body style="font-family:system-ui;max-width:1000px;margin:2em auto;padding:0 1em">
<p><a href="/admin/index.php">← Admin</a></p>
<h1>Propuestas pendientes (<?= count($pending) ?>)</h1>
<?php if (!$pending): ?><p>No hay propuestas pendientes.</p><?php else: ?>
<table><tr><th>ID</th><th>Recibida</th><th>Título</th><th>Contribuyente</th><th>Acciones</th></tr>
<?php foreach ($pending as $p): ?><tr>
<td><?= (int)$p['id'] ?></td><td><?= Security::e($p['created_at']) ?></td><td><?= Security::e($p['title']) ?></td>
<td><?= Security::e($p['submitter_name']) ?> &lt;<?= Security::e($p['submitter_email']) ?>&gt;</td>
<td><a href="/admin/submission-review.php?id=<?= (int)$p['id'] ?>">Revisar</a></td>
</tr><?php endforeach; ?></table>
<?php endif; ?>
</body></html>
