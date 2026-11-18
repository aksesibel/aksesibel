import { spawnSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';

const fixtures = spawnSync('php', ['tests/Support/browser-fixtures.php', 'reset'], {
    cwd: process.cwd(),
    env: process.env,
    encoding: 'utf8',
    timeout: 60_000,
});
if (fixtures.status !== 0) {
    process.stderr.write('Fixture browser gagal disiapkan; pastikan aksesibel_testing tersedia dan koneksi testing diizinkan.\n');
    process.exit(fixtures.status ?? 1);
}
process.stdout.write(fixtures.stdout);

const { password } = JSON.parse(readFileSync('storage/framework/testing/browser-secret.json', 'utf8'));
const result = spawnSync(process.execPath, ['node_modules/@playwright/test/cli.js', 'test', ...process.argv.slice(2)], {
    cwd: process.cwd(),
    env: { ...process.env, E2E_PASSWORD: password },
    encoding: 'utf8',
    maxBuffer: 12 * 1024 * 1024,
});
const scrub = (value) => String(value || '').replaceAll(password, '[REDACTED]');
const log = scrub(result.stdout) + scrub(result.stderr);
writeFileSync('docs/testing/pbi16-18/browser-run.log', log);
try {
    const filename = 'docs/testing/pbi16-18/browser-results.json';
    writeFileSync(filename, scrub(readFileSync(filename, 'utf8')));
} catch {
    // The runner may fail before creating its JSON report.
}
process.stdout.write(log);
process.exit(result.status ?? 1);
