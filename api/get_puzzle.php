<?php
/**
 * Banana Quiz Game - External API Proxy (Get Puzzle)
 * CIS046-3 Software For Enterprise
 * 
 * Demonstrates:
 * - Theme 2: Interoperability (Backend cURL integration with Marc Conrad's Banana API)
 * - Distributed Architecture (Server-to-server HTTP/REST communication)
 * - Security: Solution stored exclusively in server-side session, NEVER exposed to client
 * 
 * Attribution: Built with AI pair-programming assistance.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

// Banana API Endpoint as defined in official documentation
// Documentation: https://marcconrad.com/uob/banana/doc.php
const BANANA_API_ENDPOINT = 'https://marcconrad.com/uob/banana/api.php?out=json';
const QUESTIONS_PER_ROUND = 5;

// Initialize or reset game session
if (!isset($_SESSION['game']) || isset($_GET['new'])) {
    $_SESSION['game'] = [
        'round_id'         => bin2hex(random_bytes(8)),
        'question_index'   => 1,
        'total_questions'  => QUESTIONS_PER_ROUND,
        'score'            => 0,
        'correct_count'    => 0,
        'current_solution' => null,
        'current_image'    => null,
    ];
}

$game = &$_SESSION['game'];

// Check if current round has already concluded
if ($game['question_index'] > $game['total_questions']) {
    json_response([
        'success'         => true,
        'is_game_over'    => true,
        'message'         => 'Current quiz round is complete.',
        'score'           => $game['score'],
        'correct_count'   => $game['correct_count'],
        'total_questions' => $game['total_questions'],
    ]);
}

/**
 * Fetch puzzle from official Banana API via cURL
 *
 * @param string $endpoint
 * @return array{question: string, solution: int}|null
 */
function fetchBananaPuzzle(string $endpoint): ?array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'BananaQuizGame/1.0 (University Enterprise Assignment CIS046-3)',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // If local SSL certificate issue occurs in some student XAMPP setups (cURL error 60), retry with fallback
    if ($curlErr === 60) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $curlErr  = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }

    curl_close($ch);

    if ($curlErr !== 0 || $httpCode !== 200 || !$response) {
        error_log('[Banana API Interoperability Failure] HTTP Code: ' . $httpCode . ' Error: ' . curl_strerror($curlErr));
        return null;
    }

    $decoded = json_decode((string)$response, true);
    if (!is_array($decoded) || !isset($decoded['question']) || !isset($decoded['solution'])) {
        error_log('[Banana API Format Error] Invalid payload received: ' . $response);
        return null;
    }

    return [
        'question' => (string)$decoded['question'],
        'solution' => (int)$decoded['solution'],
    ];
}

// Perform external interoperability call
$puzzleData = fetchBananaPuzzle(BANANA_API_ENDPOINT);

if ($puzzleData === null) {
    json_response([
        'success'   => false,
        'error'     => 'The Banana API service is temporarily unreachable or experiencing latency. Please click Retry.',
        'can_retry' => true,
    ], 503);
}

// Store solution in secure session state ONLY - DO NOT SEND SOLUTION TO FRONTEND
$game['current_solution'] = $puzzleData['solution'];
$game['current_image']    = $puzzleData['question'];

// Return question image URL and game progress state to client
json_response([
    'success'         => true,
    'is_game_over'    => false,
    'question_image'  => $puzzleData['question'],
    'question_number' => $game['question_index'],
    'total_questions' => $game['total_questions'],
    'score'           => $game['score'],
    'correct_count'   => $game['correct_count'],
]);
