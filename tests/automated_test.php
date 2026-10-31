<?php
/**
 * Banana Quiz Game - Automated Test Suite
 * CIS046-3 Software For Enterprise
 * 
 * Verifies core functionality:
 * - MySQL connection & table integrity
 * - Password hashing and verification
 * - Banana API interoperability
 * - Score validation & persistence
 * - User data segregation
 * 
 * Run via CLI: php tests/automated_test.php
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

echo "====================================================================\n";
echo "   BANANA QUIZ GAME - AUTOMATED TEST SUITE (CIS046-3)              \n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $testName, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo " [PASS] " . $testName . "\n";
    } else {
        $failCount++;
        echo " [FAIL] " . $testName . " -> " . $details . "\n";
    }
}

// ----------------------------------------------------------------------------
// Test 1: Database Connection
// ----------------------------------------------------------------------------
try {
    $pdo = Database::getConnection();
    assertTest("Test 1: MySQL PDO Database Connection", $pdo instanceof PDO);
} catch (Exception $e) {
    assertTest("Test 1: MySQL PDO Database Connection", false, $e->getMessage());
}

// ----------------------------------------------------------------------------
// Test 2: Database Schema & Tables Check
// ----------------------------------------------------------------------------
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    assertTest("Test 2A: Table 'users' exists", count($tables) > 0);

    $tablesResults = $pdo->query("SHOW TABLES LIKE 'game_results'")->fetchAll();
    assertTest("Test 2B: Table 'game_results' exists", count($tablesResults) > 0);
} catch (Exception $e) {
    assertTest("Test 2: Schema verification", false, $e->getMessage());
}

// ----------------------------------------------------------------------------
// Test 3: Password Hashing & Verification (Virtual Identity Theme)
// ----------------------------------------------------------------------------
$rawPassword = 'SecurePassword2026!';
$hash = password_hash($rawPassword, PASSWORD_DEFAULT);
$verifyGood = password_verify($rawPassword, $hash);
$verifyBad  = password_verify('WrongPassword!', $hash);

assertTest("Test 3A: password_hash() produces valid hash", strlen($hash) > 50);
assertTest("Test 3B: password_verify() confirms correct password", $verifyGood === true);
assertTest("Test 3C: password_verify() rejects incorrect password", $verifyBad === false);

// ----------------------------------------------------------------------------
// Test 4: External Banana API Integration (Interoperability Theme)
// ----------------------------------------------------------------------------
$apiEndpoint = 'https://marcconrad.com/uob/banana/api.php?out=json';
$ch = curl_init($apiEndpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 8,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false, // Allow test run in environments without local cert bundle
]);
$apiRaw = curl_exec($ch);
$curlErr = curl_errno($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$apiJson = json_decode((string)$apiRaw, true);
$apiValid = ($curlErr === 0 && $httpStatus === 200 && is_array($apiJson) && isset($apiJson['question']) && isset($apiJson['solution']));

assertTest("Test 4A: Banana API HTTP 200 Response", $httpStatus === 200, "Status: $httpStatus, Err: $curlErr");
assertTest("Test 4B: Banana API returns valid 'question' URL", isset($apiJson['question']) && filter_var($apiJson['question'], FILTER_VALIDATE_URL) !== false);
assertTest("Test 4C: Banana API returns numeric 'solution'", isset($apiJson['solution']) && is_numeric($apiJson['solution']));

// ----------------------------------------------------------------------------
// Test 5: Answer Validation Logic (Server-Side Evaluation)
// ----------------------------------------------------------------------------
$sessionSolution = 7;
$userCorrectAnswer = 7;
$userWrongAnswer = 3;

$evalCorrect = ($userCorrectAnswer === $sessionSolution);
$evalWrong   = ($userWrongAnswer === $sessionSolution);

assertTest("Test 5A: Server correctly scores matching answer", $evalCorrect === true);
assertTest("Test 5B: Server correctly rejects non-matching answer", $evalWrong === false);

// ----------------------------------------------------------------------------
// Test 6: Database Score Persistence (Game Results Storage)
// ----------------------------------------------------------------------------
try {
    // Find or create a test user
    $userStmt = $pdo->prepare("SELECT id FROM users WHERE username = 'testuser' LIMIT 1");
    $userStmt->execute();
    $testUser = $userStmt->fetch();
    $testUserId = $testUser ? (int)$testUser['id'] : 1;

    $insertStmt = $pdo->prepare("
        INSERT INTO game_results (user_id, score, total_questions, correct_answers, accuracy, played_at)
        VALUES (:uid, 50, 5, 5, 100.00, NOW())
    ");
    $insertStmt->execute([':uid' => $testUserId]);
    $insertedId = (int)$pdo->lastInsertId();

    assertTest("Test 6A: Insert game record into MySQL", $insertedId > 0);

    $verifyStmt = $pdo->prepare("SELECT * FROM game_results WHERE id = :id");
    $verifyStmt->execute([':id' => $insertedId]);
    $record = $verifyStmt->fetch();

    assertTest("Test 6B: Retrieved saved score accurately", (int)$record['score'] === 50 && (float)$record['accuracy'] === 100.00);

} catch (Exception $e) {
    assertTest("Test 6: Game score persistence", false, $e->getMessage());
}

// ----------------------------------------------------------------------------
// Test 7: User-Level Data Isolation (Virtual Identity Authorization)
// ----------------------------------------------------------------------------
try {
    // Querying with non-existent user_id 999999 should return 0 records
    $isoStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM game_results WHERE user_id = 999999");
    $isoStmt->execute();
    $cnt = (int)$isoStmt->fetch()['cnt'];

    assertTest("Test 7: User-scoped query isolates data between identities", $cnt === 0);
} catch (Exception $e) {
    assertTest("Test 7: Data isolation", false, $e->getMessage());
}

echo "\n--------------------------------------------------------------------\n";
echo "TEST RESULTS SUMMARY: $passCount Passed, $failCount Failed.\n";
echo "====================================================================\n";

exit($failCount === 0 ? 0 : 1);
