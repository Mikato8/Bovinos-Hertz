<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf'] ?? null);
    db()->prepare('DELETE FROM studies WHERE id = :id')->execute([':id' => (int)($_POST['id'] ?? 0)]);
    header('Location: /admin/index.php'); exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare('SELECT title FROM studies WHERE id = :id');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();
if (!$s) { http_response_code(404); exit('No encontrado'); }
$csrf = Security::csrfToken();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Eliminar</title></head>
<body style="font-family:system-ui;max-width:700px;margin:3em auto">
<h1>Confirmar eliminación</h1>
<p>Vas a eliminar: <strong><?= Security::e($s['title']) ?></strong> y todos sus registros asociados.</p>
<form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= $id ?>">
<button style="color:#c62828">Sí, eliminar definitivamente</button> <a href="/admin/index.php">Cancelar</a></form>
</body></html>
