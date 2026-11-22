<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\AdminRepository;
use App\Repositories\SiteRepository;
use App\Services\FileUploader;
use App\Services\SitemapService;
final class AdminController
{
    private Auth $auth;
    public function __construct(
        private readonly AdminRepository $admins,
        private readonly SiteRepository $siteRepository,
        private readonly FileUploader $uploader,
        private readonly SitemapService $sitemap,
        private readonly array $config
    ) {
        $this->auth = new Auth($this->admins);
    }
    public function dispatch(string $route): void
    {
        $route = trim($route, '/');
        if ($route === 'login') {
            method_is('POST') ? $this->login() : $this->loginForm();
            return;
        }
        if ($route === 'setup') {
            method_is('POST') ? $this->setupAdmin() : $this->setupForm();
            return;
        }
        if ($route === 'logout') {
            $this->auth->logout();
            redirect('/admin/login');
        }
        if (!$this->auth->check()) {
            redirect('/admin/login');
        }
        if ($route === '') {
            $this->dashboard();
            return;
        }
        if ($route === 'tests') {
            $this->testsIndex();
            return;
        }
        if ($route === 'tests/create') {
            method_is('POST') ? $this->storeTest() : $this->testForm();
            return;
        }
        if (preg_match('#^tests/edit/(\d+)$#', $route, $matches) === 1) {
            method_is('POST') ? $this->updateTest((int) $matches[1]) : $this->testForm((int) $matches[1]);
            return;
        }
        if (preg_match('#^tests/delete/(\d+)$#', $route, $matches) === 1) {
            $this->deleteTest((int) $matches[1]);
            return;
        }
        if (preg_match('#^tests/(\d+)/results$#', $route, $matches) === 1) {
            $this->resultsIndex((int) $matches[1]);
            return;
        }
        if (preg_match('#^tests/(\d+)/results/create$#', $route, $matches) === 1) {
            method_is('POST') ? $this->storeResult((int) $matches[1]) : $this->resultForm(null, (int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:results/edit|результаты/редактировать)/(\d+)$#', $route, $matches) === 1) {
            method_is('POST') ? $this->updateResult((int) $matches[1]) : $this->resultForm((int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:results/delete|результаты/удалить)/(\d+)$#', $route, $matches) === 1) {
            $this->deleteResult((int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:tests/|тесты/)(\d+)(?:/questions|/вопросы)$#', $route, $matches) === 1) {
            $this->questionsIndex((int) $matches[1]);
            return;
        }
        if (preg_match('#^tests/(\d+)/questions/create$#', $route, $matches) === 1) {
            method_is('POST') ? $this->storeQuestion((int) $matches[1]) : $this->questionForm(null, (int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:questions/edit|вопросы/редактировать)/(\d+)$#', $route, $matches) === 1) {
            method_is('POST') ? $this->updateQuestion((int) $matches[1]) : $this->questionForm((int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:questions/delete|вопросы/удалить)/(\d+)$#', $route, $matches) === 1) {
            $this->deleteQuestion((int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:questions/|вопросы/)(\d+)(?:/answers|/ответы)$#', $route, $matches) === 1) {
            $this->answersIndex((int) $matches[1]);
            return;
        }
        if (preg_match('#^questions/(\d+)(?:/answers/create|/ответы/создать)$#', $route, $matches) === 1) {
            method_is('POST') ? $this->storeAnswer((int) $matches[1]) : $this->answerForm(null, (int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:answers/edit|ответы/редактировать)/(\d+)$#', $route, $matches) === 1) {
            method_is('POST') ? $this->updateAnswer((int) $matches[1]) : $this->answerForm((int) $matches[1]);
            return;
        }
        if (preg_match('#^(?:answers/delete|ответы/удалить)/(\d+)$#', $route, $matches) === 1) {
            $this->deleteAnswer((int) $matches[1]);
            return;
        }
        http_response_code(404);
        render_admin('admin/views/dashboard/index.php', [
            'config' => $this->config,
            'pageTitle' => 'Страница не найдена',
            'stats' => $this->admins->getDashboardStats(),
            'routeError' => 'Страница админки не найдена.',
        ]);
    }
    private function loginForm(): void
    {
        if ($this->admins->countAdmins() === 0) {
            redirect('/admin/setup');
        }
        render_admin('admin/views/auth/login.php', [
            'config' => $this->config,
            'pageTitle' => 'Вход в админку',
            'isSetup' => false,
        ]);
    }
    private function login(): void
    {
        $this->assertCsrf();
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (!$this->auth->attempt($username, $password)) {
            flash('error', 'Неверный логин или пароль.');
            flash('old.username', $username);
            redirect('/admin/login');
        }
        flash('success', 'Вы успешно вошли в админ-панель.');
        redirect('/admin');
    }
    private function setupForm(): void
    {
        if ($this->admins->countAdmins() > 0) {
            redirect('/admin/login');
        }
        render_admin('admin/views/auth/login.php', [
            'config' => $this->config,
            'pageTitle' => 'Создание первого администратора',
            'isSetup' => true,
        ]);
    }
    private function setupAdmin(): void
    {
        $this->assertCsrf();
        if ($this->admins->countAdmins() > 0) {
            redirect('/admin/login');
        }
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');
        if ($username === '' || mb_strlen($username, 'UTF-8') < 3) {
            back_with_input('error', 'Логин администратора должен содержать минимум 3 символа.', '/admin/setup');
        }
        if (strlen($password) < 8) {
            back_with_input('error', 'Пароль должен содержать минимум 8 символов.', '/admin/setup');
        }
        if ($password !== $confirm) {
            back_with_input('error', 'Пароли не совпадают.', '/admin/setup');
        }
        $this->admins->createAdmin($username, $password);
        flash('success', 'Первый администратор создан. Теперь можно войти.');
        redirect('/admin/login');
    }
    private function dashboard(): void
    {
        render_admin('admin/views/dashboard/index.php', [
            'config' => $this->config,
            'pageTitle' => 'Панель управления',
            'stats' => $this->admins->getDashboardStats(),
            'routeError' => null,
        ]);
    }
    private function testsIndex(): void
    {
        render_admin('admin/views/tests/index.php', [
            'config' => $this->config,
            'pageTitle' => 'Тесты',
            'tests' => $this->admins->getAllTests(),
        ]);
    }
    private function testForm(?int $id = null): void
    {
        $test = $id ? $this->admins->findTest($id) : null;
        if ($id && !$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        render_admin('admin/views/tests/form.php', [
            'config' => $this->config,
            'pageTitle' => $test ? 'Редактирование теста' : 'Новый тест',
            'test' => $test,
        ]);
    }
    private function storeTest(): void
    {
        $this->assertCsrf();
        $payload = $this->validateTestPayload();
        $imagePath = null;
        if (!empty($_FILES['cover_image']['name'])) {
            $imagePath = $this->uploader->upload($_FILES['cover_image'], 'tests');
        }
        $payload['cover_image'] = $imagePath;
        $testId = $this->admins->createTest($payload);
        $this->refreshSitemap();
        flash('success', 'Тест создан.');
        redirect('/admin/tests/edit/' . $testId);
    }
    private function updateTest(int $id): void
    {
        $this->assertCsrf();
        $test = $this->admins->findTest($id);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        $payload = $this->validateTestPayload($id);
        $imagePath = $test['cover_image'];
        if (!empty($_POST['remove_cover'])) {
            $this->uploader->delete($imagePath);
            $imagePath = null;
        }
        if (!empty($_FILES['cover_image']['name'])) {
            $newPath = $this->uploader->upload($_FILES['cover_image'], 'tests');
            $this->uploader->delete($imagePath);
            $imagePath = $newPath;
        }
        $payload['cover_image'] = $imagePath;
        $this->admins->updateTest($id, $payload);
        $this->refreshSitemap();
        flash('success', 'Тест обновлен.');
        redirect('/admin/tests/edit/' . $id);
    }
    private function deleteTest(int $id): void
    {
        $this->assertCsrf();
        $test = $this->admins->findTest($id);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        $this->uploader->delete($test['cover_image']);
        $this->admins->deleteTest($id);
        $this->refreshSitemap();
        flash('success', 'Тест удален.');
        redirect('/admin/tests');
    }
    private function resultsIndex(int $testId): void
    {
        $test = $this->admins->findTest($testId);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        render_admin('admin/views/results/index.php', [
            'config' => $this->config,
            'pageTitle' => 'Результаты теста',
            'test' => $test,
            'results' => $this->admins->getResultsForTest($testId),
        ]);
    }
    private function resultForm(?int $id = null, ?int $testId = null): void
    {
        $result = $id ? $this->admins->findResult($id) : null;
        if ($id && !$result) {
            flash('error', 'Результат не найден.');
            redirect('/admin/tests');
        }
        $test = $this->admins->findTest((int) ($result['test_id'] ?? $testId));
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        render_admin('admin/views/results/form.php', [
            'config' => $this->config,
            'pageTitle' => $result ? 'Редактирование результата' : 'Новый результат',
            'test' => $test,
            'result' => $result,
        ]);
    }
    private function storeResult(int $testId): void
    {
        $this->assertCsrf();
        $test = $this->admins->findTest($testId);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        $payload = [
            'test_id' => $testId,
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'priority' => max(0, (int) ($_POST['priority'] ?? 0)),
            'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
        ];
        if ($payload['title'] === '' || $payload['description'] === '') {
            back_with_input('error', 'Заполните название и текст результата.', '/admin/tests/' . $testId . '/results/create');
        }
        $payload['image_path'] = !empty($_FILES['image_path']['name'])
            ? $this->uploader->upload($_FILES['image_path'], 'results')
            : null;
        $this->admins->createResult($payload);
        flash('success', 'Результат добавлен.');
        redirect('/admin/tests/' . $testId . '/results');
    }
    private function updateResult(int $id): void
    {
        $this->assertCsrf();
        $result = $this->admins->findResult($id);
        if (!$result) {
            flash('error', 'Результат не найден.');
            redirect('/admin/tests');
        }
        $payload = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'priority' => max(0, (int) ($_POST['priority'] ?? 0)),
            'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
        ];
        if ($payload['title'] === '' || $payload['description'] === '') {
            back_with_input('error', 'Заполните название и текст результата.', '/admin/results/edit/' . $id);
        }
        $imagePath = $result['image_path'];
        if (!empty($_POST['remove_image'])) {
            $this->uploader->delete($imagePath);
            $imagePath = null;
        }
        if (!empty($_FILES['image_path']['name'])) {
            $newPath = $this->uploader->upload($_FILES['image_path'], 'results');
            $this->uploader->delete($imagePath);
            $imagePath = $newPath;
        }
        $payload['image_path'] = $imagePath;
        $this->admins->updateResult($id, $payload);
        flash('success', 'Результат обновлен.');
        redirect('/admin/tests/' . $result['test_id'] . '/results');
    }
    private function deleteResult(int $id): void
    {
        $this->assertCsrf();
        $result = $this->admins->findResult($id);
        if (!$result) {
            flash('error', 'Результат не найден.');
            redirect('/admin/tests');
        }
        $this->uploader->delete($result['image_path']);
        $this->admins->deleteResult($id);
        flash('success', 'Результат удален.');
        redirect('/admin/tests/' . $result['test_id'] . '/results');
    }
    private function questionsIndex(int $testId): void
    {
        $test = $this->admins->findTest($testId);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        render_admin('admin/views/questions/index.php', [
            'config' => $this->config,
            'pageTitle' => 'Вопросы теста',
            'test' => $test,
            'questions' => $this->admins->getQuestionsForTest($testId),
        ]);
    }
    private function questionForm(?int $id = null, ?int $testId = null): void
    {
        $question = $id ? $this->admins->findQuestion($id) : null;
        if ($id && !$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $test = $this->admins->findTest((int) ($question['test_id'] ?? $testId));
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        render_admin('admin/views/questions/form.php', [
            'config' => $this->config,
            'pageTitle' => $question ? 'Редактирование вопроса' : 'Новый вопрос',
            'test' => $test,
            'question' => $question,
        ]);
    }
    private function storeQuestion(int $testId): void
    {
        $this->assertCsrf();
        $test = $this->admins->findTest($testId);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        $payload = [
            'test_id' => $testId,
            'question_text' => trim((string) ($_POST['question_text'] ?? '')),
            'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
        ];
        if ($payload['question_text'] === '') {
            back_with_input('error', 'Текст вопроса обязателен.', '/admin/tests/' . $testId . '/questions/create');
        }
        $payload['image_path'] = !empty($_FILES['image_path']['name'])
            ? $this->uploader->upload($_FILES['image_path'], 'questions')
            : null;
        $questionId = $this->admins->createQuestion($payload);
        flash('success', 'Вопрос создан. Теперь добавьте к нему 4 ответа.');
        redirect('/admin/questions/' . $questionId . '/answers');
    }
    private function updateQuestion(int $id): void
    {
        $this->assertCsrf();
        $question = $this->admins->findQuestion($id);
        if (!$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $payload = [
            'question_text' => trim((string) ($_POST['question_text'] ?? '')),
            'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
        ];
        if ($payload['question_text'] === '') {
            back_with_input('error', 'Текст вопроса обязателен.', '/admin/questions/edit/' . $id);
        }
        $imagePath = $question['image_path'];
        if (!empty($_POST['remove_image'])) {
            $this->uploader->delete($imagePath);
            $imagePath = null;
        }
        if (!empty($_FILES['image_path']['name'])) {
            $newPath = $this->uploader->upload($_FILES['image_path'], 'questions');
            $this->uploader->delete($imagePath);
            $imagePath = $newPath;
        }
        $payload['image_path'] = $imagePath;
        $this->admins->updateQuestion($id, $payload);
        flash('success', 'Вопрос обновлен.');
        redirect('/admin/tests/' . $question['test_id'] . '/questions');
    }
    private function deleteQuestion(int $id): void
    {
        $this->assertCsrf();
        $question = $this->admins->findQuestion($id);
        if (!$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $this->uploader->delete($question['image_path']);
        $this->admins->deleteQuestion($id);
        flash('success', 'Вопрос удален.');
        redirect('/admin/tests/' . $question['test_id'] . '/questions');
    }
    private function answersIndex(int $questionId): void
    {
        $question = $this->admins->findQuestion($questionId);
        if (!$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $test = $this->admins->findTest((int) $question['test_id']);
        $answers = $this->admins->getAnswersForQuestion($questionId);
        render_admin('admin/views/answers/index.php', [
            'config' => $this->config,
            'pageTitle' => 'Ответы вопроса',
            'test' => $test,
            'question' => $question,
            'answers' => $answers,
            'results' => $this->admins->getResultsForTest((int) $question['test_id']),
        ]);
    }
    private function answerForm(?int $id = null, ?int $questionId = null): void
    {
        $answer = $id ? $this->admins->findAnswer($id) : null;
        if ($id && !$answer) {
            flash('error', 'Ответ не найден.');
            redirect('/admin/tests');
        }
        $question = $this->admins->findQuestion((int) ($answer['question_id'] ?? $questionId));
        if (!$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $test = $this->admins->findTest((int) $question['test_id']);
        if (!$test) {
            flash('error', 'Тест не найден.');
            redirect('/admin/tests');
        }
        render_admin('admin/views/answers/form.php', [
            'config' => $this->config,
            'pageTitle' => $answer ? 'Редактирование ответа' : 'Новый ответ',
            'test' => $test,
            'question' => $question,
            'answer' => $answer,
            'results' => $this->admins->getResultsForTest((int) $question['test_id']),
            'answersCount' => count($this->admins->getAnswersForQuestion((int) $question['id'])),
        ]);
    }
    private function storeAnswer(int $questionId): void
    {
        $this->assertCsrf();
        $question = $this->admins->findQuestion($questionId);
        if (!$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $currentAnswers = $this->admins->getAnswersForQuestion($questionId);
        if (count($currentAnswers) >= 4) {
            flash('error', 'У вопроса уже есть 4 ответа. Можно только редактировать существующие.');
            redirect('/admin/questions/' . $questionId . '/answers');
        }
        $payload = $this->validateAnswerPayload($questionId, (int) $question['test_id']);
        $this->admins->createAnswer($payload);
        flash('success', 'Ответ добавлен.');
        redirect('/admin/questions/' . $questionId . '/answers');
    }
    private function updateAnswer(int $id): void
    {
        $this->assertCsrf();
        $answer = $this->admins->findAnswer($id);
        if (!$answer) {
            flash('error', 'Ответ не найден.');
            redirect('/admin/tests');
        }
        $question = $this->admins->findQuestion((int) $answer['question_id']);
        if (!$question) {
            flash('error', 'Вопрос не найден.');
            redirect('/admin/tests');
        }
        $payload = $this->validateAnswerPayload((int) $question['id'], (int) $question['test_id']);
        $this->admins->updateAnswer($id, $payload);
        flash('success', 'Ответ обновлен.');
        redirect('/admin/questions/' . $question['id'] . '/answers');
    }
    private function deleteAnswer(int $id): void
    {
        $this->assertCsrf();
        $answer = $this->admins->findAnswer($id);
        if (!$answer) {
            flash('error', 'Ответ не найден.');
            redirect('/admin/tests');
        }
        $this->admins->deleteAnswer($id);
        flash('success', 'Ответ удален.');
        redirect('/admin/questions/' . $answer['question_id'] . '/answers');
    }
    private function validateTestPayload(?int $ignoreId = null): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $status = ($_POST['status'] ?? 'hidden') === 'published' ? 'published' : 'hidden';
        $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
        $seoTitle = trim((string) ($_POST['seo_title'] ?? ''));
        $seoDescription = trim((string) ($_POST['seo_description'] ?? ''));
        if ($title === '' || $description === '') {
            back_with_input('error', 'Название и описание теста обязательны.', $ignoreId ? '/admin/tests/edit/' . $ignoreId : '/admin/tests/create');
        }
        $slug = $slug !== '' ? slugify($slug) : slugify($title);
        if ($this->admins->findTestBySlug($slug, $ignoreId)) {
            back_with_input('error', 'Slug уже занят. Укажите другой.', $ignoreId ? '/admin/tests/edit/' . $ignoreId : '/admin/tests/create');
        }
        return [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'sort_order' => $sortOrder,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
        ];
    }
    private function validateAnswerPayload(int $questionId, int $testId): array
    {
        $answerText = trim((string) ($_POST['answer_text'] ?? ''));
        $resultId = (int) ($_POST['result_id'] ?? 0);
        $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));
        if ($answerText === '') {
            back_with_input('error', 'Текст ответа обязателен.', '/admin/questions/' . $questionId . '/answers');
        }
        $allowedResultIds = array_map('intval', array_column($this->admins->getResultsForTest($testId), 'id'));
        if (!in_array($resultId, $allowedResultIds, true)) {
            back_with_input('error', 'Выберите корректный результат для ответа.', '/admin/questions/' . $questionId . '/answers');
        }
        return [
            'question_id' => $questionId,
            'result_id' => $resultId,
            'answer_text' => $answerText,
            'sort_order' => $sortOrder,
        ];
    }
    private function assertCsrf(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            http_response_code(403);
            render_admin('admin/views/dashboard/index.php', [
                'config' => $this->config,
                'pageTitle' => 'Ошибка безопасности',
                'stats' => $this->admins->getDashboardStats(),
                'routeError' => 'Срок действия формы истек. Повторите действие еще раз.',
            ]);
            exit;
        }
    }
    private function refreshSitemap(): void
    {
        $this->sitemap->sync($this->siteRepository->getPublishedTestsForSitemap());
    }
}