import { defineConfig } from '@playwright/test';
import { readFileSync } from 'node:fs';

const secret = JSON.parse(readFileSync('storage/framework/testing/browser-secret.json', 'utf8'));
process.env.E2E_PASSWORD = secret.password;

export default defineConfig({
    testDir: './tests/Browser',
    workers: 1,
    fullyParallel: false,
    retries: 0,
    forbidOnly: true,
    timeout: 90_000,
    reporter: [
        ['list'],
        ['json', { outputFile: 'docs/testing/pbi16-18/browser-results.json' }],
    ],
    outputDir: 'storage/framework/testing/playwright-artifacts',
    webServer: {
        command: 'php -S 127.0.0.1:8876 -t public tests/Support/browser-router.php',
        url: 'http://127.0.0.1:8876/__pbi-testing/health',
        reuseExistingServer: false,
        timeout: 30_000,
        stdout: 'pipe',
        stderr: 'ignore',
    },
    use: {
        baseURL: 'http://127.0.0.1:8876',
        browserName: 'chromium',
        channel: process.env.PLAYWRIGHT_CHANNEL || undefined,
        headless: true,
        viewport: { width: 1440, height: 1000 },
        actionTimeout: 15_000,
        navigationTimeout: 20_000,
        trace: 'off',
        video: 'off',
    },
});
