<header class="admin-page-head">
    <div>
        <h1>Ответы к вопросу</h1>
        <p><?= e($question['question_text']) ?></p>
    </div>
    <div class="button-row">
        <a class="btn secondary" href="/admin/tests/<?= e((string) $test['id']) ?>/questions">Назад к вопросам</a>
        <a class="btn primary" href="/admin/questions/<?= e((string) $question['id']) ?>/answers/create">Добавить ответ</a>
    </div>
</header>
<div class="admin-card subtle">
    <p><strong>Статус:</strong> <?= count($answers) ?>/4 ответа</p>
</div>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
        <tr>
            <th>Ответ</th>
            <th>Ведет к результату</th>
            <th>Порядок</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($answers as $answer): ?>
            <tr>
                <td><?= e($answer['answer_text']) ?></td>
                <td><?= e($answer['result_title']) ?></td>
                <td><?= e((string) $answer['sort_order']) ?></td>
                <td class="actions-cell">
                    <a href="/admin/answers/edit/<?= e((string) $answer['id']) ?>">Редактировать</a>
                    <form method="post" action="/admin/answers/delete/<?= e((string) $answer['id']) ?>" onsubmit="return confirm('Удалить ответ?')">
                        <?= csrf_field() ?>
                        <button class="link-button danger" type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<section class="admin-card">
    <div class="admin-card-head"><h2>Доступные результаты теста</h2></div>
    <ul class="plain-list">
        <?php foreach ($results as $result): ?>
            <li><span><?= e($result['title']) ?></span><strong>Приоритет <?= e((string) $result['priority']) ?></strong></li>
        <?php endforeach; ?>
    </ul>
</section>