<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Databases the test suite is allowed to migrate / refresh against.
     *
     * Anything else (most importantly laravel_db, the dev database) is
     * rejected before RefreshDatabase can run `migrate:fresh`.
     */
    protected array $allowedTestDatabases = ['student_reg_test', 'student_reg_dusk'];

    /**
     * Boot the application, assert we are pointed at a test database, and
     * only THEN let the parent run the trait setup (RefreshDatabase, which
     * wipes + migrates). Booting first makes config() available; running the
     * guard before parent::setUp() means a misconfigured run aborts before
     * any destructive migrate:fresh can touch the dev database.
     */
    protected function setUp(): void
    {
        $this->refreshApplication();
        $this->guardAgainstNonTestDatabase();

        parent::setUp();
    }

    protected function guardAgainstNonTestDatabase(): void
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        // In-memory SQLite is always safe (nothing persistent to destroy).
        $isInMemorySqlite = $connection === 'sqlite'
            && in_array($database, [':memory:', null], true);

        if ($isInMemorySqlite || in_array($database, $this->allowedTestDatabases, true)) {
            return;
        }

        throw new \RuntimeException(sprintf(
            "Refusing to run migrations against database '%s'. The test suite may only ".
            'touch: %s. Check that phpunit.xml pins DB_DATABASE with force="true".',
            $database ?? '(null)',
            implode(', ', $this->allowedTestDatabases),
        ));
    }
}
