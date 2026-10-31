<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if (! $app->environment('testing')
            || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:'
            || $app['config']->get('database.connections.sqlite.url')) {
            throw new RuntimeException('Tes dibatalkan: gunakan APP_ENV=testing dan SQLite :memory: tanpa DB_URL. Database kerja tidak boleh direset.');
        }

        return $app;
    }
}
