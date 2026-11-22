<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\SiteRepository;
final class SiteController
{
    public function __construct(
        private readonly SiteRepository $repository,
        private readonly array $config
    ) {
    }
    public function home(): void
    {
        render('views/site/home.php', [
            'config' => $this->config,
            'tests' => $this->repository->getPublishedTests(),
            'pageTitle' => config($this->config, 'app.name'),
            'metaDescription' => config($this->config, 'app.default_meta_description'),
            'canonical' => base_url($this->config),
        ]);
    }
    public function showTest(string $slug): void
    {
        $test = $this->repository->findPublishedTestBySlug($slug);
        if (!$test) {
            $this->error(404);
            return;
        }
        render('views/site/test.php', [
            'config' => $this->config,
            'test' => $test,
            'pageTitle' => $test['seo_title'] ?: $test['title'],
            'metaDescription' => $test['seo_description'] ?: $test['description'],
            'canonical' => base_url($this->config, 'test/' . $test['slug']),
        ]);
    }
    public function showStart(string $slug): void
    {
        $test = $this->repository->findPublishedTestBySlug($slug);
        if (!$test) {
            $this->error(404);
            return;
        }
        render('views/site/start.php', [
            'config' => $this->config,
            'test' => $test,
            'pageTitle' => 'Начать тест: ' . $test['title'],
            'metaDescription' => $test['description'],
            'canonical' => base_url($this->config, 'test/' . $test['slug'] . '/start'),
        ]);
    }
    public function startAttempt(string $slug): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            $this->error(403, 'Неверный CSRF-токен.');
            return;
        }
        $test = $this->repository->findPublishedTestBySlug($slug);
        if (!$test) {
            $this->error(404);
            return;
        }
        if (!$this->repository->isTestReady((int) $test['id'])) {
            $this->error(500, 'Этот тест пока не готов к прохождению.');
            return;
        }
        $nickname = trim((string) ($_POST['nickname'] ?? ''));
        if ($nickname === '' || mb_strlen($nickname, 'UTF-8') < 2 || mb_strlen($nickname, 'UTF-8') > 32) {
            flash('error', 'Укажите никнейм длиной от 2 до 32 символов.');
            flash('old.nickname', $nickname);
            redirect('/test/' . $slug . '/start');
        }
        $attempt = $this->repository->createAttempt(
            (int) $test['id'],
            $nickname,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );
        $active = Session::get('quiz_active', []);
        $active[$slug] = $attempt['token'];
        Session::put('quiz_active', $active);
        redirect('/quiz/' . $slug . '/question/1');
    }
    public function showQuestion(string $slug, int $step): void
    {
        $test = $this->repository->findPublishedTestBySlug($slug);
        if (!$test) {
            $this->error(404);
            return;
        }
        $attempt = $this->getActiveAttempt($slug, $test['id']);
        if (!$attempt) {
            flash('error', 'Сначала начните прохождение теста.');
            redirect('/test/' . $slug . '/start');
        }
        if ($attempt['status'] === 'completed') {
            redirect('/quiz/' . $slug . '/result');
        }
        $totalQuestions = $this->repository->countQuestions((int) $test['id']);
        if ($totalQuestions === 0) {
            $this->error(500, 'У этого теста пока нет вопросов.');
            return;
        }
        if ($step !== (int) $attempt['current_step']) {
            redirect('/quiz/' . $slug . '/question/' . (int) $attempt['current_step']);
        }
        $question = $this->repository->findQuestionByPosition((int) $test['id'], $step);
        if (!$question || count($question['answers']) !== 4) {
            $this->error(500, 'Структура теста повреждена: вопрос не найден или содержит не 4 ответа.');
            return;
        }
        render('views/site/question.php', [
            'config' => $this->config,
            'test' => $test,
            'attempt' => $attempt,
            'question' => $question,
            'step' => $step,
            'totalQuestions' => $totalQuestions,
            'progress' => (int) round(($step / max($totalQuestions, 1)) * 100),
            'pageTitle' => $test['title'] . ' — вопрос ' . $step,
            'metaDescription' => $test['description'],
            'canonical' => base_url($this->config, 'quiz/' . $slug . '/question/' . $step),
        ]);
    }
    public function submitQuestion(string $slug, int $step): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            $this->error(403, 'Неверный CSRF-токен.');
            return;
        }
        $test = $this->repository->findPublishedTestBySlug($slug);
        if (!$test) {
            $this->error(404);
            return;
        }
        $attempt = $this->getActiveAttempt($slug, $test['id']);
        if (!$attempt) {
            flash('error', 'Сессия прохождения истекла. Начните тест заново.');
            redirect('/test/' . $slug . '/start');
        }
        if ($step !== (int) $attempt['current_step']) {
            redirect('/quiz/' . $slug . '/question/' . (int) $attempt['current_step']);
        }
        $question = $this->repository->findQuestionByPosition((int) $test['id'], $step);
        if (!$question) {
            $this->error(404);
            return;
        }
        $answerId = (int) ($_POST['answer_id'] ?? 0);
        $answer = $this->repository->findAnswerForQuestion($answerId, (int) $question['id']);
        if (!$answer) {
            flash('error', 'Выберите один вариант ответа.');
            redirect('/quiz/' . $slug . '/question/' . $step);
        }
        $this->repository->saveAttemptAnswer((int) $attempt['id'], (int) $question['id'], (int) $answer['id'], (int) $answer['result_id']);
        $totalQuestions = $this->repository->countQuestions((int) $test['id']);
        if ($step >= $totalQuestions) {
            $resultId = $this->repository->getWinningResultId((int) $attempt['id'], (int) $test['id']);
            $this->repository->finishAttempt((int) $attempt['id'], $resultId);
            $active = Session::get('quiz_active', []);
            unset($active[$slug]);
            Session::put('quiz_active', $active);
            $completed = Session::get('quiz_completed', []);
            $completed[$slug] = $attempt['token'];
            Session::put('quiz_completed', $completed);
            redirect('/quiz/' . $slug . '/result');
        }
        $this->repository->updateAttemptStep((int) $attempt['id'], $step + 1);
        redirect('/quiz/' . $slug . '/question/' . ($step + 1));
    }
    public function showResult(string $slug): void
    {
        $completed = Session::get('quiz_completed', []);
        $token = $completed[$slug] ?? null;
        if (!is_string($token) || $token === '') {
            flash('error', 'Результат недоступен. Сначала пройдите тест.');
            redirect('/test/' . $slug);
        }
        $attempt = $this->repository->findAttemptByToken($token);
        if (!$attempt || $attempt['slug'] !== $slug || $attempt['status'] !== 'completed') {
            $this->error(403, 'Результат прохождения недоступен.');
            return;
        }
        render('views/site/result.php', [
            'config' => $this->config,
            'attempt' => $attempt,
            'pageTitle' => 'Ваш результат — ' . ($attempt['result_title'] ?? 'Тест'),
            'metaDescription' => $attempt['result_description'] ?: config($this->config, 'app.default_meta_description'),
            'canonical' => base_url($this->config, 'quiz/' . $slug . '/result'),
        ]);
    }
    public function error(int $code, ?string $customDescription = null): void
    {
        http_response_code($code);
        $map = [
            403 => ['title' => 'Доступ запрещен', 'description' => 'Похоже, у этой страницы ограничен доступ.'],
            404 => ['title' => 'Страница не найдена', 'description' => 'Такой страницы не существует или она была перемещена.'],
            500 => ['title' => 'Внутренняя ошибка', 'description' => 'Что-то пошло не так. Попробуйте обновить страницу чуть позже.'],
        ];
        $payload = $map[$code] ?? $map[500];
        if ($customDescription) {
            $payload['description'] = $customDescription;
        }
        render('views/errors/error.php', [
            'config' => $this->config,
            'code' => $code,
            'title' => $payload['title'],
            'description' => $payload['description'],
            'pageTitle' => $payload['title'],
            'metaDescription' => $payload['description'],
            'canonical' => current_full_url(),
        ]);
    }
    private function getActiveAttempt(string $slug, int $testId): ?array
    {
        $active = Session::get('quiz_active', []);
        $token = $active[$slug] ?? null;
        if (!is_string($token) || $token === '') {
            return null;
        }
        $attempt = $this->repository->findAttemptByToken($token);
        if (!$attempt || (int) $attempt['test_id'] !== $testId) {
            return null;
        }
        return $attempt;
    }
}