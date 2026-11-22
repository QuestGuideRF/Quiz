<?php
$questionCount = count($test['questions']);
$resultCount = count($test['results']);
$estimatedMinutes = max(3, (int) ceil($questionCount * 0.6));
?>
<section class="section test-page">
    <div class="container test-landing-shell test-landing-shell-single">
        <article class="detail-card test-detail-card">
            <div class="detail-cover test-detail-cover <?= $test['cover_image'] ? '' : 'card-cover-empty' ?>">
                <?php if ($test['cover_image']): ?>
                    <img src="<?= e($test['cover_image']) ?>" alt="<?= e($test['title']) ?>">
                <?php else: ?>
                    <span><?= e(mb_substr($test['title'], 0, 1, 'UTF-8')) ?></span>
                <?php endif; ?>
            </div>
            <div class="detail-content test-detail-content">
                <h1><?= e($test['title']) ?></h1>
                <p class="lead"><?= e($test['description']) ?></p>
                <div class="detail-meta test-detail-meta">
                    <div>
                        <strong><?= e((string) $questionCount) ?></strong>
                        <span>вопросов в прохождении</span>
                    </div>
                    <div>
                        <strong><?= e((string) $resultCount) ?></strong>
                        <span>варианта финального результата</span>
                    </div>
                    <div>
                        <strong>~<?= e((string) $estimatedMinutes) ?> мин</strong>
                        <span>спокойный темп</span>
                    </div>
                </div>
                <div class="test-copy-grid">
                    <div class="test-copy-card">
                        <h2>Как это работает</h2>
                        <p>Перед стартом вы вводите никнейм, затем проходите вопросы по одному и в конце получаете наиболее подходящий результат по выбранным ответам.</p>
                    </div>
                    <div class="test-copy-card">
                        <h2>Что внутри</h2>
                        <ul class="test-feature-list">
                            <li>Ровно 4 варианта ответа в каждом вопросе</li>
                            <li>Понятный пошаговый формат с прогрессом</li>
                            <li>Результат определяется по вашим выборам, а не случайно</li>
                        </ul>
                    </div>
                </div>
                <div class="actions test-detail-actions">
                    <a class="btn btn-primary" href="/test/<?= e($test['slug']) ?>/start">Начать тест</a>
                    <a class="btn btn-secondary" href="/">На главную</a>
                </div>
            </div>
        </article>
    </div>
</section>