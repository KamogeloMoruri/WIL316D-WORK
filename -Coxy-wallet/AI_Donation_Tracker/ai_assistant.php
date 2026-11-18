<?php
/**
 * ai_assistant.php
 *
 * Answers questions about the donations stored in the database.
 *
 * Out of the box this works with NO configuration and NO API key: it
 * answers common questions (total raised, number of donations, top donor,
 * breakdown by purpose, etc.) by reading straight from the database.
 *
 * If you want it backed by a real AI model instead, set the
 * ANTHROPIC_API_KEY environment variable (see README.md) - the code below
 * will automatically use the Claude API when that key is present, and
 * silently falls back to the built-in rule-based answers otherwise.
 */

require __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['answer' => 'Only POST requests are allowed.']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$question = trim($input['question'] ?? '');

if ($question === '') {
    echo json_encode(['answer' => 'Please type a question first.']);
    exit;
}

$rows = $pdo->query('SELECT donor_name, amount, purpose FROM donations')->fetchAll(PDO::FETCH_ASSOC);

$apiKey = getenv('ANTHROPIC_API_KEY');
if ($apiKey) {
    $answer = ask_claude($question, $rows, $apiKey);
    if ($answer !== null) {
        echo json_encode(['answer' => $answer]);
        exit;
    }
    // fall through to rule-based answer if the API call failed
}

echo json_encode(['answer' => answer_locally($question, $rows)]);

/**
 * Very small rule-based assistant. No external calls, no API key needed.
 */
function answer_locally(string $question, array $rows): string
{
    $q = strtolower($question);

    $total = array_sum(array_column($rows, 'amount'));
    $count = count($rows);

    if ($count === 0) {
        return "There aren't any donations recorded yet.";
    }

    if (str_contains($q, 'total') || str_contains($q, 'how much')) {
        return sprintf('A total of R%s has been donated across %d donation(s).', number_format($total, 2), $count);
    }

    if (str_contains($q, 'how many') || str_contains($q, 'count') || str_contains($q, 'number of')) {
        return sprintf('There have been %d donation(s) recorded so far.', $count);
    }

    if (str_contains($q, 'top') || str_contains($q, 'biggest') || str_contains($q, 'largest')) {
        $top = null;
        foreach ($rows as $row) {
            if ($top === null || $row['amount'] > $top['amount']) {
                $top = $row;
            }
        }
        return sprintf('The largest donation is R%s from %s.', number_format($top['amount'], 2), $top['donor_name']);
    }

    if (str_contains($q, 'purpose') || str_contains($q, 'category') || str_contains($q, 'breakdown')) {
        $byPurpose = [];
        foreach ($rows as $row) {
            $byPurpose[$row['purpose']] = ($byPurpose[$row['purpose']] ?? 0) + $row['amount'];
        }
        $parts = [];
        foreach ($byPurpose as $purpose => $amount) {
            $parts[] = sprintf('%s: R%s', $purpose, number_format($amount, 2));
        }
        return 'Breakdown by purpose - ' . implode(', ', $parts) . '.';
    }

    if (str_contains($q, 'average')) {
        return sprintf('The average donation is R%s.', number_format($total / $count, 2));
    }

    return sprintf(
        "I can answer questions about totals, counts, averages, top donors, and breakdown by purpose. So far R%s has been donated across %d donation(s).",
        number_format($total, 2),
        $count
    );
}

/**
 * Optional: use the real Claude API when an API key is configured.
 * Returns null on any failure so the caller can fall back gracefully.
 */
function ask_claude(string $question, array $rows, string $apiKey): ?string
{
    $context = json_encode($rows);
    $payload = json_encode([
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => 300,
        'messages'   => [[
            'role'    => 'user',
            'content' => "You are a donation-tracking assistant. Here is the donation data as JSON: {$context}\n\nAnswer this question concisely: {$question}",
        ]],
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    return $data['content'][0]['text'] ?? null;
}
