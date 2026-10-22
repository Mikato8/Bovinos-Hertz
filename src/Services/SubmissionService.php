<?php
declare(strict_types=1);

final class SubmissionService
{
    public function __construct(private PDO $pdo) {}

    public function store(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO submissions
             (submitter_name, submitter_email, submitter_affiliation, title, authors,
              publication_year, doi, url, source_type, species, context, summary,
              evidence_level, frequencies_json, outcomes_json, risks_json)
             VALUES (:name, :email, :aff, :title, :authors, :year, :doi, :url,
                     :stype, :species, :context, :summary, :evidence,
                     :freq, :outc, :risk)'
        );
        $stmt->execute([
            ':name'    => $data['submitter_name'],
            ':email'   => $data['submitter_email'],
            ':aff'     => $data['submitter_affiliation'] ?: null,
            ':title'   => $data['title'],
            ':authors' => $data['authors'] ?: null,
            ':year'    => $data['publication_year'] ?: null,
            ':doi'     => $data['doi'] ?: null,
            ':url'     => $data['url'] ?: null,
            ':stype'   => $data['source_type'],
            ':species' => $data['species'],
            ':context' => $data['context'],
            ':summary' => $data['summary'] ?: null,
            ':evidence'=> $data['evidence_level'],
            ':freq'    => json_encode($data['frequencies'] ?? [], JSON_UNESCAPED_UNICODE),
            ':outc'    => json_encode($data['outcomes']    ?? [], JSON_UNESCAPED_UNICODE),
            ':risk'    => json_encode($data['risks']       ?? [], JSON_UNESCAPED_UNICODE),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function pending(): array
    {
        return $this->pdo->query("SELECT * FROM submissions WHERE status='pending' ORDER BY created_at ASC")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $s = $this->pdo->prepare('SELECT * FROM submissions WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public function approve(int $id, string $reviewerNotes = ''): int
    {
        $sub = $this->find($id);
        if (!$sub || $sub['status'] !== 'pending') {
            throw new RuntimeException('Submission no encontrada o ya revisada');
        }
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO studies (title, authors, publication_year, doi, url, source_type,
                 species, context, summary, evidence_level)
                 VALUES (:title, :authors, :year, :doi, :url, :stype, :species, :context, :summary, :evidence)'
            );
            $stmt->execute([
                ':title'   => $sub['title'], ':authors' => $sub['authors'],
                ':year'    => $sub['publication_year'], ':doi' => $sub['doi'],
                ':url'     => $sub['url'], ':stype' => $sub['source_type'],
                ':species' => $sub['species'], ':context' => $sub['context'],
                ':summary' => $sub['summary'], ':evidence'=> $sub['evidence_level'],
            ]);
            $studyId = (int)$this->pdo->lastInsertId();

            foreach (json_decode($sub['frequencies_json'] ?? '[]', true) ?: [] as $f) {
                $this->pdo->prepare(
                    'INSERT INTO frequencies (study_id, frequency_min_hz, frequency_max_hz, frequency_type,
                     waveform, exposure_duration, body_region, notes)
                     VALUES (:sid, :min, :max, :type, :wf, :dur, :region, :notes)'
                )->execute([
                    ':sid' => $studyId, ':min' => (float)($f['min'] ?? 0),
                    ':max' => (float)($f['max'] ?? 0), ':type'=> $f['type'] ?? 'mechanical_vibration',
                    ':wf' => $f['waveform'] ?? null, ':dur' => $f['duration'] ?? null,
                    ':region' => $f['region'] ?? null, ':notes' => $f['notes'] ?? null,
                ]);
            }

            foreach (json_decode($sub['outcomes_json'] ?? '[]', true) ?: [] as $o) {
                $this->pdo->prepare(
                    'INSERT INTO outcomes (study_id, outcome_type, effect_direction, effect_size, effect_unit, p_value, notes)
                     VALUES (:sid, :type, :dir, :size, :unit, :p, :notes)'
                )->execute([
                    ':sid' => $studyId, ':type'=> $o['type'] ?? 'other',
                    ':dir' => $o['direction'] ?? 'neutral',
                    ':size'=> isset($o['size']) && $o['size'] !== '' ? (float)$o['size'] : null,
                    ':unit'=> $o['unit'] ?? null,
                    ':p'   => isset($o['p_value']) && $o['p_value'] !== '' ? (float)$o['p_value'] : null,
                    ':notes' => $o['notes'] ?? null,
                ]);
            }

            foreach (json_decode($sub['risks_json'] ?? '[]', true) ?: [] as $r) {
                $this->pdo->prepare(
                    'INSERT INTO risk_flags (study_id, frequency_range_hz, risk_description, evidence_level)
                     VALUES (:sid, :range, :desc, :ev)'
                )->execute([
                    ':sid' => $studyId, ':range' => (string)($r['range'] ?? ''),
                    ':desc' => (string)($r['description'] ?? ''),
                    ':ev' => $r['evidence'] ?? 'preliminary',
                ]);
            }

            $this->pdo->prepare(
                "UPDATE submissions SET status='approved', reviewer_notes=:n, reviewed_at=CURRENT_TIMESTAMP WHERE id=:id"
            )->execute([':n' => $reviewerNotes ?: null, ':id' => $id]);

            $this->pdo->commit();
            return $studyId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function reject(int $id, string $reviewerNotes): void
    {
        $this->pdo->prepare(
            "UPDATE submissions SET status='rejected', reviewer_notes=:n, reviewed_at=CURRENT_TIMESTAMP WHERE id=:id AND status='pending'"
        )->execute([':n' => $reviewerNotes, ':id' => $id]);
    }
}
