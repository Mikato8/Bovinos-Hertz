<?php /** @var array $study */ ?>
<article class="study-card">
    <h3><a href="/study.php?id=<?= (int)$study['id'] ?>"><?= htmlspecialchars($study['title']) ?></a></h3>
    <p><?= htmlspecialchars((string)$study['summary']) ?></p>
    <small><?= htmlspecialchars((string)$study['context']) ?> · <?= htmlspecialchars((string)$study['evidence_level']) ?></small>
</article>
