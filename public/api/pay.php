<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/supabase.php';

header('Content-Type: application/json');

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$key = trim((string) ($_GET['key'] ?? $_POST['key'] ?? ''));
$payToNumber = preg_replace('/\D+/', '', (string) ($_GET['paytoNumber'] ?? $_POST['paytoNumber'] ?? ''));
$amount = (float) ($_GET['amount'] ?? $_POST['amount'] ?? 0);
$comment = trim((string) ($_GET['comment'] ?? $_POST['comment'] ?? 'Single Payment'));
$profile = flux_profile();

if ($token !== (string) $profile['api_token'] || $key !== (string) $profile['api_key']) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API credentials']);
    exit;
}

if (strlen($payToNumber) !== 10 || $amount <= 0) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'paytoNumber must be 10 digits and amount must be greater than zero']);
    exit;
}

$transactionId = 'FLX-' . strtoupper(bin2hex(random_bytes(8)));
$payload = [
    'profile_id' => $profile['id'],
    'amount' => -abs($amount),
    'status' => 'success',
    'method' => 'Transfer',
    'category' => 'transfer',
    'notes' => 'Single payment sent',
    'comment' => $comment,
    'net_balance_after' => max(0, (float) $profile['balance'] - $amount),
];

$saved = supabase_request('POST', 'transactions', $payload) !== null;

echo json_encode([
    'status' => 'success',
    'transaction_id' => $transactionId,
    'paytoNumber' => $payToNumber,
    'amount' => $amount,
    'comment' => $comment,
    'saved_to_supabase' => $saved,
]);
