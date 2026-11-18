<?php

use App\Models\User;
use App\Services\UserManagement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\TestingDatabaseSafety;

ini_set('display_errors', '0');
require dirname(__DIR__, 2).'/vendor/autoload.php';

$payload = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$directory = $payload['directory'];
$key = $payload['key'];

/** @param array<string, int|string> $data */
function publishWorkerResult(string $path, array $data): void
{
    file_put_contents($path.'.tmp', json_encode($data, JSON_THROW_ON_ERROR));
    rename($path.'.tmp', $path);
}

try {
    if (! in_array($key, ['a', 'b'], true)
        || ! in_array($payload['operation'], ['demote_self', 'suspend_other'], true)
        || ! is_dir($directory)) {
        throw new RuntimeException('Invalid concurrency worker payload.');
    }

    $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    TestingDatabaseSafety::assertSafe($app);

    if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
        throw new RuntimeException('Concurrency workers require the isolated MySQL/MariaDB testing database.');
    }

    DB::statement('SET SESSION innodb_lock_wait_timeout = 8');
    $actor = User::with('role')->findOrFail((int) $payload['actor_id']);
    $target = User::findOrFail((int) $payload['target_id']);
    $connectionId = (int) DB::selectOne('SELECT CONNECTION_ID() AS connection_id')->connection_id;
    publishWorkerResult($directory.'/'.$key.'.ready', ['pid' => getmypid(), 'connection_id' => $connectionId]);
    $deadline = microtime(true) + 10;

    while (! is_file($directory.'/go')) {
        if (microtime(true) >= $deadline) {
            throw new RuntimeException('Coordinator barrier timed out.');
        }

        clearstatcache();
        usleep(10_000);
    }

    file_put_contents($directory.'/'.$key.'.attempting', 'attempting');
    $data = $payload['operation'] === 'demote_self'
        ? ['role_id' => (int) $payload['public_role_id']]
        : ['account_status' => 'suspended'];

    try {
        $app->make(UserManagement::class)->update($actor, $target, $data);
        $result = ['outcome' => 'succeeded'];
    } catch (ValidationException) {
        $result = ['outcome' => 'rejected_validation'];
    } catch (AuthorizationException) {
        $result = ['outcome' => 'rejected_authorization'];
    }
} catch (Throwable $exception) {
    $result = ['outcome' => 'failed', 'exception' => $exception::class, 'code' => $exception->getCode()];
}

publishWorkerResult($directory.'/'.$key.'.complete', $result);
fwrite(STDOUT, json_encode($result, JSON_THROW_ON_ERROR).PHP_EOL);
exit($result['outcome'] === 'failed' ? 1 : 0);
