<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function supabase_headers(): array
{
    $key = flux_env('SUPABASE_SERVICE_KEY', flux_env('SUPABASE_ANON_KEY', ''));
    return [
        'Content-Type: application/json',
        'Accept: application/json',
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
    ];
}

function supabase_request(string $method, string $path, ?array $payload = null): ?array
{
    $base = rtrim((string) flux_env('SUPABASE_URL', ''), '/');
    $key = flux_env('SUPABASE_ANON_KEY', '');
    if ($base === '' || $key === '' || !function_exists('curl_init')) {
        return null;
    }

    $url = $base . '/rest/v1/' . ltrim($path, '/');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => supabase_headers(),
        CURLOPT_TIMEOUT => 8,
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($response === false || $status >= 400) {
        return null;
    }

    $decoded = json_decode((string) $response, true);
    return is_array($decoded) ? $decoded : null;
}

function flux_demo_profile(): array
{
    return [
        'id' => flux_env('FLUX_PAY_DEMO_PROFILE_ID', '00000000-0000-0000-0000-000000000000'),
        'full_name' => 'Official',
        'email' => 'subhajitmity92@gmail.com',
        'phone' => '1111222333',
        'balance' => 0.00,
        'joined_at' => '2026-05-31T00:00:00+00:00',
        'is_verified' => true,
        'api_token' => 'jOMM2wkce2Rhj5zVyDZTEoCZXVrDWD0c1QYymEs',
        'api_key' => '6Amq5Yw219Z5C1b4q',
        'api_enabled' => true,
        'api_calls' => 0,
        'api_last_used' => null,
    ];
}

function flux_demo_transactions(): array
{
    return [[
        'id' => 'STG-17802380454464ZBSY-D',
        'title' => 'Payment to Mk (223••••••41)',
        'amount' => -17.45,
        'status' => 'success',
        'method' => 'Transfer',
        'category' => 'transfer',
        'net_balance_after' => 0.00,
        'notes' => 'Single payment sent',
        'comment' => 'Single Payment',
        'created_at' => '2026-05-31T20:04:00+00:00',
    ], [
        'id' => 'STG-17802380454464CRDT-D',
        'title' => 'Wallet credit',
        'amount' => 17.00,
        'status' => 'success',
        'method' => 'UPI',
        'category' => 'credit',
        'net_balance_after' => 17.00,
        'notes' => 'Manual fund request approved',
        'comment' => 'Wallet Top-up',
        'created_at' => '2026-05-31T19:40:00+00:00',
    ]];
}

function flux_profile(): array
{
    $id = flux_env('FLUX_PAY_DEMO_PROFILE_ID', '00000000-0000-0000-0000-000000000000');
    $rows = supabase_request('GET', 'profiles?id=eq.' . rawurlencode($id) . '&select=*');
    return $rows[0] ?? flux_demo_profile();
}

function flux_transactions(string $profileId): array
{
    $rows = supabase_request('GET', 'transactions?profile_id=eq.' . rawurlencode($profileId) . '&select=*&order=created_at.desc');
    return $rows ?: flux_demo_transactions();
}

function flux_dashboard_stats(array $transactions): array
{
    $credit = 0.0;
    $success = 0;
    foreach ($transactions as $tx) {
        $amount = (float) ($tx['amount'] ?? 0);
        if ($amount > 0) {
            $credit += $amount;
        }
        if (($tx['status'] ?? '') === 'success') {
            $success++;
        }
    }
    $total = count($transactions);
    return [
        'credit' => $credit,
        'success' => $success,
        'total' => $total,
        'rate' => $total > 0 ? (int) round(($success / $total) * 100) : 0,
    ];
}

function flux_create_fund_request(string $profileId, float $amount, string $utr): bool
{
    $payload = [
        'profile_id' => $profileId,
        'amount' => $amount,
        'utr' => $utr,
        'status' => 'pending',
    ];
    return supabase_request('POST', 'fund_requests', $payload) !== null;
}
