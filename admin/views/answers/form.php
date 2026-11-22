<header class="admin-page-head">
    <div>
        <h1><?= $answer ? 'Редактирование ответа' : 'Новый ответ' ?></h1>
        <p>Вопрос: <?= e($question['question_text']) ?></p>
    </div>
    <a class="btn secondary" href="/admin/questions/<?= e((string) $question['id']) ?>/answers">Назад</a>
</header>
<?php if (!$answer && $answersCount >= 4): ?>
    <div class="alert error">У этого вопроса уже есть 4 ответа. Можно редактировать существующие, но нельзя добавить пятый.</div>
<?php endif; ?>
<form class="admin-form wide" method="post" action="<?= $answer ? '/admin/answers/edit/' . e((string) $answer['id']) : '/admin/questions/' . e((string) $question['id']) . '/answers/create' ?>">
    <?= csrf_field() ?>
    <label class="field">
        <span>Текст ответа</span>
        <input type="text" name="answer_text" required value="<?= e((string) ($answer['answer_text'] ?? old('answer_text'))) ?>">
    </label>
    <div class="inline-fields">
        <label class="field">
            <span>Результат</span>
            <select name="result_id" required>
                <option value="">Выберите результат</option>
                <?php $selectedResultId = (int) ($answer['result_id'] ?? old('result_id', 0)); ?>
                <?php foreach ($results as $result): ?>
                    <option value="<?= e((string) $result['id']) ?>" <?= $selectedResultId === (int) $result['id'] ? 'selected' : '' ?>>
                        <?= e($result['title']) ?> (приоритет <?= e((string) $result['priority']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span>Порядок</span>
            <input type="number" name="sort_order" min="0" value="<?= e((string) ($answer['sort_order'] ?? old('sort_order', 0))) ?>">
        </label>
    </div>
    <button class="btn primary" type="submit"><?= $answer ? 'Сохранить изменения' : 'Создать ответ' ?></button>
</form>