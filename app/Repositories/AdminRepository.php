<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;
final class AdminRepository
{
    public function __construct(private readonly PDO $db)
    {
    }
    public function findAdminByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }
    public function countAdmins(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    }
    public function createAdmin(string $username, string $password): void
    {
        $stmt = $this->db->prepare('INSERT INTO admins (username, password_hash, created_at, updated_at)
            VALUES (:username, :password_hash, NOW(), NOW())');
        $stmt->execute([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
    public function getDashboardStats(): array
    {
        $stats = [];
        $stats['total_attempts'] = (int) $this->db->query('SELECT COUNT(*) FROM quiz_attempts WHERE status = "completed"')->fetchColumn();
        $stats['published_tests'] = (int) $this->db->query('SELECT COUNT(*) FROM tests WHERE status = "published"')->fetchColumn();
        $stats['all_tests'] = (int) $this->db->query('SELECT COUNT(*) FROM tests')->fetchColumn();
        $stats['recent_attempts'] = $this->db->query('SELECT qa.completed_at, qa.nickname, t.title AS test_title, tr.title AS result_title
            FROM quiz_attempts qa
            INNER JOIN tests t ON t.id = qa.test_id
            LEFT JOIN test_results tr ON tr.id = qa.result_id
            WHERE qa.status = "completed"
            ORDER BY qa.completed_at DESC, qa.id DESC
            LIMIT 10')->fetchAll();
        $stats['popular_tests'] = $this->db->query('SELECT t.id, t.title, COUNT(qa.id) AS total_attempts
            FROM tests t
            LEFT JOIN quiz_attempts qa ON qa.test_id = t.id AND qa.status = "completed"
            GROUP BY t.id, t.title
            ORDER BY total_attempts DESC, t.title ASC
            LIMIT 7')->fetchAll();
        $tests = $this->db->query('SELECT id, title FROM tests ORDER BY sort_order ASC, id DESC')->fetchAll();
        $stats['results_by_test'] = [];
        $stmt = $this->db->prepare('SELECT tr.title, COUNT(qa.id) AS total
            FROM test_results tr
            LEFT JOIN quiz_attempts qa ON qa.result_id = tr.id AND qa.status = "completed"
            WHERE tr.test_id = :test_id
            GROUP BY tr.id, tr.title
            ORDER BY total DESC, tr.priority DESC, tr.id ASC');
        foreach ($tests as $test) {
            $stmt->execute(['test_id' => $test['id']]);
            $stats['results_by_test'][] = [
                'test_title' => $test['title'],
                'rows' => $stmt->fetchAll(),
            ];
        }
        return $stats;
    }
    public function getAllTests(): array
    {
        $sql = 'SELECT t.*,
            (SELECT COUNT(*) FROM test_results tr WHERE tr.test_id = t.id) AS results_count,
            (SELECT COUNT(*) FROM test_questions tq WHERE tq.test_id = t.id) AS questions_count,
            (SELECT COUNT(*) FROM quiz_attempts qa WHERE qa.test_id = t.id AND qa.status = "completed") AS attempts_count
            FROM tests t
            ORDER BY t.sort_order ASC, t.id DESC';
        return $this->db->query($sql)->fetchAll();
    }
    public function findTest(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tests WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $test = $stmt->fetch();
        return $test ?: null;
    }
    public function findTestBySlug(string $slug, ?int $ignoreId = null): ?array
    {
        $sql = 'SELECT * FROM tests WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' И идентификатор != :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }
        $sql .= ' ПРЕДЕЛ 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $test = $stmt->fetch();
        return $test ?: null;
    }
    public function createTest(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO tests
            (title, slug, description, cover_image, status, sort_order, seo_title, seo_description, created_at, updated_at)
            VALUES
            (:title, :slug, :description, :cover_image, :status, :sort_order, :seo_title, :seo_description, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
    public function updateTest(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE tests
            SET title = :title,
                slug = :slug,
                description = :description,
                cover_image = :cover_image,
                status = :status,
                sort_order = :sort_order,
                seo_title = :seo_title,
                seo_description = :seo_description,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute($data);
    }
    public function deleteTest(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tests WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    public function getResultsForTest(int $testId): array
    {
        $stmt = $this->db->prepare('SELECT tr.*,
            (SELECT COUNT(*) FROM question_answers qa WHERE qa.result_id = tr.id) AS linked_answers_count
            FROM test_results tr
            WHERE tr.test_id = :test_id
            ORDER BY tr.priority DESC, tr.sort_order ASC, tr.id ASC');
        $stmt->execute(['test_id' => $testId]);
        return $stmt->fetchAll();
    }
    public function findResult(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM test_results WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function createResult(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO test_results
            (test_id, title, description, image_path, priority, sort_order, created_at, updated_at)
            VALUES
            (:test_id, :title, :description, :image_path, :priority, :sort_order, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
    public function updateResult(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE test_results
            SET title = :title,
                description = :description,
                image_path = :image_path,
                priority = :priority,
                sort_order = :sort_order,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute($data);
    }
    public function deleteResult(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM test_results WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    public function getQuestionsForTest(int $testId): array
    {
        $stmt = $this->db->prepare('SELECT tq.*,
            (SELECT COUNT(*) FROM question_answers qa WHERE qa.question_id = tq.id) AS answers_count
            FROM test_questions tq
            WHERE tq.test_id = :test_id
            ORDER BY tq.sort_order ASC, tq.id ASC');
        $stmt->execute(['test_id' => $testId]);
        return $stmt->fetchAll();
    }
    public function findQuestion(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM test_questions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function createQuestion(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO test_questions
            (test_id, question_text, image_path, sort_order, created_at, updated_at)
            VALUES
            (:test_id, :question_text, :image_path, :sort_order, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
    public function updateQuestion(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE test_questions
            SET question_text = :question_text,
                image_path = :image_path,
                sort_order = :sort_order,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute($data);
    }
    public function deleteQuestion(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM test_questions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    public function getAnswersForQuestion(int $questionId): array
    {
        $stmt = $this->db->prepare('SELECT qa.*, tr.title AS result_title
            FROM question_answers qa
            INNER JOIN test_results tr ON tr.id = qa.result_id
            WHERE qa.question_id = :question_id
            ORDER BY qa.sort_order ASC, qa.id ASC');
        $stmt->execute(['question_id' => $questionId]);
        return $stmt->fetchAll();
    }
    public function findAnswer(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM question_answers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function createAnswer(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO question_answers
            (question_id, result_id, answer_text, sort_order, created_at, updated_at)
            VALUES
            (:question_id, :result_id, :answer_text, :sort_order, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
    public function updateAnswer(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE question_answers
            SET result_id = :result_id,
                answer_text = :answer_text,
                sort_order = :sort_order,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute($data);
    }
    public function deleteAnswer(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM question_answers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}