<!-- generated-by: gsd-doc-writer -->
# DEVELOPMENT

## Local Setup

This project is a CodeIgniter 4 PHP application. To set up a local development environment:

1. **Prerequisites** — Install PHP 8.2 or higher, Composer, and a web server (Apache/Nginx or PHP's built-in server). Ensure the following PHP extensions are enabled: `intl`, `mbstring`, `json`, `mysqlnd` (if using MySQL), and `libcurl` (if using `HTTP\CURLRequest`).
2. **Fork and clone** the repository:
   ```bash
   git clone https://github.com/ramdanz14/zulfa-frozen.git
   cd zulfa-frozen
   ```
3. **Install dependencies**:
   ```bash
   composer install
   ```
4. **Configure environment** — Create a `.env` file and set `app.baseURL`, database credentials, and other app settings:
   <!-- VERIFY: Neither `env` nor `.env.example` exists at project root; the correct setup command could not be determined from the repository -->
   Edit `.env` and set at minimum `app.baseURL` to a local URL (e.g., `http://localhost:8080/`) and your database connection details.
5. **Point your web server** document root to the `public/` directory. `index.php` is inside `public/`, not the project root. For quick local testing:
   ```bash
   php spark serve
   ```
6. **Open** `http://localhost:8080` in your browser.

## Build Commands

This is a PHP project — there is no compilation or asset-build step. The key commands are:

| Command | Description |
|---|---|
| `composer install` | Install PHP dependencies (including dev deps like PHPUnit) |
| `php spark serve` | Start the built-in development server |
| `vendor\bin\phpunit` (Windows) | Run the full test suite |
| `./vendor/bin/phpunit` (POSIX) | Run the full test suite |
| `php spark make:migration \<name\>` | Generate a new database migration |
| `php spark make:controller \<name\>` | Generate a new controller |
| `php spark make:model \<name\>` | Generate a new model |
| `php spark make:view \<name\>` | Generate a new view |

The only Composer script defined is `"test": "phpunit"`, so you can also run `composer test` as a shorthand.

## Code Style

No dedicated PHP code style formatter or linter (PHP-CS-Fixer, PHP_CodeSniffer, etc.) is configured in this project. Contributors should follow the CodeIgniter 4 coding conventions and PSR-4 autoloading standards. Key patterns in this project:

- **Namespaces**: `App\` for application code, `Config\` for configuration classes (see `composer.json` autoload section).
- **Controllers**: placed in `app/Controllers/`, extend `BaseController`.
- **Models**: placed in `app/Models/`, extend `CodeIgniter\Model`.
- **Views**: placed in `app/Views/`, named with lowercase descriptive slugs.
- **Config classes**: placed in `app/Config/`, extend CodeIgniter `Config` base or are plain classes.
- **Helpers**: custom helpers live in `app/Helpers/`.

## Branch Conventions

- The default branch is **`main`**.
- A `production` branch exists on the remote (`origin/production`) for release deployments.
- No explicit branch-naming convention is documented in the project.
- Git commit messages are informal and use a mix of Indonesian and English (e.g., `tambah migration script`, `add setting data`, `implementasi harga jual grosir per customer`).

## PR Process

No formal PR template is defined in this repository (no `.github/PULL_REQUEST_TEMPLATE.md`). A `CONTRIBUTING.md` file exists at the project root. The project is open source on GitHub. When submitting a pull request:

1. Fork the repository on GitHub (`https://github.com/ramdanz14/zulfa-frozen`).
2. Create a feature branch off `main` with a descriptive name.
3. Make your changes and commit them with clear messages.
4. Push to your fork and open a Pull Request against the `main` branch on `ramdanz14/zulfa-frozen`.
5. Include a description of what changed and why.
6. Ensure tests pass (`./vendor/bin/phpunit`) before requesting review.

There is no CI/CD pipeline configured (no `.github/workflows/` directory exists), so all testing is done locally.
