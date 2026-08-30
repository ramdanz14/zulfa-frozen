# Getting Started

This guide walks you through setting up the Zulfa Frozen development environment from scratch.

## Prerequisites

Before you begin, ensure you have the following installed:

| Tool | Minimum Version |
|------|-----------------|
| PHP | 8.2 or higher |
| Composer | 2.x |
| Web server | Apache (with `mod_rewrite`) or Nginx |
| Database | MySQL 5.7+ (MySQLi driver) |

Required PHP extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `curl`.

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/<org>/zulfa-frozen.git
cd zulfa-frozen
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure the environment

Create a `.env` file in the project root with your local settings. (CodeIgniter 4 appstarter projects typically include an `env` template; if one is present, copy it with `cp env .env`.)

At minimum, set the following in `.env`:

```ini
app.baseURL=http://localhost:8080/
database.default.hostname=localhost
database.default.database=zulfa_frozen
database.default.username=root
database.default.password=your_password
```

### 4. Create the database

Create an empty MySQL database and ensure the credentials in `.env` match. Zulfa Frozen uses the `MySQLi` driver by default. Run migrations to set up the schema:

```bash
php spark migrate
```

## First Run

Start the development server:

```bash
php spark serve
```

Open your browser and navigate to `http://localhost:8080`. The default route maps to the `Main::index` controller.

## Common Setup Issues

| Problem | Solution |
|---------|----------|
| **PHP version too low** | Zulfa Frozen requires PHP 8.2+. Update your PHP installation or use a version manager like `phpbrew` or `xampp`. |
| **Database connection fails** | Verify that MySQL is running and that the `database.default.*` settings in `.env` match your database credentials. The app cannot connect until these are configured. |
| **.`env` file not created** | The application requires a `.env` file copied from the `env` template. Without it, CodeIgniter 4 falls back to defaults that will not work for a real deployment. |
| **`mod_rewrite` not enabled (Apache)** | The `.htaccess` file in `public/` requires Apache's `mod_rewrite` module. Enable it with `a2enmod rewrite` and restart Apache. |

## Next Steps

- See [DEVELOPMENT.md](DEVELOPMENT.md) for local development setup, build commands, and code style guidelines.
- See [TESTING.md](TESTING.md) for running and writing tests with PHPUnit.
- See [ARCHITECTURE.md](ARCHITECTURE.md) for an overview of the system's components and data flow.
- See [CONFIGURATION.md](CONFIGURATION.md) for all environment variables and configuration options.
