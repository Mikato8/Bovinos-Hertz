<?php /** @var array $results */ ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Hertz Bovinos — Evidencia científica</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body{font-family:system-ui,sans-serif;max-width:1000px;margin:2em auto;padding:0 1em;line-height:1.6}
        .study-card{border:1px solid #ddd;padding:1em;margin:1em 0;border-radius:6px}
        .alert-risk{background:#fff3e0;border-left:4px solid #e65100;padding:1em;margin:1em 0}
        form label{display:inline-block;margin-right:1em}
    </style>
</head>
<body>
<h1>Hertz en bovinos — Repositorio de evidencia</h1>
<p>
    <a href="/index.php">Inicio</a> ·
    <a href="/evidence-map.php">Mapa de evidencia</a> ·
    <a href="/contribute.php">Contribuir estudio</a> ·
    <a href="/api/">API</a> ·
    <a href="/export.php?format=csv">CSV</a> ·
    <a href="/export.php?format=json">JSON</a>
</p>

<form method="get" action="/index.php">
    <label>Frecuencia mín. (Hz) <input type="number" step="0.001" name="min" value="<?= htmlspecialchars((string)($_GET['min'] ?? '')) ?>"></label>
    <label>Frecuencia máx. (Hz) <input type="number" step="0.001" name="max" value="<?= htmlspecialchars((string)($_GET['max'] ?? '')) ?>"></label>
    <label>Contexto
        <select name="context">
            <option value="">Todos</option>
            <?php foreach (['milking','feeding','transport','growth','reproduction','housing','other'] as $c): ?>
                <option value="<?= $c ?>" <?= (($context ?? '') === $c ? 'selected' : '') ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Nivel de evidencia
        <select name="evidence">
            <option value="">Todos</option>
            <?php foreach (['high','medium','low','preliminary'] as $e): ?>
                <option value="<?= $e ?>" <?= (($evidence ?? '') === $e ? 'selected' : '') ?>><?= $e ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Buscar</button>
</form>

<?php if (!empty($risk)): ?>
    <div class="alert-risk"><?= EvidenceService::riskMessage() ?></div>
<?php endif; ?>

<h2>Resultados (<?= count($results) ?>)</h2>
<?php foreach ($results as $study): include __DIR__ . '/partials/study_card.php'; endforeach; ?>

<hr>
<p><small>Dataset bajo CC-BY-4.0. Repositorio informativo; no constituye recomendación veterinaria.</small></p>
</body>
</html>
