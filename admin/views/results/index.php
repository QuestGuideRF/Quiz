<header class="admin-page-head">
    <div>
        <h1>Результаты: <?= e($test['title']) ?></h1>
        <p>При равенстве голосов победит результат с более высоким приоритетом.</p>
    </div>
    <div class="button-row">
        <a class="btn secondary" href="/admin/tests">К тестам</a>
        <a class="btn primary" href="/admin/tests/<?= e((string) $test['id']) ?>/results/create">Добавить результат</a>
    </div>
</header>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
        <tr>
            <th>Название</th>
            <th>Приоритет</th>
            <th>Порядок</th>
            <th>Связанных ответов</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $result): ?>
            <tr>
                <td><?= e($result['title']) ?></td>
                <td><?= e((string) $result['priority']) ?></td>
                <td><?= e((string) $result['sort_order']) ?></td>
                <td><?= e((string) $result['linked_answers_count']) ?></td>
                <td class="actions-cell">
                    <a href="/admin/results/edit/<?= e((string) $result['id']) ?>">Редактировать</a>
                    <form method="post" action="/admin/results/delete/<?= e((string) $result['id']) ?>" onsubmit="return confirm('Удалить результат?')">
                        <?= csrf_field() ?>
                        <button class="link-button danger" type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>