<header class="admin-page-head">
    <div>
        <h1><?= $result ? 'Редактирование результата' : 'Новый результат' ?></h1>
        <p>Тест: <?= e($test['title']) ?></p>
    </div>
    <a class="btn secondary" href="/admin/tests/<?= e((string) $test['id']) ?>/results">Назад</a>
</header>
<form class="admin-form wide" method="post" enctype="multipart/form-data" action="<?= $result ? '/admin/results/edit/' . e((string) $result['id']) : '/admin/tests/' . e((string) $test['id']) . '/results/create' ?>">
    <?= csrf_field() ?>
    <label class="field">
        <span>Название результата</span>
        <input type="text" name="title" required value="<?= e((string) ($result['title'] ?? old('title'))) ?>">
    </label>
    <label class="field">
        <span>Текст результата</span>
        <textarea name="description" rows="6" required><?= e((string) ($result['description'] ?? old('description'))) ?></textarea>
    </label>
    <label class="field">
        <span>Изображение</span>
        <input type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.gif">
    </label>
    <?php if (!empty($result['image_path'])): ?>
        <div class="image-preview">
            <img src="<?= e($result['image_path']) ?>" alt="">
            <label><input type="checkbox" name="remove_image" value="1"> Удалить изображение</label>
        </div>
    <?php endif; ?>
    <div class="inline-fields">
        <label class="field">
            <span>Приоритет</span>
            <input type="number" name="priority" min="0" value="<?= e((string) ($result['priority'] ?? old('priority', 0))) ?>">
        </label>
        <label class="field">
            <span>Порядок</span>
            <input type="number" name="sort_order" min="0" value="<?= e((string) ($result['sort_order'] ?? old('sort_order', 0))) ?>">
        </label>
    </div>
    <button class="btn primary" type="submit"><?= $result ? 'Сохранить изменения' : 'Создать результат' ?></button>
</form>