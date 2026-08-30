<!-- generated-by: gsd-doc-writer -->
# ARCHITECTURE

## System Overview

**Zulfa Frozen** is a CodeIgniter 4 PHP web application for point-of-sale (POS), inventory management, and accounting. It serves as a multi-store management platform where users can process sales, purchases, inventory transfers, absensi (attendance), kas (cash journal), closing entries, and generate financial reports. The application follows the traditional MVC pattern with CodeIgniter 4 conventions, uses MySQL as the primary database, and relies on session-based authentication. The HTTP entry point is `public/index.php`, and CLI commands are accessible via the `spark` root-level script.

## Component Diagram

```
graph TD
    A[public/index.php] --> B[CodeIgniter Bootstrap]
    B --> C{Routes.php}
    C --> D[Controllers]
    D --> E[Models]
    D --> F[Views]
    D --> G[Helpers]
    E --> H[(MySQL Database)]
    F --> I[Layout Partials]
    B --> J[AuthFilter]
    C --> K[CLI Commands]
    K --> L[Migrations]
    L --> H
    G --> H
```

## Data Flow

1. **Request arrives** at `public/index.php`, which bootstraps the CodeIgniter framework via `Boot::bootWeb()`.
2. **Routing** is handled by `app/Config/Routes.php`, which maps URLs to controller methods using `$routes->group()` with RESTful conventions (`get`, `post`, `put`, `patch`, `delete`).
3. **AuthFilter** (registered as a required `before` filter) runs on every request, checking `session()->username` and redirecting unauthenticated users to `/login`.
4. **Controller** receives the request, instantiates the relevant Model(s) via `new ModelName()`, and either:
   - Renders a view via `view('view_name', $data)` for server-side HTML pages, or
   - Returns JSON via `$this->response->setJSON(...)` for AJAX endpoints.
5. **Model** extends `CodeIgniter\Model` and performs database operations using Query Builder or raw SQL queries.
6. **Helpers** (`app/Helpers/custom_helper.php`) provide shared utility functions like `cek_akses_menu()` (menu access check), `tracelog()` (audit logging), `HitungStock()` (stock calculation), and `HitungSpd()` (SPD calculation).
7. **Response** travels back through the framework's response pipeline, passing through `after` filters (performance metrics, debug toolbar) before reaching the client.

## Key Abstractions

| Abstraction | File | Description |
|---|---|---|
| BaseController | `app/Controllers/BaseController.php` | Abstract base class all controllers extend; extends `CodeIgniter\Controller` |
| AuthFilter | `app/Filters/AuthFilter.php` | Required before-filter that enforces session-based authentication |
| MainModel | `app/Models/MainModel.php` | Dashboard aggregation model combining sales, cash, receivable, payable data |
| UserModel | `app/Models/UserModel.php` | User/employee CRUD with timestamps, auto-set `updid` from session |
| JualModel | `app/Models/JualModel.php` | POS sales processing model (~73K, largest model) |
| custom_helper | `app/Helpers/custom_helper.php` | Shared functions: `cek_akses_menu`, `tracelog`, `HitungStock`, `HitungSpd`, `GetConst` |
| Routes.php | `app/Config/Routes.php` | Central routing configuration with 30+ route groups and RESTful CRUD mappings |
| WorkerMode.php | `app/Config/WorkerMode.php` | FrankenPHP worker-mode config specifying persistent services and GC behavior |
| Database.php | `app/Config/Database.php` | MySQL/MySQLi default connection config with `DBPrefix`, charset, and failover groups |
| App.php | `app/Config/App.php` | Base site URL, index file config, URI protocol, and app-level settings |

## Directory Structure Rationale

```
zulfa-frozen/
├── public/                  # Web root (document root for webserver)
│   ├── index.php            # Front controller; bootstraps the application
│   ├── .htaccess            # Apache URL rewriting to index.php
│   └── assets/              # Static assets (CSS, JS, fonts, images, libs)
├── app/                      # Application namespace root
│   ├── Controllers/          # HTTP request handlers (40+ controllers)
│   ├── Models/               # Data access layer extending CodeIgniter Model (35+ models)
│   ├── Views/                # PHP template files (38 feature dirs + layouts/ = 39 dirs)
│   │   └── layouts/          # Shared view partials (base.php, sidebar.php, topbar.php)
│   ├── Config/               # Application configuration classes
│   │   ├── Routes.php        # Central routing (heavy custom routing)
│   │   ├── Filters.php       # Filter aliases and global filter registration
│   │   ├── Database.php      # DB connection config (MySQLi)
│   │   ├── App.php           # Base URL, index page, URI protocol
│   │   └── WorkerMode.php    # FrankenPHP worker persistent services
│   ├── Helpers/              # Shared helper functions (custom_helper.php ~357 lines)
│   ├── Filters/              # Custom filter classes (AuthFilter, BelumLogin, SudahLogin)
│   ├── Commands/             # CLI commands (ClosingAuto, StockSpd)
│   ├── Database/
│   │   ├── Migrations/       # Schema versioning (~30 migration files)
│   │   └── Seeds/            # Seed data (currently empty, .gitkeep)
│   └── ThirdParty/           # Third-party integrations (currently .gitkeep)
├── writable/                 # Writable directories (logs, cache, session, uploads, debugbar)
├── tests/                    # PHPUnit test suite
│   ├── unit/                 # Unit tests
│   ├── database/             # Database tests
│   ├── session/              # Session tests
│   └── _support/             # Test support classes
├── docs/                     # Project documentation (GSD-generated)
├── vendor/                   # Composer-installed dependencies
└── composer.json             # PHP project definition (CodeIgniter 4 framework ^4.7, PHP ^8.2)
```

- **`public/`** is the web root to prevent direct access to application logic. `index.php` is the only entry point for web requests.
- **`app/`** follows CodeIgniter 4 conventions: Controllers handle HTTP, Models handle persistence, Views handle presentation. The `Config/` directory holds all application configuration rather than using `.env` alone.
- **`app/Helpers/custom_helper.php`** is the primary shared utilities file — it is loaded globally and contains both database query helpers (e.g., `GetClosingDateByToko`, `GetAkseMenu`) and business logic (e.g., `HitungStock`, `HitungSpd`).
- **`app/Views/layouts/`** contains shared page chrome (sidebar, topbar, base layout) used by all feature views.
- **`writable/`** is separated from `app/` and `public/` for security and is Gitignored.
- **`app/Database/Migrations/`** uses timestamped filenames (`YYYY-MM-DD-HHMMSS_Description.php`) following CodeIgniter 4 migration conventions.