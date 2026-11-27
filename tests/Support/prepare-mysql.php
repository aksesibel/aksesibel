<?php

use Illuminate\Contracts\Console\Kernel;
use Tests\Support\TestingDatabaseSafety;

$app = require __DIR__.'/bootstrap-mysql.php';

try {
    $config = $app['config']->get('database.connections.mysql');
    $config['database'] = 'information_schema';
    $config['url'] = null;
    $app['config']->set('database.connections.testing_setup', $config);
    $setup = $app['db']->connection('testing_setup');
    $exists = $setup->selectOne("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = 'aksesibel_testing'");

    if (! $exists) {
        $setup->statement('CREATE DATABASE `aksesibel_testing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    TestingDatabaseSafety::assertSafe($app);
    $connection = $app['db']->connection();
    $metadata = $connection->selectOne('SELECT DATABASE() AS database_name, VERSION() AS server_version, @@collation_database AS collation');
    echo json_encode(['safety' => 'verified', 'database' => $metadata->database_name, 'server' => $metadata->server_version, 'collation' => $metadata->collation]), PHP_EOL;

    $kernel = $app->make(Kernel::class);
    $exit = $kernel->call('migrate', ['--force' => true, '--no-interaction' => true]);

    if ($exit !== 0) {
        throw new RuntimeException('Migration database testing gagal; output SQL sensitif tidak ditampilkan.');
    }

    echo json_encode(['testing_migrations' => $connection->table('migrations')->count(), 'work_database' => 'untouched']), PHP_EOL;
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, json_encode(['setup' => 'failed', 'exception' => get_class($exception), 'code' => (string) $exception->getCode()]).PHP_EOL);
    exit(1);
}
