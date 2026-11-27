<?php

namespace Tests\Concurrency;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Support\TestingDatabaseSafety;
use Tests\TestCase;

class UserManagementConcurrencyTest extends TestCase
{
    public function test_simultaneous_self_demotions_leave_one_authorized_active_manager(): void
    {
        $this->runRace('demote_self', function (array $results, array $users, Role $managerRole, Role $publicRole): void {
            $outcomes = array_column($results, 'outcome');
            sort($outcomes);

            $this->assertSame(['rejected_validation', 'succeeded'], $outcomes);
            $this->assertSame(1, User::whereKey($users)->where('role_id', $managerRole->getKey())->where('account_status', 'active')->count());
            $this->assertSame(1, User::whereKey($users)->where('role_id', $publicRole->getKey())->count());
            $this->assertSame(2, User::whereKey($users)->where('account_status', 'active')->count());
        });
    }

    public function test_simultaneous_cross_suspensions_reauthorize_the_losing_actor(): void
    {
        $this->runRace('suspend_other', function (array $results, array $users, Role $managerRole, Role $publicRole): void {
            $outcomes = array_column($results, 'outcome');
            sort($outcomes);

            $this->assertSame(['rejected_authorization', 'succeeded'], $outcomes);
            $this->assertSame(1, User::whereKey($users)->where('role_id', $managerRole->getKey())->where('account_status', 'active')->count());
            $this->assertSame(1, User::whereKey($users)->where('account_status', 'suspended')->count());
            $this->assertSame(2, User::whereKey($users)->where('role_id', $managerRole->getKey())->count());
        });
    }

    /**
     * @param  callable(array<int, array<string, mixed>>, array<int, int>, Role, Role): void  $assertResults
     */
    private function runRace(string $operation, callable $assertResults): void
    {
        TestingDatabaseSafety::assertSafe($this->app);
        $this->assertContains(DB::connection()->getDriverName(), ['mysql', 'mariadb']);
        $this->assertSame(0, DB::transactionLevel(), 'Concurrency fixtures must be committed, outside test transactions.');

        if (! function_exists('proc_open')) {
            $this->fail('Concurrency tests require proc_open so workers use independent PHP processes and database connections.');
        }

        $existingManagerRoleIds = Role::all()->whereStrict('role_name', Role::FACILITY_MANAGER)->modelKeys();
        $this->assertSame(0, User::whereIn('role_id', $existingManagerRoleIds)->where('account_status', 'active')->count(), 'Run concurrency tests alone on an isolated, empty testing database.');

        $directory = sys_get_temp_dir().'/aksesibel-concurrency-'.bin2hex(random_bytes(8));
        $this->assertTrue(mkdir($directory, 0700));
        $roleIds = [];
        $userIds = [];
        $workers = [];

        try {
            $managerRole = Role::create(['role_name' => Role::FACILITY_MANAGER]);
            $roleIds[] = $managerRole->getKey();
            $publicRole = Role::create(['role_name' => Role::PUBLIC]);
            $roleIds[] = $publicRole->getKey();
            $first = User::factory()->create(['role_id' => $managerRole->getKey(), 'account_status' => 'active']);
            $userIds[] = $first->getKey();
            $second = User::factory()->create(['role_id' => $managerRole->getKey(), 'account_status' => 'active']);
            $userIds[] = $second->getKey();

            DB::beginTransaction();
            Role::orderBy('role_id')->lockForUpdate()->get();

            foreach (['a' => [$first, $second], 'b' => [$second, $first]] as $key => [$actor, $other]) {
                $workers[$key] = $this->startWorker([
                    'key' => $key,
                    'directory' => $directory,
                    'operation' => $operation,
                    'actor_id' => $actor->getKey(),
                    'target_id' => $operation === 'demote_self' ? $actor->getKey() : $other->getKey(),
                    'public_role_id' => $publicRole->getKey(),
                ]);
            }

            $this->awaitFiles($directory, ['a.ready', 'b.ready']);
            $firstReady = json_decode(file_get_contents($directory.'/a.ready'), true, flags: JSON_THROW_ON_ERROR);
            $secondReady = json_decode(file_get_contents($directory.'/b.ready'), true, flags: JSON_THROW_ON_ERROR);
            $connectionIds = [(int) $firstReady['connection_id'], (int) $secondReady['connection_id']];
            $this->assertNotSame($connectionIds[0], $connectionIds[1]);
            $this->assertNotSame((int) $firstReady['pid'], (int) $secondReady['pid']);
            $this->assertSame(2, file_put_contents($directory.'/go', 'go'));
            $this->awaitFiles($directory, ['a.attempting', 'b.attempting']);
            $this->awaitDatabaseLockWaits($directory, $connectionIds);
            $this->assertFileDoesNotExist($directory.'/a.complete');
            $this->assertFileDoesNotExist($directory.'/b.complete');

            DB::commit();
            $this->awaitFiles($directory, ['a.complete', 'b.complete']);
            $results = [];

            foreach (['a', 'b'] as $key) {
                $result = json_decode(file_get_contents($directory.'/'.$key.'.complete'), true, flags: JSON_THROW_ON_ERROR);
                $this->assertNotSame('failed', $result['outcome'], 'Worker failed: '.($result['exception'] ?? 'unknown'));
                $results[] = $result;
            }

            $assertResults($results, $userIds, $managerRole, $publicRole);
        } finally {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            foreach ($workers as $worker) {
                $this->closeWorker($worker);
            }

            TestingDatabaseSafety::assertSafe($this->app);
            User::whereKey($userIds)->delete();
            Role::whereKey($roleIds)->delete();

            foreach (glob($directory.'/*') ?: [] as $file) {
                unlink($file);
            }

            rmdir($directory);
        }
    }

    /**
     * @param  array<string, int|string>  $payload
     * @return array{process: resource, stdout: resource, stderr: resource}
     */
    private function startWorker(array $payload): array
    {
        $connection = config('database.connections.'.config('database.default'));
        $environment = array_merge(getenv(), [
            'APP_ENV' => 'testing',
            'APP_KEY' => (string) config('app.key'),
            'DB_CONNECTION' => (string) config('database.default'),
            'DB_DATABASE' => (string) $connection['database'],
            'DB_URL' => '',
            'DB_HOST' => (string) $connection['host'],
            'DB_PORT' => (string) $connection['port'],
            'DB_USERNAME' => (string) $connection['username'],
            'DB_PASSWORD' => (string) $connection['password'],
            'DB_SOCKET' => (string) ($connection['unix_socket'] ?? ''),
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ]);
        $process = proc_open([PHP_BINARY, base_path('tests/Support/concurrent-user-update.php')], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path(), $environment);

        if (! is_resource($process)) {
            throw new RuntimeException('Unable to start an independent concurrency worker.');
        }

        fwrite($pipes[0], json_encode($payload, JSON_THROW_ON_ERROR));
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        return ['process' => $process, 'stdout' => $pipes[1], 'stderr' => $pipes[2]];
    }

    /** @param array<int, string> $files */
    private function awaitFiles(string $directory, array $files): void
    {
        $deadline = microtime(true) + 10;

        do {
            clearstatcache();

            foreach (['a', 'b'] as $key) {
                $completeFile = $directory.'/'.$key.'.complete';

                if (is_file($completeFile)) {
                    $result = json_decode(file_get_contents($completeFile), true, flags: JSON_THROW_ON_ERROR);

                    if ($result['outcome'] === 'failed') {
                        $this->fail('Concurrency worker failed: '.($result['exception'] ?? 'unknown'));
                    }
                }
            }

            if (collect($files)->every(fn (string $file): bool => is_file($directory.'/'.$file))) {
                return;
            }

            usleep(10_000);
        } while (microtime(true) < $deadline);

        $this->fail('Concurrency worker did not reach the expected barrier: '.implode(', ', $files));
    }

    /** @param array<int, int> $connectionIds */
    private function awaitDatabaseLockWaits(string $directory, array $connectionIds): void
    {
        $observerName = 'mysql_concurrency_observer';
        $configurationKey = 'database.connections.'.$observerName;
        $previousConfiguration = config($configurationKey);
        config([$configurationKey => DB::connection()->getConfig()]);
        DB::purge($observerName);
        $observer = DB::connection($observerName);
        $this->assertSame('aksesibel_testing', $observer->selectOne('SELECT DATABASE() AS database_name')->database_name);
        $deadline = microtime(true) + 5;
        $lastStates = [];

        try {
            do {
                foreach (['a', 'b'] as $key) {
                    $completeFile = $directory.'/'.$key.'.complete';
                    clearstatcache(true, $completeFile);

                    if (is_file($completeFile)) {
                        $result = json_decode(file_get_contents($completeFile), true, flags: JSON_THROW_ON_ERROR);
                        $this->fail('Worker completed before the coordinator released its row lock: '.json_encode($result, JSON_THROW_ON_ERROR));
                    }
                }

                $lastStates = $observer->select('SELECT trx_mysql_thread_id, trx_state FROM information_schema.INNODB_TRX WHERE trx_mysql_thread_id IN (?, ?)', $connectionIds);
                $waiting = collect($lastStates)->where('trx_state', 'LOCK WAIT');

                if ($waiting->count() === 2) {
                    $this->assertCount(2, $waiting);

                    return;
                }

                usleep(20_000);
            } while (microtime(true) < $deadline);

            $this->fail('Both workers must enter InnoDB LOCK WAIT; observed states: '.json_encode($lastStates, JSON_THROW_ON_ERROR));
        } finally {
            DB::purge($observerName);
            config([$configurationKey => $previousConfiguration]);
        }
    }

    /** @param array{process: resource, stdout: resource, stderr: resource} $worker */
    private function closeWorker(array $worker): void
    {
        $deadline = microtime(true) + 2;

        do {
            $status = proc_get_status($worker['process']);

            if (! $status['running']) {
                break;
            }

            usleep(10_000);
        } while (microtime(true) < $deadline);

        if ($status['running']) {
            proc_terminate($worker['process']);
        }

        fclose($worker['stdout']);
        fclose($worker['stderr']);
        proc_close($worker['process']);
    }
}
