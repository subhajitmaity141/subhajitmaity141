<?php

declare(strict_types=1);

function flux_load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

flux_load_env(dirname(__DIR__) . '/.env');

function flux_env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function flux_money(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

function flux_mask_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?: $phone;
    if (strlen($digits) <= 4) {
        return $digits;
    }
    return substr($digits, 0, 3) . '••••••' . substr($digits, -2);
}

function flux_mask_email(string $email): string
{
    if (!str_contains($email, '@')) {
        return $email;
    }
    [$name, $domain] = explode('@', $email, 2);
    return substr($name, 0, 3) . '***@' . $domain;
}

function flux_date(string $date): string
{
    $timestamp = strtotime($date) ?: time();
    return date('d M Y', $timestamp);
}
