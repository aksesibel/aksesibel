import { test, expect } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import path from 'node:path';

const credentials = () => {
    if (!process.env.E2E_PASSWORD || process.env.E2E_PASSWORD.length < 12) {
        throw new Error('E2E_PASSWORD harus berisi password sementara pengujian minimal 12 karakter.');
    }
    return process.env.E2E_PASSWORD;
};

const screenshotDirectory = path.resolve('docs/testing/pbi16-18/screenshots');
const protectedPages = [
    '/pengelola/users',
    '/pengelola/users/create',
    '/pengelola/facility-categories',
    '/pengelola/facility-categories/create',
    '/pengelola/issue-categories',
    '/pengelola/issue-categories/create',
    '/pengelola/reporting-periods',
    '/pengelola/reporting-periods/create',
    '/pengelola/statistics',
];
const diagnostics = new Map();

function redact(value) {
    const secret = process.env.E2E_PASSWORD;
    return secret ? String(value).replaceAll(secret, '[REDACTED]') : String(value);
}

function resourcePath(url) {
    try {
        return new URL(url).pathname;
    } catch {
        return '[invalid URL]';
    }
}

function observePage(page, details) {
    page.on('pageerror', (error) => details.pageErrors.push(redact(error.message)));
    page.on('console', (message) => {
        if (message.type() === 'error') {
            details.consoleErrors.push(redact(message.text()));
        }
    });
    page.on('requestfailed', (request) => {
        const error = request.failure()?.errorText ?? 'unknown';
        if (error !== 'net::ERR_ABORTED') {
            details.failedRequests.push({ path: resourcePath(request.url()), type: request.resourceType(), error });
        }
    });
    page.on('response', (response) => {
        if (response.status() >= 400) {
            details.httpErrors.push({ path: resourcePath(response.url()), type: response.request().resourceType(), status: response.status() });
        }
    });
    page.on('dialog', (dialog) => dialog.accept());
}

async function screenshot(page, name, testInfo) {
    const file = path.join(screenshotDirectory, `${name}.png`);
    await page.screenshot({ path: file, fullPage: true, animations: 'disabled' });
    await testInfo.attach(name, { path: file, contentType: 'image/png' });
}

async function login(page, email = 'manager.browser@example.test') {
    await page.goto('/login');
    await page.getByLabel('Email', { exact: true }).fill(email);
    await page.getByLabel('Password', { exact: true }).fill(credentials());
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/account$/);
}

async function manager(page) {
    await login(page);
    await expect(page.getByRole('heading', { name: 'Akun saya', exact: true })).toBeVisible();
}

async function openUser(page, email) {
    await page.goto(`/pengelola/users?q=${encodeURIComponent(email)}`);
    const row = page.locator('tbody tr').filter({ hasText: email });
    await expect(row).toHaveCount(1);
    await row.getByRole('link', { name: /^Detail/ }).click();
    await expect(page.getByRole('heading', { name: 'Detail pengguna', exact: true })).toBeVisible();
    return new URL(page.url()).pathname;
}

async function changeStatus(page, userPath, status) {
    await page.goto(userPath);
    await page.getByLabel('Status akun', { exact: true }).selectOption(status);
    await page.getByRole('button', { name: 'Ubah status', exact: true }).click();
}

async function fillUser(page, { email, name = 'Pengguna UI Browser', password = credentials(), role = 'Masyarakat' }) {
    await page.getByLabel('Nama', { exact: true }).fill(name);
    await page.getByLabel('Email', { exact: true }).fill(email);
    await page.locator('#password').fill(password);
    await page.getByLabel('Konfirmasi password', { exact: true }).fill(password);
    await page.getByLabel('Peran', { exact: true }).selectOption({ label: role });
    await page.getByLabel('Status akun', { exact: true }).selectOption('active');
}

async function createUser(page, email, name) {
    await page.goto('/pengelola/users/create');
    await fillUser(page, { email, name });
    await page.getByRole('button', { name: 'Simpan pengguna', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Pengguna berhasil ditambahkan.');
    return new URL(page.url()).pathname;
}

async function csrfPost(page, pathname, method, values = {}) {
    const token = await page.locator('input[name="_token"]').first().inputValue();
    return page.request.post(pathname, {
        form: { _token: token, _method: method, ...values },
        headers: { Referer: page.url(), Accept: 'text/html' },
        maxRedirects: 0,
    });
}

async function openNamedCategory(page, kind, name) {
    await page.goto(`/pengelola/${kind}?q=${encodeURIComponent(name)}`);
    await page.locator('tbody').getByRole('link', { name, exact: true }).click();
    return new URL(page.url()).pathname;
}

async function expectReportCount(page, count) {
    await expect(page.locator('[aria-labelledby="report-total-title"] p').first()).toHaveText(String(count));
}

test.setTimeout(90_000);

test.beforeAll(async () => {
    credentials();
    await mkdir(screenshotDirectory, { recursive: true });
});

test.beforeEach(async ({ page }, testInfo) => {
    const details = { pageErrors: [], consoleErrors: [], failedRequests: [], httpErrors: [] };
    diagnostics.set(testInfo.testId, details);
    observePage(page, details);
    try {
        execFileSync('php', ['tests/Support/browser-fixtures.php', 'clear-throttle'], { stdio: 'pipe', timeout: 20_000 });
    } catch {
        throw new Error('Pembersihan throttle database testing gagal; periksa koneksi aksesibel_testing yang dilindungi guard.');
    }
});

test.afterEach(async ({ page }, testInfo) => {
    const details = diagnostics.get(testInfo.testId);
    if (testInfo.status !== testInfo.expectedStatus && !page.isClosed()) {
        await screenshot(page, `failed-${testInfo.title.replace(/[^a-z0-9]+/gi, '-').toLowerCase()}`, testInfo);
    }
    await testInfo.attach('browser-diagnostics', { body: JSON.stringify(details, null, 2), contentType: 'application/json' });
    expect(details.pageErrors, 'Tidak boleh ada error JavaScript aplikasi').toEqual([]);
    expect(details.failedRequests, 'Tidak boleh ada resource yang gagal dimuat').toEqual([]);
    expect(details.httpErrors.filter((error) => error.type !== 'document'), 'Asset dan request antarmuka tidak boleh gagal').toEqual([]);
    expect(details.consoleErrors.filter((error) => !/^Failed to load resource: the server responded with a status of (403|419|422|429)/.test(error)), 'Tidak boleh ada error console selain penolakan akses yang diuji').toEqual([]);
    diagnostics.delete(testInfo.testId);
});

test('guest diarahkan ke login untuk semua URL internal langsung', async ({ page }, testInfo) => {
    for (const pathname of protectedPages) {
        await page.goto(pathname);
        await expect(page).toHaveURL(/\/login$/);
        await expect(page.getByRole('heading', { name: 'Masuk', exact: true })).toBeVisible();
    }
    await screenshot(page, '01-guest-login', testInfo);
});

test('form lintas origin tanpa token CSRF ditolak server dengan 419', async ({ page, baseURL }, testInfo) => {
    await page.goto('/login');
    const target = new URL('/login', baseURL).href;
    await page.route('http://localhost:8877/forgery', (route) => route.fulfill({
        status: 200,
        contentType: 'text/html',
        body: `<!doctype html><html lang="id"><title>Simulasi origin asing khusus tes</title><form method="POST" action="${target}"><label>Email<input name="email" type="email"></label><label>Password<input name="password" type="password"></label><button type="submit">Kirim formulir asing</button></form></html>`,
    }));
    await page.goto('http://localhost:8877/forgery');
    await page.getByLabel('Email', { exact: true }).fill('manager.browser@example.test');
    await page.getByLabel('Password', { exact: true }).fill(credentials());
    const response = page.waitForResponse((result) => result.url() === target && result.request().method() === 'POST');

    await page.getByRole('button', { name: 'Kirim formulir asing', exact: true }).click();

    const csrfResponse = await response;
    const runtime = { status: csrfResponse.status(), console: await csrfResponse.headerValue('x-pbi-testing-console'), fetchSite: await csrfResponse.headerValue('x-pbi-testing-fetch-site') };
    await testInfo.attach('csrf-runtime', { body: JSON.stringify(runtime), contentType: 'application/json' });
    expect(runtime.console).toBe('no');
    expect(runtime.fetchSite).toBe('cross-site');
    expect(csrfResponse.status()).toBe(419);
    await expect(page.locator('body')).toContainText('419');
    await screenshot(page, '01b-csrf-tanpa-token-ditolak', testInfo);
    await page.goto('/pengelola/users');
    await expect(page).toHaveURL(/\/login$/);
});

test('pengelola melihat lima navigasi dan logout mencabut sesi', async ({ page }, testInfo) => {
    await manager(page);
    const navigation = page.getByRole('navigation', { name: 'Modul pengelola', exact: true });
    for (const label of ['Pengguna dan Hak Akses', 'Kategori Fasilitas', 'Kategori Kendala', 'Periode Pelaporan', 'Rekap dan Statistik']) {
        await expect(navigation.getByRole('link', { name: label, exact: true })).toBeVisible();
    }
    await navigation.getByRole('link', { name: 'Pengguna dan Hak Akses', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Pengguna dan Hak Akses', exact: true })).toBeVisible();
    await screenshot(page, '02-pengelola-navigasi', testInfo);
    await page.getByRole('button', { name: 'Keluar', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Anda telah keluar.');
    await page.goto('/pengelola/users');
    await expect(page).toHaveURL(/\/login$/);
});

for (const [role, email] of [
    ['Admin', 'admin.browser@example.test'],
    ['Masyarakat', 'public.browser@example.test'],
    ['Tidak dikenal', 'unknown.browser@example.test'],
    ['Kosong', 'roleless.browser@example.test'],
]) {
    test(`peran ${role} ditolak saat mengakses URL modul secara langsung`, async ({ page }, testInfo) => {
        await login(page, email);
        await expect(page.getByRole('navigation', { name: 'Modul pengelola' })).toHaveCount(0);
        for (const pathname of protectedPages) {
            const response = await page.goto(pathname);
            expect(response.status(), `${role}: ${pathname}`).toBe(403);
        }
        await screenshot(page, `03-akses-ditolak-${role.toLowerCase().replaceAll(' ', '-')}`, testInfo);
    });
}

for (const [status, email] of [
    ['inactive', 'inactive.browser@example.test'],
    ['suspended', 'suspended.browser@example.test'],
]) {
    test(`akun ${status} tidak dapat login`, async ({ page }, testInfo) => {
        await page.goto('/login');
        await page.getByLabel('Email', { exact: true }).fill(email);
        await page.getByLabel('Password', { exact: true }).fill(credentials());
        await page.getByRole('button', { name: 'Masuk', exact: true }).click();
        await expect(page).toHaveURL(/\/login$/);
        await expect(page.getByRole('alert')).toContainText('Email/password tidak sesuai atau akun tidak aktif.');
        await screenshot(page, `04-login-${status}-ditolak`, testInfo);
    });
}

test('registrasi dan profil menolak injeksi peran serta status', async ({ page, browser, baseURL }, testInfo) => {
    const context = await browser.newContext({ baseURL });
    const privileged = await context.newPage();
    observePage(privileged, diagnostics.get(testInfo.testId));
    let managerRole;
    try {
        await manager(privileged);
        await privileged.goto('/pengelola/users/create');
        managerRole = await privileged.getByRole('option', { name: 'Pengelola Fasilitas', exact: true }).getAttribute('value');
    } finally {
        await context.close();
    }
    await page.goto('/register');
    await page.getByLabel('Nama', { exact: true }).fill('Registrasi Browser');
    await page.getByLabel('Email', { exact: true }).fill('registration.browser@example.test');
    await page.locator('#password').fill(credentials());
    await page.getByLabel('Konfirmasi password', { exact: true }).fill(credentials());
    await page.locator('form[action$="/register"]').evaluate((form, role) => {
        for (const [name, value] of [['role_id', role], ['account_status', 'suspended']]) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value; form.append(input);
        }
    }, managerRole);
    await page.getByRole('button', { name: 'Buat akun', exact: true }).click();
    await expect(page.getByRole('alert')).toContainText('Peran tidak boleh diubah melalui halaman ini.');
    await expect(page.getByRole('alert')).toContainText('Status akun tidak boleh diubah melalui halaman ini.');
    await screenshot(page, '05-registrasi-eskalasi-ditolak', testInfo);
    await page.locator('#password').fill(credentials());
    await page.getByLabel('Konfirmasi password', { exact: true }).fill(credentials());
    await page.getByRole('button', { name: 'Buat akun', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Masyarakat', exact: true })).toBeVisible();
    const profileResponse = await csrfPost(page, '/profile', 'PATCH', {
        name: 'Registrasi Browser', email: 'registration.browser@example.test', role_id: managerRole, account_status: 'active',
    });
    expect(profileResponse.status()).toBe(302);
    await page.goto('/account');
    await expect(page.getByRole('alert')).toContainText('Peran tidak boleh diubah melalui halaman ini.');
    await expect(page.getByRole('heading', { name: 'Masyarakat', exact: true })).toBeVisible();
    expect((await page.goto('/pengelola/users')).status()).toBe(403);
});

test('pengguna dapat dibuat diedit dicari dan password kosong tetap berlaku', async ({ page, browser, baseURL }, testInfo) => {
    await manager(page);
    await page.goto('/pengelola/users/create');
    await fillUser(page, { email: 'manager.browser@example.test' });
    await page.getByRole('button', { name: 'Simpan pengguna', exact: true }).click();
    await expect(page.getByRole('alert')).toContainText('Email sudah digunakan.');
    await fillUser(page, { email: 'created.browser@example.test', password: 'short' });
    await page.locator('form[action$="/users"]').evaluate((form) => { form.noValidate = true; });
    await page.getByRole('button', { name: 'Simpan pengguna', exact: true }).click();
    await expect(page.getByRole('alert')).toContainText('Password minimal 12 karakter.');
    await screenshot(page, '06-validasi-pengguna', testInfo);
    await fillUser(page, { email: 'created.browser@example.test' });
    await page.getByRole('button', { name: 'Simpan pengguna', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Pengguna berhasil ditambahkan.');
    await page.getByRole('link', { name: 'Edit pengguna', exact: true }).click();
    await expect(page.locator('#password')).toHaveValue('');
    await page.getByLabel('Nama', { exact: true }).fill('Pengguna Browser Diperbarui');
    await page.getByLabel('Peran', { exact: true }).selectOption({ label: 'Admin' });
    await page.getByRole('button', { name: 'Simpan pengguna', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Data pengguna berhasil diperbarui.');
    await expect(page.locator('dd').filter({ hasText: /^Admin$/ })).toBeVisible();
    await screenshot(page, '07-detail-pengguna-diperbarui', testInfo);
    const context = await browser.newContext({ baseURL });
    const updatedUser = await context.newPage();
    observePage(updatedUser, diagnostics.get(testInfo.testId));
    try {
        await login(updatedUser, 'created.browser@example.test');
        await expect(updatedUser.getByRole('heading', { name: 'Admin', exact: true })).toBeVisible();
        expect((await updatedUser.goto('/pengelola/users')).status()).toBe(403);
    } finally {
        await context.close();
    }
    await page.goto('/pengelola/users');
    await page.getByLabel('Cari nama atau email').fill('created.browser@example.test');
    await page.getByLabel('Peran', { exact: true }).selectOption({ label: 'Admin' });
    await page.getByLabel('Status akun', { exact: true }).selectOption('active');
    await page.getByRole('button', { name: 'Terapkan', exact: true }).click();
    await expect(page.locator('tbody tr')).toHaveCount(1);
    await expect(page.locator('tbody')).toContainText('Pengguna Browser Diperbarui');
    await expect(page.locator('tbody')).toContainText('created.browser@example.test');
});

test('penonaktifan dan penangguhan mencabut sesi browser yang sudah login', async ({ page, browser, baseURL }, testInfo) => {
    await manager(page);
    const userPath = await createUser(page, 'revoked.browser@example.test', 'Sesi Browser Dicabut');
    const context = await browser.newContext({ baseURL });
    const oldSession = await context.newPage();
    observePage(oldSession, diagnostics.get(testInfo.testId));
    try {
        for (const status of ['inactive', 'suspended']) {
            await login(oldSession, 'revoked.browser@example.test');
            await expect(oldSession.getByRole('heading', { name: 'Akun saya', exact: true })).toBeVisible();
            await changeStatus(page, userPath, status);
            await expect(page.getByRole('status')).toContainText('Status akun berhasil diperbarui.');
            await oldSession.goto('/account');
            await expect(oldSession).toHaveURL(/\/login$/);
            await expect(oldSession.getByRole('alert')).toContainText('Akun tidak aktif atau ditangguhkan. Hubungi Pengelola Fasilitas.');
            await screenshot(oldSession, `08-sesi-${status}-dicabut`, testInfo);
            await changeStatus(page, userPath, 'active');
            await expect(page.getByRole('status')).toContainText('Status akun berhasil diperbarui.');
        }
    } finally {
        await context.close();
    }
});

test('akun sendiri dan pengelola aktif terakhir dilindungi', async ({ page }, testInfo) => {
    await manager(page);
    const ownPath = await openUser(page, 'manager.browser@example.test');
    await changeStatus(page, ownPath, 'inactive');
    await expect(page.getByRole('alert')).toContainText('Anda tidak dapat menonaktifkan atau menangguhkan akun sendiri.');
    const backupPath = await openUser(page, 'backup.browser@example.test');
    await changeStatus(page, backupPath, 'inactive');
    await expect(page.getByRole('status')).toContainText('Status akun berhasil diperbarui.');
    try {
        await page.goto(`${ownPath}/edit`);
        await page.getByLabel('Peran', { exact: true }).selectOption({ label: 'Masyarakat' });
        await page.getByRole('button', { name: 'Simpan pengguna', exact: true }).click();
        await expect(page.getByRole('alert')).toContainText('Harus tersedia minimal satu Pengelola Fasilitas aktif.');
        await screenshot(page, '09-pengelola-terakhir-dilindungi', testInfo);
    } finally {
        await changeStatus(page, backupPath, 'active');
        await expect(page.getByRole('status')).toContainText('Status akun berhasil diperbarui.');
    }
});

for (const [kind, label, fixture, slug] of [
    ['facility-categories', 'Kategori Fasilitas', 'Fasilitas Browser A', 'fasilitas'],
    ['issue-categories', 'Kategori Kendala', 'Kendala Browser A', 'kendala'],
]) {
    test(`${label} memiliki CRUD status duplikasi dan perlindungan kategori terpakai`, async ({ page }, testInfo) => {
        await manager(page);
        await page.getByRole('navigation', { name: 'Modul pengelola' }).getByRole('link', { name: label, exact: true }).click();
        await page.getByRole('link', { name: 'Tambah kategori', exact: true }).click();
        await page.getByLabel('Nama kategori').fill(`  Kategori UI Browser ${slug}  `);
        await page.getByLabel('Deskripsi (opsional)').fill('Deskripsi pengujian melalui browser.');
        await page.getByRole('button', { name: 'Simpan kategori', exact: true }).click();
        await expect(page.getByRole('status')).toContainText('berhasil ditambahkan.');
        const categoryPath = new URL(page.url()).pathname;
        await expect(page.locator('dd').first()).toHaveText(`Kategori UI Browser ${slug}`);
        await page.getByRole('link', { name: 'Edit kategori', exact: true }).click();
        await page.getByLabel('Nama kategori').fill(`Kategori UI Browser ${slug} diubah`);
        await page.getByLabel('Deskripsi (opsional)').fill('Deskripsi telah diperbarui.');
        await page.getByRole('button', { name: 'Simpan kategori', exact: true }).click();
        await expect(page.getByRole('status')).toContainText('berhasil diperbarui.');
        await page.getByRole('button', { name: 'Nonaktifkan kategori', exact: true }).click();
        await expect(page.locator('dd .badge')).toHaveText('Nonaktif');
        await screenshot(page, `10-kategori-${slug}-nonaktif`, testInfo);
        await page.getByRole('button', { name: 'Aktifkan kategori', exact: true }).click();
        await expect(page.locator('dd .badge')).toHaveText('Aktif');
        await page.goto(`/pengelola/${kind}/create`);
        await page.getByLabel('Nama kategori').fill(`  ${fixture.toLowerCase()}  `);
        await page.getByRole('button', { name: 'Simpan kategori', exact: true }).click();
        await expect(page.getByRole('alert')).toContainText('Nama kategori sudah digunakan.');
        await page.goto(categoryPath);
        await page.getByRole('button', { name: 'Hapus kategori', exact: true }).click();
        await expect(page.getByRole('status')).toContainText('berhasil dihapus.');
        const usedPath = await openNamedCategory(page, kind, fixture);
        await expect(page.getByRole('button', { name: 'Hapus kategori', exact: true })).toHaveCount(0);
        const response = await csrfPost(page, usedPath, 'DELETE');
        expect(response.status()).toBe(302);
        await page.goto(usedPath);
        await expect(page.getByRole('alert')).toContainText('Nonaktifkan kategori agar histori tetap tersedia.');
        await expect(page.getByRole('heading', { name: `${label}: ${fixture}`, exact: true })).toBeVisible();
        await screenshot(page, `11-kategori-${slug}-terpakai-dilindungi`, testInfo);
    });
}

test('periode memvalidasi tanggal menolak overlap dan melindungi periode terpakai', async ({ page }, testInfo) => {
    await manager(page);
    await page.goto('/pengelola/reporting-periods?q=Periode%20Browser%20A');
    await page.locator('tbody').getByRole('link', { name: 'Periode Browser A', exact: true }).click();
    const usedPath = new URL(page.url()).pathname;
    await page.getByRole('link', { name: 'Edit periode', exact: true }).click();
    const fixtureStart = await page.getByLabel('Tanggal mulai').inputValue();
    const fixtureEnd = await page.getByLabel('Tanggal selesai').inputValue();
    await page.goto('/pengelola/reporting-periods/create');
    await page.getByLabel('Nama periode').fill('Periode UI Browser');
    await page.getByLabel('Tanggal mulai').fill('2026-11-10');
    await page.getByLabel('Tanggal selesai').fill('2026-11-09');
    await page.getByRole('button', { name: 'Simpan periode', exact: true }).click();
    await expect(page.getByRole('alert')).toContainText('Tanggal selesai harus sama atau sesudah tanggal mulai.');
    await screenshot(page, '12-validasi-tanggal-periode', testInfo);
    await page.getByLabel('Tanggal selesai').fill('2026-11-10');
    await page.getByRole('button', { name: 'Simpan periode', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Periode pelaporan berhasil ditambahkan.');
    await page.getByRole('link', { name: 'Edit periode', exact: true }).click();
    await page.getByLabel('Nama periode').fill('Periode UI Browser diubah');
    await page.getByLabel('Tanggal mulai').fill(fixtureStart);
    await page.getByLabel('Tanggal selesai').fill(fixtureEnd);
    await page.getByRole('button', { name: 'Simpan periode', exact: true }).click();
    await expect(page.getByRole('alert')).toContainText('Rentang tanggal bertumpang tindih dengan periode lain.');
    await page.getByLabel('Tanggal mulai').fill('2026-11-11');
    await page.getByLabel('Tanggal selesai').fill('2026-11-11');
    await page.getByRole('button', { name: 'Simpan periode', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Periode diperbarui. Laporan yang terhubung tetap pada periode ini.');
    await page.getByRole('button', { name: 'Hapus periode', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Periode pelaporan berhasil dihapus.');
    await page.goto(usedPath);
    await expect(page.getByRole('button', { name: 'Hapus periode', exact: true })).toHaveCount(0);
    expect((await csrfPost(page, usedPath, 'DELETE')).status()).toBe(302);
    await page.goto(usedPath);
    await expect(page.getByRole('alert')).toContainText('Periode digunakan laporan dan tidak dapat dihapus.');
    await screenshot(page, '13-periode-terpakai-dilindungi', testInfo);
});

test('pencarian dan pagination daftar mempertahankan filter', async ({ page }, testInfo) => {
    await manager(page);
    for (const [pathname, label] of [
        ['/pengelola/users', 'Cari nama atau email'],
        ['/pengelola/facility-categories', 'Cari nama kategori'],
        ['/pengelola/issue-categories', 'Cari nama kategori'],
        ['/pengelola/reporting-periods', 'Cari nama periode'],
    ]) {
        await page.goto(pathname);
        await page.getByLabel(label).fill('Paginasi Browser');
        await page.locator('form[method="GET"]').getByRole('button').click();
        await expect(page.locator('tbody tr')).toHaveCount(15);
        await page.locator('a[rel="next"]:visible').click();
        await expect(page).toHaveURL(/[?&]page=2/);
        await expect(page.getByLabel(label)).toHaveValue('Paginasi Browser');
        await expect(page.locator('tbody tr')).toHaveCount(3);
        await page.getByRole('link', { name: 'Reset filter', exact: true }).click();
        await expect(page.getByLabel(label)).toHaveValue('');
    }
    await screenshot(page, '14-pagination-periode', testInfo);
});

test('statistik menghitung laporan sekali dengan filter konsisten dan kondisi kosong jelas', async ({ page }, testInfo) => {
    await manager(page);
    await page.goto('/pengelola/statistics');
    await expectReportCount(page, 5);
    await expect(page.locator('[aria-labelledby="facility-total-title"] p').first()).toHaveText('3');
    await expect(page.locator('[aria-labelledby="status-title"] tbody tr').filter({ hasText: 'Status Browser A' }).locator('td')).toHaveText('2');
    await expect(page.locator('[aria-labelledby="status-title"] tbody tr').filter({ hasText: 'Status Browser B' }).locator('td')).toHaveText('3');
    await screenshot(page, '15-statistik-semua-laporan', testInfo);
    const scenarios = [
        ['Periode Browser A', '', '', 3],
        ['', 'Fasilitas Browser A', '', 3],
        ['', '', 'Kendala Browser A', 3],
        ['Periode Browser A', 'Fasilitas Browser A', '', 2],
        ['Periode Browser A', '', 'Kendala Browser A', 2],
        ['', 'Fasilitas Browser A', 'Kendala Browser A', 2],
        ['Periode Browser A', 'Fasilitas Browser A', 'Kendala Browser A', 1],
    ];
    for (const [period, facility, issue, expected] of scenarios) {
        await page.getByLabel('Periode pelaporan', { exact: true }).selectOption(period ? { label: period } : '');
        await page.getByLabel('Kategori fasilitas', { exact: true }).selectOption(facility ? { label: facility } : '');
        await page.getByLabel('Kategori kendala', { exact: true }).selectOption(issue ? { label: issue } : '');
        await page.getByRole('button', { name: 'Terapkan filter', exact: true }).click();
        await expectReportCount(page, expected);
        await expect(page.locator('[aria-labelledby="facility-total-title"] p').first()).toHaveText('3');
        for (const id of ['status', 'issue', 'facility']) {
            const counts = await page.locator(`[aria-labelledby="${id}-title"] tbody td`).allTextContents();
            expect(counts.reduce((sum, value) => sum + Number(value.replaceAll('.', '')), 0)).toBe(expected);
        }
        const recapCounts = await page.locator('[aria-labelledby="recap-title"] tbody tr td:last-child').allTextContents();
        expect(recapCounts.reduce((sum, value) => sum + Number(value.replaceAll('.', '')), 0)).toBe(expected);
    }
    await screenshot(page, '16-statistik-tiga-filter', testInfo);
    await page.getByLabel('Periode pelaporan', { exact: true }).selectOption({ label: 'Periode Browser B' });
    await page.getByLabel('Kategori fasilitas', { exact: true }).selectOption({ label: 'Fasilitas Browser B' });
    await page.getByLabel('Kategori kendala', { exact: true }).selectOption({ label: 'Kendala Browser A' });
    await page.getByRole('button', { name: 'Terapkan filter', exact: true }).click();
    await expectReportCount(page, 0);
    await expect(page.getByRole('status')).toContainText('Belum ada laporan yang sesuai dengan filter.');
    await screenshot(page, '17-statistik-filter-kosong', testInfo);
    await page.getByRole('link', { name: 'Reset filter', exact: true }).click();
    await expectReportCount(page, 5);
    await expect(page.getByLabel('Periode pelaporan', { exact: true })).toHaveValue('');
    await expect(page.getByLabel('Kategori fasilitas', { exact: true })).toHaveValue('');
    await expect(page.getByLabel('Kategori kendala', { exact: true })).toHaveValue('');
});

test('navigasi keyboard dan layout seluler dapat digunakan', async ({ page }, testInfo) => {
    await manager(page);
    await page.goto('/pengelola/users');
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Lewati ke konten', exact: true })).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(page.locator('main')).toBeFocused();
    await page.setViewportSize({ width: 390, height: 844 });
    for (const pathname of ['/pengelola/users', '/pengelola/facility-categories', '/pengelola/issue-categories', '/pengelola/reporting-periods', '/pengelola/statistics']) {
        await page.goto(pathname);
        await expect(page.locator('h1')).toBeVisible();
        const menu = page.getByRole('button', { name: 'Buka navigasi', exact: true });
        await expect(page.getByRole('navigation', { name: 'Modul pengelola', exact: true })).toBeHidden();
        await menu.click();
        await expect(page.getByRole('navigation', { name: 'Modul pengelola', exact: true })).toBeVisible();
        await expect(page.locator('#workspace-content')).toHaveAttribute('inert', '');
        await page.keyboard.press('Escape');
        await expect(menu).toBeFocused();
        await expect(page.locator('#workspace-content')).not.toHaveAttribute('inert');
        const layout = await page.evaluate(() => ({
            viewport: window.innerWidth,
            documentWidth: document.documentElement.scrollWidth,
            overflowing: [...document.querySelectorAll('body *')].map((element) => ({
                tag: element.tagName, className: String(element.className),
                left: Math.round(element.getBoundingClientRect().left),
                right: Math.round(element.getBoundingClientRect().right),
            })).filter((element) => element.right > window.innerWidth + 1 || element.left < -1),
        }));
        await testInfo.attach(`layout-${pathname.split('/').pop()}`, { body: JSON.stringify(layout), contentType: 'application/json' });
        expect(layout.documentWidth <= layout.viewport + 1, `${pathname}: ${JSON.stringify(layout)}`).toBe(true);
    }
    await screenshot(page, '18-statistik-seluler', testInfo);
});
