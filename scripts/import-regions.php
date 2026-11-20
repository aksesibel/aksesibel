#!/usr/bin/env php
<?php
/**
 * scripts/import-regions.php
 *
 * Import data wilayah dari file CSV ke database.
 *
 * Format CSV yang diharapkan:
 *   code,name,level,parent_code
 *   31,DKI Jakarta,PROVINSI,
 *   31.71,Kota Jakarta Pusat,KOTA_KABUPATEN,31
 *   31.71.01,Menteng,KECAMATAN,31.71
 *   31.71.01.1001,Menteng,KELURAHAN,31.71.01
 *
 * Level yang valid: PROVINSI | KOTA_KABUPATEN | KECAMATAN | KELURAHAN
 *
 * Penggunaan:
 *   php scripts/import-regions.php path/to/wilayah.csv
 *   php scripts/import-regions.php path/to/wilayah.csv --dry-run
 *
 * Catatan: File CSV wilayah lengkap Indonesia tersedia di
 *   https://github.com/cahyadsn/wilayah (lisensi MIT)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$args    = array_slice($argv, 1);
$csvFile = null;
$dryRun  = false;

foreach ($args as $arg) {
    if ($arg === '--dry-run') {
        $dryRun = true;
    } elseif (!$csvFile) {
        $csvFile = $arg;
    }
}

if (!$csvFile) {
    echo "Penggunaan: php scripts/import-regions.php <file.csv> [--dry-run]\n";
    exit(1);
}

if (!file_exists($csvFile)) {
    echo "File tidak ditemukan: {$csvFile}\n";
    exit(1);
}

$validLevels = ['PROVINSI', 'KOTA_KABUPATEN', 'KECAMATAN', 'KELURAHAN'];

$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle); // skip header

$imported  = 0;
$updated   = 0;
$skipped   = 0;
$errors    = [];

// Ambil semua kode yang sudah ada + yang baru diimport untuk lookup parent
$codeToId = DB::table('regions')->pluck('region_id', 'code')->toArray();

while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 3) continue;

    [$code, $name, $level] = $row;
    $parentCode = $row[3] ?? null;
    $code       = trim($code);
    $name       = trim($name);
    $level      = strtoupper(trim($level));

    if (!$code || !$name) {
        $errors[] = "Baris kosong dilewati.";
        $skipped++;
        continue;
    }

    if (!in_array($level, $validLevels)) {
        $errors[] = "Level '{$level}' tidak valid untuk kode {$code}. Dilewati.";
        $skipped++;
        continue;
    }

    $parentId = null;
    if ($parentCode && $parentCode = trim($parentCode)) {
        $parentId = $codeToId[$parentCode] ?? null;
        if (!$parentId) {
            $errors[] = "Parent kode '{$parentCode}' untuk '{$code}' belum ada. Dilewati.";
            $skipped++;
            continue;
        }
    }

    if ($dryRun) {
        echo "[DRY-RUN] {$level}: {$code} — {$name}" . ($parentId ? " (parent: {$parentCode})" : '') . "\n";
        $imported++;
        continue;
    }

    $existing = DB::table('regions')->where('code', $code)->first();
    if ($existing) {
        DB::table('regions')->where('code', $code)->update([
            'name'       => $name,
            'level'      => $level,
            'parent_id'  => $parentId,
            'updated_at' => now(),
        ]);
        $codeToId[$code] = $existing->region_id;
        $updated++;
    } else {
        $newId = DB::table('regions')->insertGetId([
            'code'       => $code,
            'name'       => $name,
            'level'      => $level,
            'parent_id'  => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $codeToId[$code] = $newId;
        $imported++;
    }
}

fclose($handle);

echo "\n";
if ($dryRun) echo "[DRY-RUN MODE — tidak ada yang disimpan]\n";
echo "Selesai. Ditambahkan: {$imported} | Diperbarui: {$updated} | Dilewati: {$skipped}\n";

if ($errors) {
    echo "\nPeringatan:\n";
    foreach ($errors as $e) echo "  - {$e}\n";
}
