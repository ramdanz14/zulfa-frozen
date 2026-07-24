# OpenCode agents

Only touch code, not framework core.

## Stack and layout
- Framework: CodeIgniter 4 appstarter, PHP 8.2+ required.
- HTTP entry: `public/index.php`.
- CLI entry: `spark` in repo root.
- App namespaces: `App\` and `Config\` from `app/`.
- Controllers: `app/Controllers/*.php`.
- Models: `app/Models/*.php`.
- Views: `app/Views/**`.
- Routes: `app/Config/Routes.php` (heavy custom routing). Default: `/` → `Main::index`.

## How to run
- Web: point webserver document root to `public/`. `index.php` not in repo root.
- Env: copy `env` → `.env`, set `app.baseURL`, DB, etc.
- PHP extensions: intl, mbstring, json, mysqlnd (if MySQL), libcurl (if HTTP\CURLRequest).

## Tests
- Composer dev deps include PHPUnit 10.x.
- Install deps: `composer install`.
- Run all tests (Windows): `vendor\bin\phpunit`.
- Run all tests (POSIX): `./phpunit` symlink or `vendor/bin/phpunit`.
- PHPUnit config: `phpunit.xml.dist` (tests under `tests/`, source `app/`).

## App-specific notes
- Business logic lives in `app/Controllers`, `app/Models`, `app/Views` (POS, stock, kas, hutang, konversi, poin member, etc.).
- Access control uses helper `cek_akses_menu()` and related helpers; keep calls consistent when adding endpoints.
- DB access often done via `Config\Database::connect()` inside controllers; follow existing pattern unless explicitly refactoring.
- Custom config `app/Config/WorkerMode.php` present; check there before changing any worker/background behavior.

## When editing
- New feature: add route in `app/Config/Routes.php`, controller in `app/Controllers`, model in `app/Models` if needed, views under feature dir in `app/Views`.
- Do not move `index.php` out of `public/`.
- Do not change `composer.json` constraints unless told.
- Keep namespace `App\Controllers` / `App\Models` consistent.
