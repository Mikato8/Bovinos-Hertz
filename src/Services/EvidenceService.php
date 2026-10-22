<?php
declare(strict_types=1);

final class EvidenceService
{
    public static function crossesAuditoryRiskZone(?float $minHz, ?float $maxHz): bool
    {
        if ($minHz === null && $maxHz === null) return false;
        $min = $minHz ?? 0.0;
        $max = $maxHz ?? PHP_FLOAT_MAX;
        return !($max < 6000 || $min > 8000);
    }

    public static function riskMessage(): string
    {
        return '⚠️ El rango consultado incluye 6.000–8.000 Hz, zona de máxima sensibilidad auditiva bovina. '
             . 'Ruidos en esta banda se asocian a estrés y reducción de ganancia de peso e inmunidad.';
    }
}
