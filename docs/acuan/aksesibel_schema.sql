-- =====================================================================
-- AKSESIBEL - Skema database (MySQL / MariaDB)
-- Dibuat dari file migration di repo. Sumber kebenaran tetap MIGRATION.
--
-- KAPAN FILE INI DIPAKAI:
--   * Lampiran laporan / dokumentasi / presentasi
--   * Demo cepat di phpMyAdmin pada database TERPISAH
--
-- PENTING:
--   Jangan import file ini ke database `aksesibel` yang dipakai Laravel
--   lalu menjalankan `php artisan migrate`. Tabel `migrations` tidak ikut
--   terisi, sehingga migrate akan error "table already exists".
--   Untuk kerja harian pakai: php artisan migrate --seed
--
-- CARA IMPORT (phpMyAdmin):
--   1. Buat database baru, misalnya `aksesibel_sql` (utf8mb4_unicode_ci)
--   2. Pilih database itu -> tab Import -> pilih file ini -> Go
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- BAGIAN 1: TABEL SESUAI ERD
-- =====================================================================

-- ---------------------------------------------------------------------
-- roles
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name`   VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- users  (id & password mengikuti standar Laravel; + role_id, account_status)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id`           BIGINT UNSIGNED NULL,
  `name`              VARCHAR(255) NOT NULL,
  `email`             VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password`          VARCHAR(255) NOT NULL,
  `account_status`    VARCHAR(255) NOT NULL DEFAULT 'active',
  `remember_token`    VARCHAR(100) NULL,
  `created_at`        TIMESTAMP NULL,
  `updated_at`        TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`)
    REFERENCES `roles` (`role_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- facility_categories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `facility_categories`;
CREATE TABLE `facility_categories` (
  `category_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(255) NOT NULL,
  `description`   TEXT NULL,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- facilities
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `facilities`;
CREATE TABLE `facilities` (
  `facility_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`     BIGINT UNSIGNED NOT NULL,
  `name`            VARCHAR(255) NOT NULL,
  `description`     TEXT NULL,
  `address`         TEXT NULL,
  `operating_hours` TEXT NULL,
  `contact`         VARCHAR(255) NULL,
  `facility_status` VARCHAR(255) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`facility_id`),
  CONSTRAINT `facilities_category_id_foreign` FOREIGN KEY (`category_id`)
    REFERENCES `facility_categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- facility_locations  (relasi 1:1 dengan facilities)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `facility_locations`;
CREATE TABLE `facility_locations` (
  `location_id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `facility_id`          BIGINT UNSIGNED NOT NULL,
  `region`               VARCHAR(255) NULL,
  `latitude`             DECIMAL(10,7) NULL,
  `longitude`            DECIMAL(10,7) NULL,
  `location_description` TEXT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `facility_locations_facility_id_unique` (`facility_id`),
  CONSTRAINT `facility_locations_facility_id_foreign` FOREIGN KEY (`facility_id`)
    REFERENCES `facilities` (`facility_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- accessibility_types
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `accessibility_types`;
CREATE TABLE `accessibility_types` (
  `accessibility_type_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_name`             VARCHAR(255) NOT NULL,
  `description`           TEXT NULL,
  PRIMARY KEY (`accessibility_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- facility_accessibilities
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `facility_accessibilities`;
CREATE TABLE `facility_accessibilities` (
  `facility_accessibility_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `facility_id`               BIGINT UNSIGNED NOT NULL,
  `accessibility_type_id`     BIGINT UNSIGNED NOT NULL,
  `availability_status`       VARCHAR(255) NULL,
  `condition_status`          VARCHAR(255) NULL,
  `location_description`      TEXT NULL,
  `photo_path`                TEXT NULL,
  `notes`                     TEXT NULL,
  PRIMARY KEY (`facility_accessibility_id`),
  CONSTRAINT `facility_accessibilities_facility_id_foreign` FOREIGN KEY (`facility_id`)
    REFERENCES `facilities` (`facility_id`) ON DELETE CASCADE,
  CONSTRAINT `facility_accessibilities_accessibility_type_id_foreign` FOREIGN KEY (`accessibility_type_id`)
    REFERENCES `accessibility_types` (`accessibility_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- accessibility_inspections
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `accessibility_inspections`;
CREATE TABLE `accessibility_inspections` (
  `inspection_id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `facility_accessibility_id` BIGINT UNSIGNED NOT NULL,
  `inspector_id`              BIGINT UNSIGNED NOT NULL,
  `inspection_date`           DATE NOT NULL,
  `condition_result`          VARCHAR(255) NULL,
  `notes`                     TEXT NULL,
  `information_source`        VARCHAR(255) NULL,
  PRIMARY KEY (`inspection_id`),
  CONSTRAINT `accessibility_inspections_facility_accessibility_id_foreign` FOREIGN KEY (`facility_accessibility_id`)
    REFERENCES `facility_accessibilities` (`facility_accessibility_id`) ON DELETE CASCADE,
  CONSTRAINT `accessibility_inspections_inspector_id_foreign` FOREIGN KEY (`inspector_id`)
    REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- issue_categories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `issue_categories`;
CREATE TABLE `issue_categories` (
  `issue_category_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name`     VARCHAR(255) NOT NULL,
  `description`       TEXT NULL,
  `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`issue_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- reporting_periods
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reporting_periods`;
CREATE TABLE `reporting_periods` (
  `period_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `period_name` VARCHAR(255) NOT NULL,
  `start_date`  DATE NOT NULL,
  `end_date`    DATE NOT NULL,
  PRIMARY KEY (`period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- verification_statuses
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `verification_statuses`;
CREATE TABLE `verification_statuses` (
  `status_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- reports
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `report_id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_number`     VARCHAR(255) NOT NULL,
  `user_id`           BIGINT UNSIGNED NOT NULL,
  `facility_id`       BIGINT UNSIGNED NOT NULL,
  `issue_category_id` BIGINT UNSIGNED NOT NULL,
  `status_id`         BIGINT UNSIGNED NOT NULL,
  `period_id`         BIGINT UNSIGNED NOT NULL,
  `description`       TEXT NOT NULL,
  `created_at`        TIMESTAMP NULL,
  `updated_at`        TIMESTAMP NULL,
  PRIMARY KEY (`report_id`),
  UNIQUE KEY `reports_report_number_unique` (`report_number`),
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`),
  CONSTRAINT `reports_facility_id_foreign` FOREIGN KEY (`facility_id`)
    REFERENCES `facilities` (`facility_id`),
  CONSTRAINT `reports_issue_category_id_foreign` FOREIGN KEY (`issue_category_id`)
    REFERENCES `issue_categories` (`issue_category_id`),
  CONSTRAINT `reports_status_id_foreign` FOREIGN KEY (`status_id`)
    REFERENCES `verification_statuses` (`status_id`),
  CONSTRAINT `reports_period_id_foreign` FOREIGN KEY (`period_id`)
    REFERENCES `reporting_periods` (`period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- report_evidences
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `report_evidences`;
CREATE TABLE `report_evidences` (
  `evidence_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id`   BIGINT UNSIGNED NOT NULL,
  `file_path`   TEXT NOT NULL,
  `file_type`   VARCHAR(255) NULL,
  `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `caption`     TEXT NULL,
  PRIMARY KEY (`evidence_id`),
  CONSTRAINT `report_evidences_report_id_foreign` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`report_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- verifications
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `verifications`;
CREATE TABLE `verifications` (
  `verification_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id`           BIGINT UNSIGNED NOT NULL,
  `verifier_id`         BIGINT UNSIGNED NOT NULL,
  `verification_result` VARCHAR(255) NULL,
  `notes`               TEXT NULL,
  `verified_at`         DATETIME NULL,
  PRIMARY KEY (`verification_id`),
  CONSTRAINT `verifications_report_id_foreign` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`report_id`) ON DELETE CASCADE,
  CONSTRAINT `verifications_verifier_id_foreign` FOREIGN KEY (`verifier_id`)
    REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- verification_histories
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `verification_histories`;
CREATE TABLE `verification_histories` (
  `history_id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `verification_id`    BIGINT UNSIGNED NOT NULL,
  `actor_id`           BIGINT UNSIGNED NOT NULL,
  `previous_status_id` BIGINT UNSIGNED NULL,
  `new_status_id`      BIGINT UNSIGNED NOT NULL,
  `notes`              TEXT NULL,
  `changed_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  CONSTRAINT `verification_histories_verification_id_foreign` FOREIGN KEY (`verification_id`)
    REFERENCES `verifications` (`verification_id`) ON DELETE CASCADE,
  CONSTRAINT `verification_histories_actor_id_foreign` FOREIGN KEY (`actor_id`)
    REFERENCES `users` (`id`),
  CONSTRAINT `verification_histories_previous_status_id_foreign` FOREIGN KEY (`previous_status_id`)
    REFERENCES `verification_statuses` (`status_id`),
  CONSTRAINT `verification_histories_new_status_id_foreign` FOREIGN KEY (`new_status_id`)
    REFERENCES `verification_statuses` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- follow_up_assignments
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `follow_up_assignments`;
CREATE TABLE `follow_up_assignments` (
  `assignment_id`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_id`         BIGINT UNSIGNED NOT NULL,
  `assigned_to`       BIGINT UNSIGNED NOT NULL,
  `assigned_by`       BIGINT UNSIGNED NOT NULL,
  `assignment_status` VARCHAR(255) NOT NULL DEFAULT 'assigned',
  `assigned_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date`          DATE NULL,
  PRIMARY KEY (`assignment_id`),
  CONSTRAINT `follow_up_assignments_report_id_foreign` FOREIGN KEY (`report_id`)
    REFERENCES `reports` (`report_id`) ON DELETE CASCADE,
  CONSTRAINT `follow_up_assignments_assigned_to_foreign` FOREIGN KEY (`assigned_to`)
    REFERENCES `users` (`id`),
  CONSTRAINT `follow_up_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`)
    REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- follow_up_progress
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `follow_up_progress`;
CREATE TABLE `follow_up_progress` (
  `progress_id`        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_id`      BIGINT UNSIGNED NOT NULL,
  `actor_id`           BIGINT UNSIGNED NOT NULL,
  `progress_status`    VARCHAR(255) NULL,
  `action_description` TEXT NULL,
  `recorded_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  CONSTRAINT `follow_up_progress_assignment_id_foreign` FOREIGN KEY (`assignment_id`)
    REFERENCES `follow_up_assignments` (`assignment_id`) ON DELETE CASCADE,
  CONSTRAINT `follow_up_progress_actor_id_foreign` FOREIGN KEY (`actor_id`)
    REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- follow_up_evidences
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `follow_up_evidences`;
CREATE TABLE `follow_up_evidences` (
  `follow_up_evidence_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `progress_id`           BIGINT UNSIGNED NOT NULL,
  `file_path`             TEXT NOT NULL,
  `file_type`             VARCHAR(255) NULL,
  `uploaded_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description`           TEXT NULL,
  PRIMARY KEY (`follow_up_evidence_id`),
  CONSTRAINT `follow_up_evidences_progress_id_foreign` FOREIGN KEY (`progress_id`)
    REFERENCES `follow_up_progress` (`progress_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- BAGIAN 2: TABEL BAWAAN LARAVEL
-- (dibutuhkan karena SESSION_DRIVER, CACHE_STORE, QUEUE_CONNECTION = database)
-- =====================================================================

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id`            VARCHAR(255) NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL,
  `ip_address`    VARCHAR(45) NULL,
  `user_agent`    TEXT NULL,
  `payload`       LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key`        VARCHAR(255) NOT NULL,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255) NOT NULL,
  `payload`      LONGTEXT NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at`   INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id`             VARCHAR(255) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `total_jobs`     INT NOT NULL,
  `pending_jobs`   INT NOT NULL,
  `failed_jobs`    INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options`        MEDIUMTEXT NULL,
  `cancelled_at`   INT NULL,
  `created_at`     INT NOT NULL,
  `finished_at`    INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue`      TEXT NOT NULL,
  `payload`    LONGTEXT NOT NULL,
  `exception`  LONGTEXT NOT NULL,
  `failed_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- =====================================================================
-- BAGIAN 3: DATA MASTER AWAL (sama dengan MasterDataSeeder; isinya contoh)
-- =====================================================================

INSERT INTO `roles` (`role_name`) VALUES
  ('Admin'),
  ('Masyarakat'),
  ('Verifikator'),
  ('Petugas Tindak Lanjut');

INSERT INTO `verification_statuses` (`status_name`) VALUES
  ('Menunggu Verifikasi'),
  ('Terverifikasi'),
  ('Ditolak'),
  ('Dalam Tindak Lanjut'),
  ('Selesai'),
  ('Memerlukan Pemeriksaan Lanjutan'),
  ('Dibatalkan');

INSERT INTO `facility_categories` (`category_name`) VALUES
  ('Taman'),
  ('Fasilitas Kesehatan'),
  ('Pendidikan'),
  ('Transportasi Umum'),
  ('Perkantoran Pemerintah');

INSERT INTO `accessibility_types` (`type_name`) VALUES
  ('Ramp / Jalur Kursi Roda'),
  ('Toilet Difabel'),
  ('Guiding Block'),
  ('Parkir Difabel'),
  ('Lift'),
  ('Handrail');

INSERT INTO `issue_categories` (`category_name`) VALUES
  ('Fasilitas rusak'),
  ('Fasilitas tidak tersedia'),
  ('Akses terhalang'),
  ('Informasi tidak sesuai');

-- Catatan: user (akun) tidak dimasukkan di sini karena password harus berupa
-- hash bcrypt. Buat user lewat seeder Laravel (php artisan db:seed) atau
-- halaman registrasi aplikasi.


-- =====================================================================
-- BAGIAN 4: QUERY CONTOH (untuk laporan / uji coba)
-- =====================================================================

-- 4.1 Daftar fasilitas beserta kategori dan lokasinya
SELECT f.facility_id, f.name, c.category_name, f.address,
       l.region, l.latitude, l.longitude
FROM facilities f
JOIN facility_categories c ON c.category_id = f.category_id
LEFT JOIN facility_locations l ON l.facility_id = f.facility_id;

-- 4.2 Daftar laporan beserta pelapor, fasilitas, kategori masalah, dan status
SELECT r.report_number, u.name AS pelapor, f.name AS fasilitas,
       ic.category_name AS kategori_masalah, vs.status_name AS status,
       r.created_at
FROM reports r
JOIN users u                 ON u.id = r.user_id
JOIN facilities f            ON f.facility_id = r.facility_id
JOIN issue_categories ic     ON ic.issue_category_id = r.issue_category_id
JOIN verification_statuses vs ON vs.status_id = r.status_id
ORDER BY r.created_at DESC;

-- 4.3 Jumlah laporan per status
SELECT vs.status_name, COUNT(r.report_id) AS jumlah
FROM verification_statuses vs
LEFT JOIN reports r ON r.status_id = vs.status_id
GROUP BY vs.status_id, vs.status_name;

-- 4.4 Penugasan tindak lanjut beserta petugas dan progres terakhir
SELECT a.assignment_id, r.report_number,
       ut.name AS petugas, ub.name AS ditugaskan_oleh,
       a.assignment_status, a.due_date,
       (SELECT p.progress_status
          FROM follow_up_progress p
         WHERE p.assignment_id = a.assignment_id
         ORDER BY p.recorded_at DESC
         LIMIT 1) AS progres_terakhir
FROM follow_up_assignments a
JOIN reports r ON r.report_id = a.report_id
JOIN users ut  ON ut.id = a.assigned_to
JOIN users ub  ON ub.id = a.assigned_by;

-- 4.5 Riwayat perubahan status verifikasi suatu laporan (ganti angka 1)
SELECT h.changed_at, ps.status_name AS status_lama, ns.status_name AS status_baru,
       u.name AS diubah_oleh, h.notes
FROM verification_histories h
JOIN verifications v ON v.verification_id = h.verification_id
LEFT JOIN verification_statuses ps ON ps.status_id = h.previous_status_id
JOIN verification_statuses ns ON ns.status_id = h.new_status_id
JOIN users u ON u.id = h.actor_id
WHERE v.report_id = 1
ORDER BY h.changed_at;
