<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Repositories/StudyRepository.php';
require_once __DIR__ . '/../src/Services/EvidenceService.php';

$repo = new StudyRepository(db());
$minHz    = isset($_GET['min']) && $_GET['min'] !== '' ? (float) $_GET['min'] : null;
$maxHz    = isset($_GET['max']) && $_GET['max'] !== '' ? (float) $_GET['max'] : null;
$context  = $_GET['context'] ?? null;
$evidence = $_GET['evidence'] ?? null;

$results = $repo->search($minHz, $maxHz, $context, $evidence);
$risk    = EvidenceService::crossesAuditoryRiskZone($minHz, $maxHz);

require __DIR__ . '/../templates/layout.php';
