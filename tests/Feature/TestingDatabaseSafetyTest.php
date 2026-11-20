<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\TestingDatabaseSafety;
use Tests\TestCase;

class TestingDatabaseSafetyTest extends TestCase
{
    public function test_accepts_only_the_current_verified_testing_connection(): void
    {
        TestingDatabaseSafety::assertSafe($this->app);

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->assertSame(':memory:', DB::connection()->getConfig('database'));
        } else {
            $this->assertSame('aksesibel_testing', DB::selectOne('SELECT DATABASE() AS name')->name);
        }
    }

    public function test_rejects_a_non_testing_application_before_any_reset(): void
    {
        $this->app->instance('env', 'local');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV harus testing');

        TestingDatabaseSafety::assertSafe($this->app);
    }

    public function test_rejects_the_work_database_name_without_connecting_to_it(): void
    {
        $config = config('database.connections.mysql');
        $config['database'] = 'aksesibel';
        $config['url'] = null;
        config(['database.connections.rejected_work' => $config, 'database.default' => 'rejected_work']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('hanya SQLite :memory: atau MySQL/MariaDB aksesibel_testing');

        TestingDatabaseSafety::assertSafe($this->app);
    }

    public function test_rejects_a_database_url_even_if_the_declared_database_looks_safe(): void
    {
        $config = config('database.connections.mysql');
        $config['database'] = 'aksesibel_testing';
        $config['url'] = 'mysql://127.0.0.1/aksesibel_testing';
        config(['database.connections.rejected_url' => $config, 'database.default' => 'rejected_url']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_URL harus kosong');

        TestingDatabaseSafety::assertSafe($this->app);
    }

    public function test_rejects_a_file_based_sqlite_database_without_creating_the_file(): void
    {
        $path = sys_get_temp_dir().'/aksesibel-rejected-'.bin2hex(random_bytes(6)).'.sqlite';
        $config = config('database.connections.sqlite');
        $config['database'] = $path;
        $config['url'] = null;
        config(['database.connections.rejected_file' => $config, 'database.default' => 'rejected_file']);

        try {
            TestingDatabaseSafety::assertSafe($this->app);
            $this->fail('Database SQLite berbasis file harus ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('hanya SQLite :memory:', $exception->getMessage());
            $this->assertFileDoesNotExist($path);
        }
    }

    public function test_rejects_a_different_effective_database_even_if_configuration_says_testing(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Requires real MySQL/MariaDB to verify the effective database.');
        }

        $pdo = DB::connection()->getPdo();
        $pdo->exec('USE information_schema');

        try {
            TestingDatabaseSafety::assertSafe($this->app);
            $this->fail('Target PDO yang berbeda harus ditolak sebelum reset.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('database efektif bukan aksesibel_testing', $exception->getMessage());
        } finally {
            $pdo->exec('USE aksesibel_testing');
        }

        $this->assertSame('aksesibel_testing', DB::selectOne('SELECT DATABASE() AS name')->name);
    }
}
