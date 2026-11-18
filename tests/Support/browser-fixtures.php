<?php

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FollowUpAssignment;
use App\Models\FollowUpProgress;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationHistory;
use App\Models\VerificationStatus;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Contracts\Console\Kernel;
use Tests\Support\TestingDatabaseSafety;

set_exception_handler(function (Throwable $exception): void {
    fwrite(STDERR, json_encode(['fixtures' => 'failed', 'exception' => $exception::class, 'code' => (string) $exception->getCode()]).PHP_EOL);
    exit(1);
});

$app = require __DIR__.'/bootstrap-mysql.php';
TestingDatabaseSafety::assertSafe($app);

if (($argv[1] ?? '') === 'clear-throttle') {
    $app['db']->table('cache')->delete();
    $app['db']->table('cache_locks')->delete();
    echo json_encode(['database' => 'aksesibel_testing', 'throttle_cache' => 'cleared']), PHP_EOL;
    exit(0);
}

if (($argv[1] ?? '') !== 'reset') {
    fwrite(STDERR, "Gunakan reset atau clear-throttle; database testing diverifikasi lebih dahulu.\n");
    exit(1);
}

$kernel = $app->make(Kernel::class);
$exit = $kernel->call('migrate:fresh', ['--force' => true, '--no-interaction' => true]);

if ($exit !== 0) {
    throw new RuntimeException('Reset database testing gagal.');
}

TestingDatabaseSafety::assertSafe($app);
$app->make(MasterDataSeeder::class)->run();
$password = bin2hex(random_bytes(20));
$roles = Role::all()->keyBy('role_name');
$unknownRole = Role::create(['role_name' => 'Peran Browser Tidak Dikenal']);
$accounts = [
    'manager' => [Role::FACILITY_MANAGER, 'active'],
    'backup' => [Role::FACILITY_MANAGER, 'active'],
    'admin' => [Role::ADMIN, 'active'],
    'public' => [Role::PUBLIC, 'active'],
    'inactive' => [Role::PUBLIC, 'inactive'],
    'suspended' => [Role::PUBLIC, 'suspended'],
    'unknown' => [null, 'active'],
    'roleless' => [null, 'active'],
];
$users = [];

foreach ($accounts as $key => [$role, $status]) {
    $users[$key] = User::create([
        'name' => 'Akun Browser '.ucfirst($key),
        'email' => $key.'.browser@example.test',
        'password' => $password,
        'role_id' => $key === 'unknown' ? $unknownRole->role_id : ($role ? $roles[$role]->role_id : null),
        'account_status' => $status,
    ]);
}

$categories = [
    FacilityCategory::create(['category_name' => 'Fasilitas Browser A', 'is_active' => true]),
    FacilityCategory::create(['category_name' => 'Fasilitas Browser B', 'is_active' => true]),
];
$issues = [
    IssueCategory::create(['category_name' => 'Kendala Browser A', 'is_active' => true]),
    IssueCategory::create(['category_name' => 'Kendala Browser B', 'is_active' => true]),
];
$periods = [
    ReportingPeriod::create(['period_name' => 'Periode Browser A', 'start_date' => '2026-01-01', 'end_date' => '2026-01-31']),
    ReportingPeriod::create(['period_name' => 'Periode Browser B', 'start_date' => '2026-02-01', 'end_date' => '2026-02-28']),
];
$statuses = [
    VerificationStatus::create(['status_name' => 'Status Browser A']),
    VerificationStatus::create(['status_name' => 'Status Browser B']),
];
$facilities = [
    Facility::create(['category_id' => $categories[0]->category_id, 'name' => 'Taman Browser A']),
    Facility::create(['category_id' => $categories[1]->category_id, 'name' => 'Taman Browser B']),
    Facility::create(['category_id' => $categories[1]->category_id, 'name' => 'Taman Browser C', 'facility_status' => 'inactive']),
];
$reports = [];

foreach ([[0, 0, 0, 0], [0, 0, 1, 1], [1, 0, 0, 1], [0, 1, 0, 0], [1, 1, 1, 1]] as $index => [$period, $facility, $issue, $status]) {
    $reports[] = Report::create([
        'report_number' => 'BROWSER-'.($index + 1),
        'user_id' => $users['public']->id,
        'facility_id' => $facilities[$facility]->facility_id,
        'issue_category_id' => $issues[$issue]->issue_category_id,
        'status_id' => $statuses[$status]->status_id,
        'period_id' => $periods[$period]->period_id,
        'description' => 'Laporan fiktif khusus pengujian browser.',
        'created_at' => $index === 0 ? '2025-12-31 23:59:59' : '2026-01-15 10:00:00',
        'updated_at' => '2026-01-15 10:00:00',
    ]);
}

$reports[0]->evidences()->createMany([
    ['file_path' => 'testing/browser-evidence-a.jpg', 'file_type' => 'image'],
    ['file_path' => 'testing/browser-evidence-b.jpg', 'file_type' => 'image'],
]);
$verification = Verification::create(['report_id' => $reports[0]->report_id, 'verifier_id' => $users['admin']->id]);

foreach (['Pemeriksaan pertama', 'Pemeriksaan lanjutan'] as $notes) {
    VerificationHistory::create(['verification_id' => $verification->verification_id, 'actor_id' => $users['admin']->id, 'new_status_id' => $statuses[0]->status_id, 'notes' => $notes]);
}

$assignment = FollowUpAssignment::create(['report_id' => $reports[0]->report_id, 'assigned_by' => $users['manager']->id, 'assigned_to' => $users['backup']->id]);
foreach (['started', 'in_progress'] as $status) {
    FollowUpProgress::create(['assignment_id' => $assignment->assignment_id, 'actor_id' => $users['backup']->id, 'progress_status' => $status, 'action_description' => 'Progres fiktif browser.']);
}

foreach (range(1, 18) as $number) {
    $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
    User::create(['name' => 'Paginasi Browser '.$suffix, 'email' => 'pagination'.$suffix.'.browser@example.test', 'password' => $password, 'role_id' => $roles[Role::PUBLIC]->role_id, 'account_status' => 'active']);
    FacilityCategory::create(['category_name' => 'Paginasi Browser '.$suffix, 'is_active' => true]);
    IssueCategory::create(['category_name' => 'Paginasi Browser '.$suffix, 'is_active' => true]);
    ReportingPeriod::create(['period_name' => 'Paginasi Browser '.$suffix, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31']);
}

$secretFile = storage_path('framework/testing/browser-secret.json');
file_put_contents($secretFile, json_encode(['password' => $password]));
chmod($secretFile, 0600);
echo json_encode(['database' => 'aksesibel_testing', 'fixtures' => 'ready', 'reports' => count($reports), 'facilities' => count($facilities), 'password' => 'temporary; not printed']), PHP_EOL;
