<?php
/**
 * Banana Quiz Game - Submit Answer & Score Validation
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 2 & Theme 3: Interoperable JSON endpoint responding to frontend JavaScript events
 * - Enterprise Security: Server-side answer verification (client never trusted)
 * - Enterprise Persistence: Atomic score recording in MySQL via PDO prepared statement
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Method Not Allowed. Use POST.'], 405);
}

// 1. Verify CSRF Token
$token = get_submitted_csrf_token();
if (!verify_csrf_token($token)) {
    json_response(['success' => false, 'error' => 'Security token invalid or expired. Please refresh the page.'], 403);
}

// 2. Ensure an active game session exists
if (!isset($_SESSION['game']) || !isset($_SESSION['game']['current_solution'])) {
    json_response(['success' => false, 'error' => 'No active puzzle awaiting an answer. Please request a new puzzle.'], 400);
}

// 3. Extract submitted answer (supports JSON body or form payload)
$inputRaw = file_get_contents('php://input');
$inputData = json_decode($inputRaw, true);

$answerValue = null;
if (is_array($inputData) && isset($inputData['answer'])) {
    $answerValue = $inputData['answer'];
} elseif (isset($_POST['answer'])) {
    $answerValue = $_POST['answer'];
}

if ($answerValue === null || !is_numeric($answerValue)) {
    json_response(['success' => false, 'error' => 'Please provide a valid numeric answer.'], 422);
}

$submittedAnswer = (int)$answerValue;
$game = &$_SESSION['game'];
$correctSolution = (int)$game['current_solution'];

// 4. Server-Side Validation: Never trust client score calculations
$isCorrect = ($submittedAnswer === $correctSolution);

// Award 10 points per correct answer
const POINTS_PER_QUESTION = 10;
if ($isCorrect) {
    $game['score'] += POINTS_PER_QUESTION;
    $game['correct_count'] += 1;
}

// Mark this puzzle as evaluated (prevent replay attacks)
$game['current_solution'] = null;

// Advance question index
$game['question_index'] += 1;
$isGameOver = ($game['question_index'] > $game['total_questions']);

$savedRecordId = null;

// 5. If round concluded, persist final game results to MySQL
if ($isGameOver) {
    $totalQuestions = (int)$game['total_questions'];
    $correctAnswers = (int)$game['correct_count'];
    $finalScore     = (int)$game['score'];
    $accuracy       = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0.00;
    $userId         = (int)$_SESSION['user_id'];

    try {
        $pdo = Database::getConnection();
        $insertStmt = $pdo->prepare('
            INSERT INTO game_results (user_id, score, total_questions, correct_answers, accuracy, played_at)
            VALUES (:uid, :score, :total_q, :correct_q, :accuracy, NOW())
        ');
        $insertStmt->execute([
            ':uid'       => $userId,
            ':score'     => $finalScore,
            ':total_q'   => $totalQuestions,
            ':correct_q' => $correctAnswers,
            ':accuracy'  => $accuracy,
        ]);
        $savedRecordId = (int)$pdo->lastInsertId();
    } catch (Exception $e) {
        error_log('[Persistence Error] Failed to save game results: ' . $e->getMessage());
    }
}

// 6. Return evaluation feedback and updated game state
json_response([
    'success'              => true,
    'is_correct'           => $isCorrect,
    'submitted_answer'     => $submittedAnswer,
    'correct_solution'     => $correctSolution, // Now revealed as instructional feedback
    'current_score'        => $game['score'],
    'correct_count'        => $game['correct_count'],
    'is_game_over'         => $isGameOver,
    'next_question_number' => $game['question_index'],
    'total_questions'      => $game['total_questions'],
    'final_score'          => $isGameOver ? $game['score'] : null,
    'accuracy'             => $isGameOver ? round(($game['correct_count'] / $game['total_questions']) * 100, 2) : null,
    'saved_record_id'      => $savedRecordId,
]);
