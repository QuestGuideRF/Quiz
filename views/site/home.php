<section class="hero">
    <div class="container hero-grid hero-grid-single">
        <div>
            <p class="eyebrow">Мобильные квизы</p>
            <h1>Выберите тест по настроению и получите результат за пару минут</h1>
            <p class="hero-text">Легкие развлекательные тесты с аккуратным интерфейсом, крупными кнопками и комфортным прохождением на телефоне.</p>
        </div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Все тесты</h2>
            <p>Выберите карточку и начните прохождение.</p>
        </div>
        <div class="cards-grid">
            <?php foreach ($tests as $test): ?>
                <article class="test-card">
                    <a class="card-cover <?= $test['cover_image'] ? '' : 'card-cover-empty' ?>" href="/test/<?= e($test['slug']) ?>">
                        <?php if ($test['cover_image']): ?>
                            <img src="<?= e($test['cover_image']) ?>" alt="<?= e($test['title']) ?>">
                        <?php else: ?>
                            <span><?= e(mb_substr($test['title'], 0, 1, 'UTF-8')) ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="card-body">
                        <h3><a href="/test/<?= e($test['slug']) ?>"><?= e($test['title']) ?></a></h3>
                        <p><?= e($test['description']) ?></p>
                        <a class="btn btn-primary" href="/test/<?= e($test['slug']) ?>">Пройти тест</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>