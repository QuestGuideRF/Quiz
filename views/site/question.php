<section class="section">
    <div class="container narrow">
        <div class="progress-card">
            <div class="progress-head">
                <div>
                    <p class="eyebrow">Прохождение теста</p>
                    <h1><?= e($test['title']) ?></h1>
                </div>
                <span class="progress-label">Вопрос <?= e((string) $step) ?> из <?= e((string) $totalQuestions) ?></span>
            </div>
            <div class="progress-bar">
                <span style="width: <?= e((string) $progress) ?>%"></span>
            </div>
        </div>
        <article class="panel question-panel">
            <?php if ($question['image_path']): ?>
                <div class="question-image">
                    <img src="<?= e($question['image_path']) ?>" alt="<?= e($question['question_text']) ?>">
                </div>
            <?php endif; ?>
            <h2><?= e($question['question_text']) ?></h2>
            <form method="post" action="/quiz/<?= e($test['slug']) ?>/question/<?= e((string) $step) ?>" class="answers-form">
                <?= csrf_field() ?>
                <?php foreach ($question['answers'] as $answer): ?>
                    <label class="answer-option">
                        <input type="radio" name="answer_id" value="<?= e((string) $answer['id']) ?>" required>
                        <span><?= e($answer['answer_text']) ?></span>
                    </label>
                <?php endforeach; ?>
                <button class="btn btn-primary btn-block" type="submit">
                    <?= $step === $totalQuestions ? 'Показать результат' : 'Следующий вопрос' ?>
                </button>
            </form>
        </article>
    </div>
</section>