<?php $selectedStatus = $test['status'] ?? old('status', 'hidden'); ?>
<header class="admin-page-head">
    <div>
        <h1><?= $test ? 'Редактирование теста' : 'Создание теста' ?></h1>
        <p>Настройте карточку, описание, SEO и публикацию.</p>
    </div>
    <a class="btn secondary" href="/admin/tests">Назад к списку</a>
</header>
<form class="admin-form wide" method="post" enctype="multipart/form-data" action="<?= $test ? '/admin/tests/edit/' . e((string) $test['id']) : '/admin/tests/create' ?>">
    <?= csrf_field() ?>
    <label class="field">
        <span>Название</span>
        <input type="text" name="title" required value="<?= e((string) ($test['title'] ?? old('title'))) ?>">
    </label>
    <label class="field">
        <span>Slug</span>
        <input type="text" name="slug" placeholder="avto-generator-esli-pusto" value="<?= e((string) ($test['slug'] ?? old('slug'))) ?>">
    </label>
    <label class="field">
        <span>Краткое описание</span>
        <textarea name="description" rows="4" required><?= e((string) ($test['description'] ?? old('description'))) ?></textarea>
    </label>
    <label class="field">
        <span>Обложка</span>
        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,.gif">
    </label>
    <?php if (!empty($test['cover_image'])): ?>
        <div class="image-preview">
            <img src="<?= e($test['cover_image']) ?>" alt="">
            <label><input type="checkbox" name="remove_cover" value="1"> Удалить текущую обложку</label>
        </div>
    <?php endif; ?>
    <div class="inline-fields">
        <label class="field">
            <span>Статус</span>
            <select name="status">
                <option value="published" <?= ($selectedStatus === 'published') ? 'selected' : '' ?>>Опубликован</option>
                <option value="hidden" <?= ($selectedStatus === 'hidden') ? 'selected' : '' ?>>Скрыт</option>
            </select>
        </label>
        <label class="field">
            <span>Сортировка</span>
            <input type="number" name="sort_order" min="0" value="<?= e((string) ($test['sort_order'] ?? old('sort_order', 0))) ?>">
        </label>
    </div>
    <label class="field">
        <span>SEO title</span>
        <input type="text" name="seo_title" value="<?= e((string) ($test['seo_title'] ?? old('seo_title'))) ?>">
    </label>
    <label class="field">
        <span>Meta description</span>
        <textarea name="seo_description" rows="3"><?= e((string) ($test['seo_description'] ?? old('seo_description'))) ?></textarea>
    </label>
    <div class="form-actions">
        <button class="btn primary" type="submit"><?= $test ? 'Сохранить изменения' : 'Создать тест' ?></button>
        <?php if ($test): ?>
            <a class="btn secondary" href="/admin/tests/<?= e((string) $test['id']) ?>/results">Результаты</a>
            <a class="btn secondary" href="/admin/tests/<?= e((string) $test['id']) ?>/questions">Вопросы</a>
        <?php endif; ?>
    </div>
</form>