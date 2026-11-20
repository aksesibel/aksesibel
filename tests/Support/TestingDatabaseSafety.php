<?php

namespace Tests\Support;

use Illuminate\Foundation\Application;
use RuntimeException;

class TestingDatabaseSafety
{
    public static function assertSafe(Application $app): void
    {
        $config = $app['config'];
        $name = $config->get('database.default');
        $connection = $app['db']->connection($name);

        if (! $app->environment('testing')
            || $config->get('database.connections.'.$name.'.url')
            || $connection->getConfig('url')) {
            throw new RuntimeException('Tes dibatalkan: APP_ENV harus testing dan DB_URL harus kosong.');
        }

        if ($connection->getDriverName() === 'sqlite'
            && $connection->getConfig('database') === ':memory:') {
            return;
        }

        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)
            || $connection->getConfig('database') !== 'aksesibel_testing') {
            throw new RuntimeException('Tes dibatalkan: hanya SQLite :memory: atau MySQL/MariaDB aksesibel_testing yang diizinkan.');
        }

        $effective = $connection->selectOne('SELECT DATABASE() AS database_name');

        if ($effective?->database_name !== 'aksesibel_testing') {
            throw new RuntimeException('Tes dibatalkan: database efektif bukan aksesibel_testing. Database kerja dilindungi.');
        }
    }
}
