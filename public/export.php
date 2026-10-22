<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

$format = $_GET['format'] ?? 'json';
if (!in_array($format, ['json', 'csv'], true)) { http_response_code(400); exit('Usa ?format=json o ?format=csv'); }

$pdo = db();
$sql = 'SELECT s.id AS study_id, s.title, s.authors, s.publication_year, s.doi, s.url,
               s.source_type, s.species, s.context, s.summary, s.evidence_level,
               f.frequency_min_hz, f.frequency_max_hz, f.frequency_type, f.waveform,
               f.exposure_duration, f.body_region AS frequency_body_region,
               o.outcome_type, o.effect_direction, o.effect_size, o.effect_unit, o.p_value,
               r.frequency_range_hz AS risk_range, r.risk_description, r.evidence_level AS risk_evidence
        FROM studies s
        LEFT JOIN frequencies f ON f.study_id = s.id
        LEFT JOIN outcomes o ON o.study_id = s.id
        LEFT JOIN risk_flags r ON r.study_id = s.id
        ORDER BY s.id, f.id, o.id, r.id';
$rows = $pdo->query($sql)->fetchAll();
$date = gmdate('Ymd');

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"hertz-bovinos-$date.csv\"");
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $r) fputcsv($out, $r); }
    fclose($out);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'generated_at' => gmdate('c'), 'source' => $_SERVER['HTTP_HOST'] ?? 'hertz-bovinos',
    'license' => 'CC-BY-4.0', 'count' => count($rows), 'records' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
