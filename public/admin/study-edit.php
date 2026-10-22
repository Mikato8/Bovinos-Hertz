<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
Auth::requireLogin();

$pdo = db();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$study = ['title'=>'','authors'=>'','publication_year'=>'','doi'=>'','url'=>'','source_type'=>'paper','species'=>'dairy_cows','context'=>'other','summary'=>'','evidence_level'=>'preliminary'];
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM studies WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $study = $stmt->fetch() ?: $study;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::verifyCsrf($_POST['csrf'] ?? null);
    $data = [
        ':title' => trim($_POST['title'] ?? ''),
        ':authors' => trim($_POST['authors'] ?? ''),
        ':publication_year' => ($_POST['publication_year'] ?? '') !== '' ? (int)$_POST['publication_year'] : null,
        ':doi' => trim($_POST['doi'] ?? ''),
        ':url' => trim($_POST['url'] ?? ''),
        ':source_type' => $_POST['source_type'] ?? 'paper',
        ':species' => $_POST['species'] ?? 'dairy_cows',
        ':context' => $_POST['context'] ?? 'other',
        ':summary' => trim($_POST['summary'] ?? ''),
        ':evidence_level' => $_POST['evidence_level'] ?? 'preliminary',
    ];
    if ($data[':title'] === '') {
        $error = 'Título obligatorio';
    } else {
        if ($id > 0) {
            $data[':id'] = $id;
            $pdo->prepare('UPDATE studies SET title=:title, authors=:authors, publication_year=:publication_year, doi=:doi, url=:url, source_type=:source_type, species=:species, context=:context, summary=:summary, evidence_level=:evidence_level WHERE id=:id')->execute($data);
        } else {
            $pdo->prepare('INSERT INTO studies (title, authors, publication_year, doi, url, source_type, species, context, summary, evidence_level) VALUES (:title, :authors, :publication_year, :doi, :url, :source_type, :species, :context, :summary, :evidence_level)')->execute($data);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: /admin/study-related.php?id=' . $id); exit;
    }
    $study = array_merge($study, $_POST);
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Estudio</title></head>
<body style="font-family:system-ui;max-width:900px;margin:2em auto;padding:0 1em">
<p><a href="/admin/index.php">← Volver</a></p>
<h1><?= $id > 0 ? 'Editar' : 'Nuevo' ?> estudio</h1>
<?php if (!empty($error)): ?><p style="color:#c62828"><?= Security::e($error) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf" value="<?= Security::e(Security::csrfToken()) ?>">
<p><label>Título *<br><input name="title" size="80" required value="<?= Security::e($study['title']) ?>"></label></p>
<p><label>Autores<br><input name="authors" size="80" value="<?= Security::e($study['authors']) ?>"></label></p>
<p><label>Año <input type="number" name="publication_year" value="<?= Security::e((string)$study['publication_year']) ?>"></label>
   <label>DOI <input name="doi" value="<?= Security::e($study['doi']) ?>"></label></p>
<p><label>URL <input name="url" size="60" value="<?= Security::e($study['url']) ?>"></label></p>
<p>Tipo <select name="source_type"><?php foreach (['paper','patent','review','thesis','report'] as $t): ?><option <?= $study['source_type']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select>
Especie <select name="species"><?php foreach (['dairy_cows','beef_cattle','calves','mixed'] as $t): ?><option <?= $study['species']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select>
Contexto <select name="context"><?php foreach (['milking','feeding','transport','growth','reproduction','housing','other'] as $t): ?><option <?= $study['context']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select>
Evidencia <select name="evidence_level"><?php foreach (['high','medium','low','preliminary'] as $t): ?><option <?= $study['evidence_level']===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select></p>
<p><label>Resumen<br><textarea name="summary" rows="5" cols="80"><?= Security::e($study['summary']) ?></textarea></label></p>
<button>Guardar</button>
</form>
</body></html>
