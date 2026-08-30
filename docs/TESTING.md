<!-- generated-by: gsd-doc-writer -->
# Testing

## Test framework and setup

This project uses **PHPUnit 10** (`phpunit/phpunit: ^10.5.16`) for testing, installed as a Composer dev dependency.

**Required setup:**

1. Install dependencies:
   ```bash
   composer install
   ```
2. Ensure PHP 8.2 or higher is available (`php -v`).
3. Copy the PHPUnit configuration from the distribution file (a `phpunit.xml` is git-ignored so each developer can customize it):
   ```bash
   cp phpunit.xml.dist phpunit.xml
   ```
4. (Optional) For code coverage, install [XDebug](https://xdebug.org/docs/install) and set `xdebug.mode=coverage` in your `php.ini`.

**Configuration file:** `phpunit.xml.dist` in the project root. It sets:
- Bootstrap: `vendor/codeigniter4/framework/system/Test/bootstrap.php`
- Test suite source: `./app` (excluding `Views` and `Routes.php`)
- Test directories: `./tests`
- Coverage output: Clover XML to `build/logs/clover.xml`, HTML to `build/logs/html/`, serialized PHP to `build/logs/coverage.serialized`, text to `php://stdout`
- Server variables: `app.baseURL = http://example.com/`

## Running tests

### All tests

```bash
# Windows
vendor\bin\phpunit

# POSIX / macOS / Linux
./vendor/bin/phpunit
```

### Single test directory

```bash
# Run only unit tests
vendor\bin\phpunit tests/unit

# Run only database tests
vendor\bin\phpunit tests/database
```

### Single test file

```bash
vendor\bin\phpunit tests/unit/HealthTest.php
```

### With code coverage

```bash
vendor\bin\phpunit --colors --coverage-text=build/coverage.txt --coverage-html=build/coverage -d memory_limit=1024m
```

HTML coverage reports are available at `build/coverage/index.html`.

## Writing new tests

### File naming convention

Test files live under `tests/` and follow the `*Test.php` naming pattern. The directory structure under `tests/` should reflect the type of test:

| Directory | Purpose |
|-----------|---------|
| `tests/unit/` | Business logic and utility tests (no database) |
| `tests/database/` | Tests that exercise the database (migrations, seeds, models) |
| `tests/session/` | Session-related tests |

### Test case base class

All tests extend `CodeIgniter\Test\CIUnitTestCase`:

```php
use CodeIgniter\Test\CIUnitTestCase;

final class MyTest extends CIUnitTestCase
{
    public function testSomething(): void
    {
        $this->assertTrue($something);
    }
}
```

### Test method naming

Test methods must start with `test` and use descriptive names:
- `testUserCanLogin()` — clear, intent-revealing name
- `testModelFindAllReturnsThreeRows()` — describes expected result

### Database tests

For tests that need database setup, use the `DatabaseTestTrait` and specify a seed class:

```php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class MyDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed = \Tests\Support\Database\Seeds\ExampleSeeder::class;

    public function testModelFindAll(): void
    {
        $model = new ExampleModel();
        $this->assertCount(3, $model->findAll());
    }
}
```

### Test support helpers

The `tests/_support/` directory contains reusable test fixtures:
- `_support/Models/ExampleModel.php` — example model for database tests
- `_support/Libraries/ConfigReader.php` — helper for reading config values in tests
- `_support/Database/Seeds/ExampleSeeder.php` — example database seeder
- `_support/Database/Migrations/` — example migrations for test database setup

## Coverage requirements

No coverage thresholds are configured in `phpunit.xml.dist`. The coverage reporting is set up to output results but does not enforce minimum line, branch, function, or statement coverage. Coverage is informational only — no CI gate blocks on coverage percentages.

| Type | Threshold |
|------|-----------|
| Lines | N/A |
| Branches | N/A |
| Functions | N/A |
| Statements | N/A |

## CI integration

No CI/CD pipeline was detected in this repository — there is no `.github/workflows/` directory or equivalent CI configuration file. To add CI-based test execution, create a workflow file that runs the test command (`vendor\bin\phpunit` or `./vendor/bin/phpunit`) on push and pull request events.
