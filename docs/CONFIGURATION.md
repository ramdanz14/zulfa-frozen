<!-- generated-by: gsd-doc-writer -->

# Configuration

This document describes the configuration system for the zulfa-frozen CodeIgniter 4 application, including environment variables, config file locations, required settings, defaults, and per-environment overrides.

## Environment Variables

This project does not include a committed `.env` file or `env` template in the repository root. CodeIgniter 4 uses an `env` file that must be copied to `.env` before the application can be configured. See the "Quick Setup" section below.

The application does not read configuration values from environment variables (`getenv()` or `process.env`) directly. Instead, CI4's `Config` class loads settings from PHP config files in `app/Config/`. The `ENVIRONMENT` environment variable is the only one used by the framework internally to select the appropriate `Boot/` configuration file.

| Variable | Required | Default | Description |
|---|---|---|---|
| `ENVIRONMENT` | Optional | `development` | Determines which `app/Config/Boot/` file is loaded (`development.php`, `production.php`, or `testing.php`). Set in `.env` as `CI_ENVIRONMENT=development`. |
| `app.baseURL` | **Required** | `http://localhost:8080/` | The base URL of the site, including the trailing slash. Must be set in `.env` for the app to function correctly. |
| `database.default.hostname` | **Required** | `localhost` | Database server hostname. Set in `.env` as `database.default.hostname=localhost`. |
| `database.default.database` | **Required** | *(empty)* | Database name. Set in `.env` as `database.default.database=your_db`. |
| `database.default.username` | **Required** | *(empty)* | Database user. Set in `.env` as `database.default.username=root`. |
| `database.default.password` | **Required** | *(empty)* | Database password. Set in `.env` as `database.default.password=secret`. |
| `database.default.DBDriver` | Optional | `MySQLi` | Database driver (`MySQLi`, `Postgre`, `SQLite3`, `SQLSRV`, `OCI8`). |
| `database.default.port` | Optional | `3306` | Database server port. |
| `database.default.charset` | Optional | `utf8mb4` | Database character set. |
| `database.default.DBCollat` | Optional | `utf8mb4_general_ci` | Database collation. |

## Config File Format

Configuration is stored in PHP class files under `app/Config/`. Each file defines a class that extends `CodeIgniter\Config\BaseConfig` (or a framework base class like `CodeIgniter\Database\Config` for database settings). Properties are declared as public class properties and auto-wired by CI4's config system.

Key config files:

| File | Purpose |
|---|---|
| `app/Config/App.php` | Base URL, locale, timezone, charset, CSP, proxy settings |
| `app/Config/Database.php` | Default and test database connection groups |
| `app/Config/Routes.php` | All application routes |
| `app/Config/Routing.php` | Routing module configuration |
| `app/Config/Security.php` | CSRF protection settings |
| `app/Config/Session.php` | Session driver, cookie, and expiration settings |
| `app/Config/Logger.php` | Log threshold and file handler configuration |
| `app/Config/Email.php` | SMTP and mail protocol settings |
| `app/Config/Paths.php` | System, app, writable, and tests directory paths |
| `app/Config/WorkerMode.php` | FrankenPHP worker persistent services and GC settings |
| `app/Config/Boot/` | Environment-specific PHP settings (error display, debug mode) |

## Required Settings

The application will fail to operate correctly without the following settings configured:

1. **`app.baseURL`** — Set in `.env`. The app uses this for URL generation, redirects, and CSP headers. The default `http://localhost:8080/` is only suitable for local development.

2. **`database.default.hostname`**, **`database.default.database`**, **`database.default.username`** — Set in `.env`. The database connection defaults to an empty hostname, database, and username, which means the app cannot connect to a database until these are configured.

3. **`ENVIRONMENT`** — While not strictly required (it defaults to `development`), setting this correctly in `.env` is important because it controls error display (debug mode on in development, off in production) and the CSRF redirect-on-failure behavior.

## Defaults

The following default values are defined in the source code:

| Setting | Default | Location |
|---|---|---|
| `baseURL` | `http://localhost:8080/` | `app/Config/App.php:19` |
| `defaultLocale` | `id` | `app/Config/App.php:96` |
| `appTimezone` | `UTC` | `app/Config/App.php:136` |
| `charset` | `UTF-8` | `app/Config/App.php:148` |
| `DBDriver` | `MySQLi` | `app/Config/Database.php:33` |
| `DBDebug` | `true` | `app/Config/Database.php:36` |
| `DBPrefix` | *(empty)* | `app/Config/Database.php:34` |
| `port` | `3306` | `app/Config/Database.php:44` |
| `csrfProtection` | `cookie` | `app/Config/Security.php:18` |
| `session.driver` | `CodeIgniter\Session\Handlers\FileHandler` | `app/Config/Session.php:25` |
| `session.savePath` | `WRITEPATH . 'session'` | `app/Config/Session.php:61` |
| `session.expiration` | `7200` seconds (2 hours) | `app/Config/Session.php:44` |
| `CSRF cookie name` | `csrf_cookie_name` | `app/Config/Security.php:54` |
| `csrf expiration` | `7200` seconds | `app/Config/Security.php:65` |
| `log threshold (development)` | `9` (all messages) | `app/Config/Logger.php:42` |
| `log threshold (production)` | `4` (warnings and above) | `app/Config/Logger.php:42` |
| `protocol` | `mail` (PHP mail()) | `app/Config/Email.php:21` |
| `SMTP port` | `25` | `app/Config/Email.php:51` |

## Per-Environment Overrides

CodeIgniter 4 uses three environments: `development`, `production`, and `testing`. Each environment has a corresponding `Boot/` configuration file that controls error reporting and debug behavior.

### Setting the environment

Set `CI_ENVIRONMENT` in `.env`:

```ini
CI_ENVIRONMENT=development
```

### Environment-specific behavior

| Environment | Boot file | `display_errors` | `CI_DEBUG` | CSRF redirect-on-failure | Log threshold |
|---|---|---|---|---|---|
| `development` | `app/Config/Boot/development.php` | `1` (on) | `true` | No redirect | `9` (all) |
| `production` | `app/Config/Boot/production.php` | `0` (off) | `false` | Redirect to previous page | `4` (warnings+) |
| `testing` | `app/Config/Boot/testing.php` | `1` (on) | `true` | No redirect | Inherited from development |

### The `testing` environment

When `ENVIRONMENT` is `testing`, the `Database.php` constructor automatically switches the default connection group to `tests`, which uses an in-memory SQLite database (`:memory:`). This ensures tests don't affect live data. The `phpunit.xml.dist` sets `app.baseURL` to `http://example.com/` and defines test-specific database connection parameters (commented out by default).

### How environment detection works

1. CI4 reads `CI_ENVIRONMENT` from `.env` (or the server environment).
2. The value determines which `Boot/` file is loaded at boot time.
3. The `Boot/` file defines `CI_DEBUG`, `SHOW_DEBUG_BACKTRACE`, error reporting level, and `display_errors`.
4. The `Logger.php` config checks `ENVIRONMENT` at runtime to set the log threshold.
5. The `Security.php` config checks `ENVIRONMENT` at runtime to control CSRF redirect-on-failure behavior.

## Quick Setup

To configure the application for the first time:

1. Copy the CI4 `env` file to `.env` in the project root. The `env` file is included in the CodeIgniter4 framework package at `vendor/codeigniter4/framework/env`. Run: `copy vendor\codeigniter4\framework\env .env` (Windows) or `cp vendor/codeigniter4/framework/env .env` (POSIX).
2. Open `.env` and set `CI_ENVIRONMENT=development` (or `production`).
3. Edit `app.baseURL` in `.env` to match your site's base URL (e.g., `http://localhost/zulfa-frozen/`).
4. Configure database settings in `.env` (`database.default.hostname`, `database.default.database`, `database.default.username`, `database.default.password`).
5. Ensure the `writable/` directory is writable by the web server.

## Additional Configuration

For advanced configuration options, refer to the CodeIgniter 4 documentation at [https://codeigniter.com/user_guide/](https://codeigniter.com/user_guide/). The complete list of configurable system properties is in `app/Config/` — each file's docblock comments document all available settings and their defaults.
