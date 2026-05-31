-- ============================================================
--  LuxeStay Group – Database Setup
--  Import this file in phpMyAdmin (WAMP)
--  Server: localhost | User: root | Password: (empty by default)
-- ============================================================

CREATE DATABASE IF NOT EXISTS luxestay_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE luxestay_db;

-- ── CLIENTS TABLE ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clients (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    full_name     VARCHAR(100)    NOT NULL,
    email         VARCHAR(150)    NOT NULL UNIQUE,
    phone         VARCHAR(30)     DEFAULT NULL,
    nationality   VARCHAR(100)    DEFAULT NULL,
    password_hash VARCHAR(255)    NOT NULL,
    service       ENUM(
                    'hotel',
                    'rent_car',
                    'school',
                    'company'
                  )               NOT NULL DEFAULT 'hotel',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                           ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_email (email),
    INDEX idx_service (service)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ── If upgrading an existing database, run this to add nationality column:
-- ALTER TABLE clients ADD COLUMN nationality VARCHAR(100) DEFAULT NULL AFTER phone;

-- ── SAMPLE DATA (optional – remove if not needed) ────────────
-- Password for both test accounts is:  Test@1234
INSERT INTO clients (full_name, email, phone, nationality, password_hash, service) VALUES
(
  'Alice Dupont',
  'alice@example.com',
  '+33 6 12 34 56 78',
  'France',
  '$2y$12$eImiTXuWVxfM37uY4JANjOe5XlR1R4F7Y3t9Gb8E6LkE3QFZzG8cy',
  'hotel'
),
(
  'Carlos Mendes',
  'carlos@example.com',
  '+34 612 345 678',
  'Spain',
  '$2y$12$eImiTXuWVxfM37uY4JANjOe5XlR1R4F7Y3t9Gb8E6LkE3QFZzG8cy',
  'rent_car'
);
