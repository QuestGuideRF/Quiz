<header class="admin-page-head">
    <div>
        <h1>Вопросы: <?= e($test['title']) ?></h1>
        <p>Для каждого вопроса нужно ровно 4 ответа.</p>
    </div>
    <div class="button-row">
        <a class="btn secondary" href="/admin/tests">К тестам</a>
        <a class="btn primary" href="/admin/tests/<?= e((string) $test['id']) ?>/questions/create">Добавить вопрос</a>
    </div>
</header>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
        <tr>
            <th>Вопрос</th>
            <th>Порядок</th>
            <th>Ответов</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($questions as $question): ?>
            <tr>
                <td><?= e($question['question_text']) ?></td>
                <td><?= e((string) $question['sort_order']) ?></td>
                <td><span class="badge <?= (int) $question['answers_count'] === 4 ? 'published': 'hidden' ?>"><?= e((string) $question['answers_count']) ?>/4</span></td>
                <td class="actions-cell">
                    <a href="/admin/questions/edit/<?= e((string) $question['id']) ?>">Редактировать</a>
                    <a href="/admin/questions/<?= e((string) $question['id']) ?>/answers">Ответы</a>
                    <form method="post" action="/admin/questions/delete/<?= e((string) $question['id']) ?>" onsubmit="return confirm('Удалить вопрос?')">
                        <?= csrf_field() ?>
                        <button class="link-button danger" type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>