<?php

use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Tests\Support\TestingDatabaseSafety;

$root = dirname(__DIR__, 2);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicFile = realpath($root.'/public'.rawurldecode($uri));

if ($uri !== '/' && $publicFile && str_starts_with($publicFile, realpath($root.'/public').DIRECTORY_SEPARATOR) && is_file($publicFile)) {
    return false;
}

$app = require __DIR__.'/bootstrap-mysql.php';
TestingDatabaseSafety::assertSafe($app);
header('X-PBI-Testing-Console: '.($app->runningInConsole() ? 'yes' : 'no'));
header('X-PBI-Testing-Fetch-Site: '.($_SERVER['HTTP_SEC_FETCH_SITE'] ?? 'absent'));

if ($uri === '/__pbi-testing/health') {
    header('Content-Type: application/json');
    echo json_encode(['environment' => 'testing', 'database' => $app['db']->connection()->selectOne('SELECT DATABASE() AS name')->name]);

    return;
}

$app->make(Vite::class)->useHotFile($app->storagePath('framework/testing/no-vite-hot'));
$app->handleRequest(Request::capture());
