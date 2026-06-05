# Flux Pay

Flux Pay is a mobile-first payment gateway demo inspired by the supplied Ultra Pay screens. It uses PHP for the app shell and Supabase REST APIs for backend storage/auth-friendly data access.

## Features

- Login and 3-step registration flows with Flux Pay branding
- Dashboard cards for balance, credits, successful transactions, total transactions, and success rate
- Add-funds flow with UPI/manual payment and UTR confirmation
- API integration page with API status, credentials, endpoint builder, and test request UI
- Profile settings and transaction receipt modal/page styling
- Supabase-ready PHP backend helpers with local fallback demo data

## Supabase setup

Create these tables in Supabase (or adapt the queries in `lib/supabase.php`):

- `profiles`: `id`, `full_name`, `email`, `phone`, `balance`, `joined_at`, `is_verified`, `api_token`, `api_key`, `api_enabled`, `api_calls`, `api_last_used`
- `transactions`: `id`, `profile_id`, `amount`, `status`, `method`, `category`, `notes`, `comment`, `net_balance_after`, `created_at`
- `fund_requests`: `id`, `profile_id`, `amount`, `utr`, `status`, `created_at`

Copy `.env.example` to `.env` and add your Supabase credentials:

```bash
cp .env.example .env
```

Then run locally:

```bash
php -S 127.0.0.1:8080 -t public
```

Open <http://127.0.0.1:8080>.
