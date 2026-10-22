<?php
declare(strict_types=1);
require_once __DIR__ . '/../../src/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$pdo = db();
$minHz    = isset($_GET['min']) && $_GET['min'] !== '' ? (float) $_GET['min'] : null;
$maxHz    = isset($_GET['max']) && $_GET['max'] !== '' ? (float) $_GET['max'] : null;
$context  = trim($_GET['context']  ?? '');
$species  = trim($_GET['species']  ?? '');
$evidence = trim($_GET['evidence'] ?? '');
$q        = trim($_GET['q']        ?? '');
$limit    = max(1, min(200, (int)($_GET['limit'] ?? 50)));
$offset   = max(0, (int)($_GET['offset'] ?? 0));

$where = []; $params = [];
if ($minHz !== null || $maxHz !== null) {
    $sub = [];
    if ($minHz !== null) { $sub[] = 'f.frequency_max_hz >= :min'; $params[':min'] = $minHz; }
    if ($maxHz !== null) { $sub[] = 'f.frequency_min_hz <= :max'; $params[':max'] = $maxHz; }
    $where[] = 'EXISTS (SELECT 1 FROM frequencies f WHERE f.study_id = s.id AND ' . implode(' AND ', $sub) . ')';
}
if ($context !== '')  { $where[] = 's.context = :context';         $params[':context']  = $context; }
if ($species !== '')  { $where[] = 's.species = :species';         $params[':species']  = $species; }
if ($evidence !== '') { $where[] = 's.evidence_level = :evidence'; $params[':evidence'] = $evidence; }
if ($q !== '')        { $where[] = '(s.title LIKE :q OR s.summary LIKE :q)'; $params[':q'] = "%$q%"; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM studies s $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$sql = "SELECT s.* FROM studies s $whereSql ORDER BY s.publication_year DESC, s.id DESC LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$studies = $stmt->fetchAll();

if ($studies) {
    $ids = array_column($studies, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $freqStmt = $pdo->prepare("SELECT * FROM frequencies WHERE study_id IN ($in) ORDER BY id"); $freqStmt->execute($ids);
    $fByS = []; foreach ($freqStmt->fetchAll() as $f) $fByS[$f['study_id']][] = $f;
    $outStmt = $pdo->prepare("SELECT * FROM outcomes WHERE study_id IN ($in) ORDER BY id"); $outStmt->execute($ids);
    $oByS = []; foreach ($outStmt->fetchAll() as $o) $oByS[$o['study_id']][] = $o;
    $riskStmt = $pdo->prepare("SELECT * FROM risk_flags WHERE study_id IN ($in) ORDER BY id"); $riskStmt->execute($ids);
    $rByS = []; foreach ($riskStmt->fetchAll() as $r) $rByS[$r['study_id']][] = $r;
    foreach ($studies as &$s) {
        $s['frequencies'] = $fByS[$s['id']] ?? [];
        $s['outcomes']    = $oByS[$s['id']] ?? [];
        $s['risk_flags']  = $rByS[$s['id']] ?? [];
    }
    unset($s);
}

$warning = null;
if (($minHz !== null || $maxHz !== null) && !(($maxHz ?? PHP_FLOAT_MAX) < 6000 || ($minHz ?? 0) > 8000)) {
    $warning = 'El rango consultado incluye 6.000–8.000 Hz, zona de máxima sensibilidad auditiva bovina.';
}

echo json_encode([
    'meta' => [
        'generated_at' => gmdate('c'),
        'license'      => 'CC-BY-4.0',
        'total'        => $total, 'limit' => $limit, 'offset' => $offset,
        'filters'      => ['min_hz'=>$minHz,'max_hz'=>$maxHz,'context'=>$context ?: null,'species'=>$species ?: null,'evidence'=>$evidence ?: null,'q'=>$q ?: null],
        'warning'      => $warning,
    ],
    'data' => $studies,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
