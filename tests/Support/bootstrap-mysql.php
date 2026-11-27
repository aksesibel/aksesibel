<?php

use Illuminate\Contracts\Console\Kernel;

/**
 * Isolated CLI/browser test runtime. Credentials are read by Laravel from the
 * local environment, while database/session/cache targets are forced here.
 * This file never edits .env or the normal application's configuration.
 */

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$testingEnvironment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://127.0.0.1:8876',
    'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
    'APP_CONFIG_CACHE' => dirname(__DIR__, 2).'/bootstrap/cache/config.pbi-testing.php',
    'DB_CONNECTION' => 'mysql',
    'DB_DATABASE' => 'aksesibel_testing',
    'DB_URL' => '',
    'BCRYPT_ROUNDS' => '4',
    'CACHE_STORE' => 'database',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BROADCAST_CONNECTION' => 'null',
    'SESSION_DRIVER' => 'database',
    'SESSION_CONNECTION' => 'mysql',
    'SESSION_COOKIE' => 'aksesibel_pbi_testing_session',
    'SESSION_DOMAIN' => 'null',
    'SESSION_SECURE_COOKIE' => 'false',
];

foreach ($testingEnvironment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

return $app;
