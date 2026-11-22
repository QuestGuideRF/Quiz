<section class="section">
    <div class="container narrow">
        <div class="panel">
            <p class="eyebrow">Перед стартом</p>
            <h1><?= e($test['title']) ?></h1>
            <p class="lead"><?= e($test['description']) ?></p>
            <form class="stack-form" method="post" action="/test/<?= e($test['slug']) ?>/start">
                <?= csrf_field() ?>
                <label class="field">
                    <span>Как к вам обращаться?</span>
                    <input type="text" name="nickname" maxlength="32" minlength="2" required placeholder="Введите никнейм" value="<?= e((string) old('nickname')) ?>">
                </label>
                <button class="btn btn-primary btn-block" type="submit">Начать прохождение</button>
            </form>
        </div>
    </div>
</section>