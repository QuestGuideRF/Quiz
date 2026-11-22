<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;
final class SiteRepository
{
    public function __construct(private readonly PDO $db)
    {
    }
    public function getPublishedTests(): array
    {
        $sql = 'SELECT id, title, slug, description, cover_image, seo_title, seo_description
                FROM tests
                WHERE status = "published"
                ORDER BY sort_order ASC, id DESC';
        return $this->db->query($sql)->fetchAll();
    }
    public function getPublishedTestsForSitemap(): array
    {
        $sql = 'SELECT slug, updated_at
                FROM tests
                WHERE status = "published"
                ORDER BY sort_order ASC, id DESC';
        return $this->db->query($sql)->fetchAll();
    }
    public function findPublishedTestBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT *
            FROM tests
            WHERE slug = :slug AND status = "published"
            LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $test = $stmt->fetch();
        if (!$test) {
            return null;
        }
        $test['results'] = $this->getResultsByTestId((int) $test['id']);
        $test['questions'] = $this->getQuestionsWithAnswers((int) $test['id']);
        return $test;
    }
    public function getResultsByTestId(int $testId): array
    {
        $stmt = $this->db->prepare('SELECT *
            FROM test_results
            WHERE test_id = :test_id
            ORDER BY priority DESC, sort_order ASC, id ASC');
        $stmt->execute(['test_id' => $testId]);
        return $stmt->fetchAll();
    }
    public function getQuestionsWithAnswers(int $testId): array
    {
        $questionsStmt = $this->db->prepare('SELECT *
            FROM test_questions
            WHERE test_id = :test_id
            ORDER BY sort_order ASC, id ASC');
        $questionsStmt->execute(['test_id' => $testId]);
        $questions = $questionsStmt->fetchAll();
        $answerStmt = $this->db->prepare('SELECT qa.*, tr.title AS result_title
            FROM question_answers qa
            INNER JOIN test_results tr ON tr.id = qa.result_id
            WHERE qa.question_id = :question_id
            ORDER BY qa.sort_order ASC, qa.id ASC');
        foreach ($questions as &$question) {
            $answerStmt->execute(['question_id' => $question['id']]);
            $question['answers'] = $answerStmt->fetchAll();
        }
        unset($question);
        return $questions;
    }
    public function createAttempt(int $testId, string $nickname, ?string $ip, ?string $userAgent): array
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare('INSERT INTO quiz_attempts
            (test_id, nickname, token, status, current_step, ip_address, user_agent, started_at, created_at, updated_at)
            VALUES
            (:test_id, :nickname, :token, "started", 1, :ip_address, :user_agent, NOW(), NOW(), NOW())');
        $stmt->execute([
            'test_id' => $testId,
            'nickname' => $nickname,
            'token' => $token,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
        return $this->findAttemptByToken($token);
    }
    public function findAttemptByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT qa.*, t.slug, t.title AS test_title, tr.title AS result_title, tr.description AS result_description, tr.image_path AS result_image
            FROM quiz_attempts qa
            INNER JOIN tests t ON t.id = qa.test_id
            LEFT JOIN test_results tr ON tr.id = qa.result_id
            WHERE qa.token = :token
            LIMIT 1');
        $stmt->execute(['token' => $token]);
        $attempt = $stmt->fetch();
        return $attempt ?: null;
    }
    public function findQuestionByPosition(int $testId, int $position): ?array
    {
        $stmt = $this->db->prepare('SELECT *
            FROM test_questions
            WHERE test_id = :test_id
            ORDER BY sort_order ASC, id ASC
            LIMIT 1 OFFSET :offset');
        $stmt->bindValue(':test_id', $testId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $position - 1, PDO::PARAM_INT);
        $stmt->execute();
        $question = $stmt->fetch();
        if (!$question) {
            return null;
        }
        $answersStmt = $this->db->prepare('SELECT qa.*, tr.title AS result_title
            FROM question_answers qa
            INNER JOIN test_results tr ON tr.id = qa.result_id
            WHERE qa.question_id = :question_id
            ORDER BY qa.sort_order ASC, qa.id ASC');
        $answersStmt->execute(['question_id' => $question['id']]);
        $question['answers'] = $answersStmt->fetchAll();
        return $question;
    }
    public function countQuestions(int $testId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM test_questions WHERE test_id = :test_id');
        $stmt->execute(['test_id' => $testId]);
        return (int) $stmt->fetchColumn();
    }
    public function isTestReady(int $testId): bool
    {
        $questions = $this->getQuestionsWithAnswers($testId);
        if (count($questions) === 0) {
            return false;
        }
        foreach ($questions as $question) {
            if (count($question['answers']) !== 4) {
                return false;
            }
        }
        return count($this->getResultsByTestId($testId)) > 0;
    }
    public function saveAttemptAnswer(int $attemptId, int $questionId, int $answerId, int $resultId): void
    {
        $existing = $this->db->prepare('SELECT id FROM attempt_answers WHERE attempt_id = :attempt_id AND question_id = :question_id LIMIT 1');
        $existing->execute([
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
        ]);
        $existingId = $existing->fetchColumn();
        if ($existingId) {
            $stmt = $this->db->prepare('UPDATE attempt_answers
                SET answer_id = :answer_id, result_id = :result_id, created_at = NOW()
                WHERE id = :id');
            $stmt->execute([
                'answer_id' => $answerId,
                'result_id' => $resultId,
                'id' => $existingId,
            ]);
            return;
        }
        $stmt = $this->db->prepare('INSERT INTO attempt_answers
            (attempt_id, question_id, answer_id, result_id, created_at)
            VALUES
            (:attempt_id, :question_id, :answer_id, :result_id, NOW())');
        $stmt->execute([
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'answer_id' => $answerId,
            'result_id' => $resultId,
        ]);
    }
    public function updateAttemptStep(int $attemptId, int $step): void
    {
        $stmt = $this->db->prepare('UPDATE quiz_attempts
            SET current_step = :current_step, updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'current_step' => $step,
            'id' => $attemptId,
        ]);
    }
    public function finishAttempt(int $attemptId, int $resultId): void
    {
        $stmt = $this->db->prepare('UPDATE quiz_attempts
            SET result_id = :result_id, status = "completed", completed_at = NOW(), updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'result_id' => $resultId,
            'id' => $attemptId,
        ]);
    }
    public function getWinningResultId(int $attemptId, int $testId): int
    {
        $stmt = $this->db->prepare('SELECT aa.result_id, COUNT(*) AS total, tr.priority
            FROM attempt_answers aa
            INNER JOIN test_results tr ON tr.id = aa.result_id
            WHERE aa.attempt_id = :attempt_id AND tr.test_id = :test_id
            GROUP BY aa.result_id, tr.priority
            ORDER BY total DESC, tr.priority DESC, aa.result_id ASC
            LIMIT 1');
        $stmt->execute([
            'attempt_id' => $attemptId,
            'test_id' => $testId,
        ]);
        return (int) $stmt->fetchColumn();
    }
    public function findAnswerForQuestion(int $answerId, int $questionId): ?array
    {
        $stmt = $this->db->prepare('SELECT *
            FROM question_answers
            WHERE id = :id AND question_id = :question_id
            LIMIT 1');
        $stmt->execute([
            'id' => $answerId,
            'question_id' => $questionId,
        ]);
        $answer = $stmt->fetch();
        return $answer ?: null;
    }
}