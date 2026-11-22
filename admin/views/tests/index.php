<header class="admin-page-head">
    <div>
        <h1>Тесты</h1>
        <p>Создание, публикация и переход к управлению вопросами, ответами и результатами.</p>
    </div>
    <a class="btn primary" href="/admin/tests/create">Новый тест</a>
</header>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
        <tr>
            <th>Тест</th>
            <th>Slug</th>
            <th>Статус</th>
            <th>Результаты</th>
            <th>Вопросы</th>
            <th>Прохождения</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tests as $test): ?>
            <tr>
                <td><?= e($test['title']) ?></td>
                <td><?= e($test['slug']) ?></td>
                <td><span class="badge <?= $test['status'] === 'published' ? 'published': 'hidden' ?>"><?= e($test['status']) ?></span></td>
                <td><?= e((string) $test['results_count']) ?></td>
                <td><?= e((string) $test['questions_count']) ?></td>
                <td><?= e((string) $test['attempts_count']) ?></td>
                <td class="actions-cell">
                    <a href="/admin/tests/edit/<?= e((string) $test['id']) ?>">Редактировать</a>
                    <a href="/admin/tests/<?= e((string) $test['id']) ?>/results">Результаты</a>
                    <a href="/admin/tests/<?= e((string) $test['id']) ?>/questions">Вопросы</a>
                    <form method="post" action="/admin/tests/delete/<?= e((string) $test['id']) ?>" onsubmit="return confirm('Удалить тест?')">
                        <?= csrf_field() ?>
                        <button class="link-button danger" type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>