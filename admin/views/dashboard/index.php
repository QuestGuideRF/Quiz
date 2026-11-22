<header class="admin-page-head">
    <div>
        <h1>Панель управления</h1>
        <p>Сводка по тестам, прохождениям и последней активности.</p>
    </div>
    <a class="btn primary" href="/admin/tests/create">Создать тест</a>
</header>
<?php if ($routeError): ?>
    <div class="alert error"><?= e($routeError) ?></div>
<?php endif; ?>
<section class="stats-grid">
    <article class="stat-card">
        <span>Всего прохождений</span>
        <strong><?= e((string) $stats['total_attempts']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Опубликованных тестов</span>
        <strong><?= e((string) $stats['published_tests']) ?></strong>
    </article>
    <article class="stat-card">
        <span>Всего тестов</span>
        <strong><?= e((string) $stats['all_tests']) ?></strong>
    </article>
</section>
<section class="admin-card">
    <div class="admin-card-head">
        <h2>Популярные тесты</h2>
    </div>
    <div class="chart-bars">
        <?php foreach ($stats['popular_tests'] as $row): ?>
            <div class="bar-row">
                <span><?= e($row['title']) ?></span>
                <div class="bar-track"><span style="width: <?= max(8, (int) $row['total_attempts'] * 18) ?>px"></span></div>
                <strong><?= e((string) $row['total_attempts']) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<section class="admin-card">
    <div class="admin-card-head">
        <h2>Последние прохождения</h2>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Никнейм</th>
                <th>Тест</th>
                <th>Результат</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stats['recent_attempts'] as $attempt): ?>
                <tr>
                    <td><?= e(format_datetime($attempt['completed_at'])) ?></td>
                    <td><?= e($attempt['nickname']) ?></td>
                    <td><?= e($attempt['test_title']) ?></td>
                    <td><?= e($attempt['result_title'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<section class="admin-card">
    <div class="admin-card-head">
        <h2>Частые результаты по тестам</h2>
    </div>
    <div class="result-groups">
        <?php foreach ($stats['results_by_test'] as $group): ?>
            <article class="subcard">
                <h3><?= e($group['test_title']) ?></h3>
                <ul class="plain-list">
                    <?php foreach ($group['rows'] as $row): ?>
                        <li><span><?= e($row['title']) ?></span><strong><?= e((string) $row['total']) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</section>