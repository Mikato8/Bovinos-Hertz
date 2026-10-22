<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class StudyRepository
{
    public function __construct(private PDO $pdo) {}

    public function search(?float $minHz, ?float $maxHz, ?string $context, ?string $evidence): array
    {
        $sql = 'SELECT DISTINCT s.* FROM studies s';
        $params = [];

        if ($minHz !== null || $maxHz !== null) {
            $sql .= ' INNER JOIN frequencies f ON f.study_id = s.id';
            if ($minHz !== null && $maxHz !== null) {
                $sql .= ' WHERE f.frequency_max_hz >= :min AND f.frequency_min_hz <= :max';
                $params[':min'] = $minHz; $params[':max'] = $maxHz;
            } elseif ($minHz !== null) {
                $sql .= ' WHERE f.frequency_max_hz >= :min';
                $params[':min'] = $minHz;
            } else {
                $sql .= ' WHERE f.frequency_min_hz <= :max';
                $params[':max'] = $maxHz;
            }
        } else {
            $sql .= ' WHERE 1=1';
        }

        if ($context !== null && $context !== '') {
            $sql .= ' AND s.context = :context';
            $params[':context'] = $context;
        }
        if ($evidence !== null && $evidence !== '') {
            $sql .= ' AND s.evidence_level = :evidence';
            $params[':evidence'] = $evidence;
        }

        $sql .= ' ORDER BY s.publication_year DESC, s.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM studies WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function frequenciesOf(int $studyId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM frequencies WHERE study_id = :id');
        $stmt->execute([':id' => $studyId]);
        return $stmt->fetchAll();
    }

    public function outcomesOf(int $studyId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM outcomes WHERE study_id = :id');
        $stmt->execute([':id' => $studyId]);
        return $stmt->fetchAll();
    }

    public function risksOf(int $studyId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM risk_flags WHERE study_id = :id');
        $stmt->execute([':id' => $studyId]);
        return $stmt->fetchAll();
    }

    public function allForMap(): array
    {
        $sql = 'SELECT s.id, s.title, f.frequency_min_hz, f.frequency_max_hz,
                       o.effect_direction, o.outcome_type
                FROM studies s
                JOIN frequencies f ON f.study_id = s.id
                LEFT JOIN outcomes o ON o.study_id = s.id';
        return $this->pdo->query($sql)->fetchAll();
    }
}
