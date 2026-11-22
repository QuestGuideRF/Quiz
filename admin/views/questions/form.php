<header class="admin-page-head">
    <div>
        <h1><?= $question ? 'Редактирование вопроса' : 'Новый вопрос' ?></h1>
        <p>Тест: <?= e($test['title']) ?></p>
    </div>
    <a class="btn secondary" href="/admin/tests/<?= e((string) $test['id']) ?>/questions">Назад</a>
</header>
<form class="admin-form wide" method="post" enctype="multipart/form-data" action="<?= $question ? '/admin/questions/edit/' . e((string) $question['id']) : '/admin/tests/' . e((string) $test['id']) . '/questions/create' ?>">
    <?= csrf_field() ?>
    <label class="field">
        <span>Текст вопроса</span>
        <textarea name="question_text" rows="5" required><?= e((string) ($question['question_text'] ?? old('question_text'))) ?></textarea>
    </label>
    <label class="field">
        <span>Изображение вопроса</span>
        <input type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.gif">
    </label>
    <?php if (!empty($question['image_path'])): ?>
        <div class="image-preview">
            <img src="<?= e($question['image_path']) ?>" alt="">
            <label><input type="checkbox" name="remove_image" value="1"> Удалить изображение</label>
        </div>
    <?php endif; ?>
    <label class="field">
        <span>Порядок</span>
        <input type="number" name="sort_order" min="0" value="<?= e((string) ($question['sort_order'] ?? old('sort_order', 0))) ?>">
    </label>
    <button class="btn primary" type="submit"><?= $question ? 'Сохранить изменения' : 'Создать вопрос' ?></button>
</form>