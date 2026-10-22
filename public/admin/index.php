<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/Repositories/StudyRepository.php';
Auth::requireLogin();

$studies = db()->query('SELECT id, title, context, evidence_level, publication_year FROM studies ORDER BY id DESC')->fetchAll();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Admin · Estudios</title>
<style>table{border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px}</style></head>
<body style="font-family:system-ui;max-width:1100px;margin:2em auto;padding:0 1em">
<nav><a href="/admin/index.php">Estudios</a> · <a href="/admin/study-edit.php">Nuevo</a> · <a href="/admin/submissions.php">Propuestas pendientes</a> · <a href="/export.php?format=csv">CSV</a> · <a href="/export.php?format=json">JSON</a> · <a href="/admin/logout.php">Salir</a></nav>
<h1>Estudios (<?= count($studies) ?>)</h1>
<table><tr><th>ID</th><th>Título</th><th>Contexto</th><th>Evidencia</th><th>Año</th><th>Acciones</th></tr>
<?php foreach ($studies as $s): ?><tr>
<td><?= (int)$s['id'] ?></td><td><?= Security::e($s['title']) ?></td>
<td><?= Security::e($s['context']) ?></td><td><?= Security::e($s['evidence_level']) ?></td>
<td><?= (int)$s['publication_year'] ?></td>
<td><a href="/admin/study-edit.php?id=<?= (int)$s['id'] ?>">Editar</a> ·
    <a href="/admin/study-related.php?id=<?= (int)$s['id'] ?>">Frecuencias/Outcomes/Riesgos</a> ·
    <a href="/admin/study-delete.php?id=<?= (int)$s['id'] ?>">Eliminar</a></td>
</tr><?php endforeach; ?></table>
</body></html>
