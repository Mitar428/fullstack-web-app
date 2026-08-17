-- ============================================================
--  Medicinska Klinika – Kompletan SQL Schema
--  Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Kreiranje baze
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS medical_clinic
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE medical_clinic;

-- ============================================================
-- 1. ROLES
--    Nivoi pristupa: admin, manager, user (pacijent)
-- ============================================================
CREATE TABLE roles (
    id        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name      VARCHAR(50)     NOT NULL COMMENT 'Ime uloge (Admin, Menadzer, Pacijent)',
    slug      VARCHAR(50)     NOT NULL COMMENT 'Identifikator: admin | manager | user',
    created_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Uloge korisnika – RBAC';

-- ------------------------------------------------------------
INSERT INTO roles (name, slug) VALUES
    ('Administrator', 'admin'),
    ('Menadžer',      'manager'),
    ('Pacijent',      'user');

-- ============================================================
-- 2. USERS
--    Centralna tabela za sve korisnike sistema
-- ============================================================
CREATE TABLE users (
    id             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    role_id        INT UNSIGNED   NOT NULL,
    first_name     VARCHAR(100)   NOT NULL,
    last_name      VARCHAR(100)   NOT NULL,
    email          VARCHAR(191)   NOT NULL,
    password_hash  VARCHAR(255)   NOT NULL COMMENT 'bcrypt hash lozinke',
    is_active      TINYINT(1)     NOT NULL DEFAULT 1 COMMENT '1=aktivan, 0=deaktiviran',
    created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Svi korisnici sistema (admin, menadzer, pacijent)';

-- ------------------------------------------------------------
-- Demo korisnici (lozinka za sve: Password123!)
-- Hash generisan sa password_hash('Password123!', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES
    (1, 'Nikola',  'Adminović',  'admin@klinika.rs',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (2, 'Marija',  'Menadžić',   'manager@klinika.rs',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (2, 'Stefan',  'Popović',    'manager2@klinika.rs', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (3, 'Ana',     'Jovanović',  'ana@email.com',       '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (3, 'Petar',   'Nikolić',    'petar@email.com',     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (3, 'Jovana',  'Đorđević',   'jovana@email.com',    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
-- 3. SPECIALIZATIONS
--    Medicinske specijalnosti lekara
-- ============================================================
CREATE TABLE specializations (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)   NOT NULL,
    description TEXT,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_spec_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Medicinske specijalnosti';

-- ------------------------------------------------------------
INSERT INTO specializations (name, description) VALUES
    ('Opšta medicina',       'Primarna zdravstvena zaštita i opšti pregledi'),
    ('Kardiologija',         'Dijagnostika i lečenje bolesti srca i krvnih sudova'),
    ('Neurologija',          'Bolesti nervnog sistema'),
    ('Ortopedija',           'Bolesti i povrede koštano-mišićnog sistema'),
    ('Dermatologija',        'Bolesti kože, kose i noktiju'),
    ('Pedijatrija',          'Zdravstvena zaštita dece i adolescenata'),
    ('Oftalmologija',        'Bolesti i poremećaji vida'),
    ('Psihijatrija',         'Mentalni poremećaji i psihijatrijske bolesti'),
    ('Ginekologija',         'Ženski reproduktivni sistem'),
    ('Gastroenterologija',   'Bolesti digestivnog sistema');

-- ============================================================
-- 4. DOCTORS
--    Proširenje users tabele za lekare
--    (One-to-One sa users, One-to-Many sa specializations)
-- ============================================================
CREATE TABLE doctors (
    id                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED   NOT NULL,
    specialization_id   INT UNSIGNED   NOT NULL,
    license_number      VARCHAR(50)    NOT NULL COMMENT 'Broj licence lekara',
    phone               VARCHAR(20),
    bio                 TEXT           COMMENT 'Kratka biografija',
    working_hours_from  TIME           NOT NULL DEFAULT '08:00:00',
    working_hours_to    TIME           NOT NULL DEFAULT '16:00:00',
    created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doctors_user    (user_id),
    UNIQUE KEY uq_doctors_license (license_number),
    KEY idx_doctors_spec (specialization_id),
    CONSTRAINT fk_doctors_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_doctors_spec
        FOREIGN KEY (specialization_id) REFERENCES specializations (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Profili lekara';

-- ------------------------------------------------------------
-- Demo lekari (kreirani kao users sa role_id=2 ili posebno)
-- Za demo – koristimo nove users zapise za lekare
-- ------------------------------------------------------------
INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES
    (2, 'Dr. Milena',  'Stojanović', 'dr.stojanovic@klinika.rs', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (2, 'Dr. Zoran',   'Lukić',      'dr.lukic@klinika.rs',      '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
    (2, 'Dr. Vesna',   'Marković',   'dr.markovic@klinika.rs',   '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- user id-ovi 7, 8, 9

INSERT INTO doctors (user_id, specialization_id, license_number, phone, bio) VALUES
    (7, 2, 'KRD-2018-001', '+381641234567', 'Specijalista kardiologije sa 10 godina iskustva.'),
    (8, 3, 'NEU-2015-042', '+381652345678', 'Neurolog, subspecijalista za epilepsiju.'),
    (9, 1, 'OPM-2020-115', '+381663456789', 'Lekar opšte medicine, izabrani lekar za odrasle.');

-- ============================================================
-- 5. PATIENTS
--    Proširenje users tabele za pacijente
-- ============================================================
CREATE TABLE patients (
    id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED   NOT NULL,
    date_of_birth DATE           NOT NULL,
    gender        ENUM('M','F','Other') NOT NULL DEFAULT 'Other',
    blood_type    ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown') NOT NULL DEFAULT 'Unknown',
    address       VARCHAR(255),
    phone         VARCHAR(20),
    allergies     TEXT           COMMENT 'Poznate alergije pacijenta',
    created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_patients_user (user_id),
    CONSTRAINT fk_patients_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Profili pacijenata';

-- ------------------------------------------------------------
INSERT INTO patients (user_id, date_of_birth, gender, blood_type, address, phone, allergies) VALUES
    (4, '1990-05-15', 'F', 'A+',      'Bulevar Oslobođenja 12, Novi Sad',    '+381601111111', 'Penicilin'),
    (5, '1985-11-22', 'M', 'O+',      'Knez Mihailova 5, Beograd',           '+381602222222', NULL),
    (6, '1995-03-08', 'F', 'B-',      'Njegoševa 30, Niš',                   '+381603333333', 'Polen, prašina');

-- ============================================================
-- 6. APPOINTMENTS
--    Zakazani termini između pacijenta i lekara
-- ============================================================
CREATE TABLE appointments (
    id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    patient_id    INT UNSIGNED   NOT NULL,
    doctor_id     INT UNSIGNED   NOT NULL,
    scheduled_at  DATETIME       NOT NULL COMMENT 'Datum i vreme termina',
    duration_min  SMALLINT       NOT NULL DEFAULT 30 COMMENT 'Trajanje u minutima',
    status        ENUM('pending','confirmed','completed','cancelled','no_show')
                                 NOT NULL DEFAULT 'pending',
    reason        VARCHAR(255)   COMMENT 'Razlog posete',
    notes         TEXT           COMMENT 'Beleške lekara',
    created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_appt_patient   (patient_id),
    KEY idx_appt_doctor    (doctor_id),
    KEY idx_appt_scheduled (scheduled_at),
    KEY idx_appt_status    (status),
    CONSTRAINT fk_appt_patient
        FOREIGN KEY (patient_id) REFERENCES patients (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_appt_doctor
        FOREIGN KEY (doctor_id) REFERENCES doctors (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Zakazani termini';

-- ------------------------------------------------------------
INSERT INTO appointments (patient_id, doctor_id, scheduled_at, duration_min, status, reason) VALUES
    (1, 3, '2025-04-10 09:00:00', 30, 'completed',  'Redovni pregled'),
    (2, 1, '2025-04-12 10:30:00', 45, 'completed',  'Bol u grudima'),
    (3, 2, '2025-04-15 11:00:00', 30, 'confirmed',  'Glavobolje'),
    (1, 1, '2025-05-05 14:00:00', 30, 'pending',    'Kontrolni pregled'),
    (2, 3, '2025-05-07 08:30:00', 30, 'cancelled',  'Gripa'),
    (3, 1, '2025-05-20 15:00:00', 45, 'pending',    'Srčani ritam');

-- ============================================================
-- 7. DIAGNOSES
--    Katalog dijagnoza (ICD-10 kodovi)
-- ============================================================
CREATE TABLE diagnoses (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    icd_code    VARCHAR(10)    NOT NULL COMMENT 'ICD-10 kod npr. J06.9',
    name        VARCHAR(200)   NOT NULL,
    description TEXT,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_diag_icd (icd_code),
    FULLTEXT KEY ft_diag_name (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Katalog dijagnoza (ICD-10)';

-- ------------------------------------------------------------
INSERT INTO diagnoses (icd_code, name, description) VALUES
    ('J06.9',  'Akutna infekcija gornjeg respiratornog trakta', 'Prehlada, faringitis, laringitis'),
    ('I10',    'Esencijalna hipertenzija',                      'Povišen krvni pritisak bez poznatog uzroka'),
    ('E11',    'Dijabetes melitus tip 2',                       'Poremećaj metabolizma glukoze'),
    ('G43.9',  'Migrena',                                       'Ponavljajuće jake glavobolje'),
    ('M54.5',  'Bol u donjem delu leđa',                        'Lumbalni bolni sindrom'),
    ('F32',    'Depresivna epizoda',                            'Poremećaj raspoloženja'),
    ('K21.0',  'Gastroezofagealni refluks',                     'GERB sa ezofagitisom'),
    ('J45.9',  'Astma',                                         'Hronična upalna bolest disajnih puteva'),
    ('I25.1',  'Aterosklerotska bolest srca',                   'Koronarna arterijska bolest'),
    ('L30.9',  'Dermatitis',                                    'Upala kože nespecifikovana');

-- ============================================================
-- 8. MEDICAL_RECORDS
--    Medicinski kartoni – kreirati za svaki završen termin
-- ============================================================
CREATE TABLE medical_records (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    patient_id      INT UNSIGNED   NOT NULL,
    doctor_id       INT UNSIGNED   NOT NULL,
    appointment_id  INT UNSIGNED            COMMENT 'Može biti NULL za retroaktivne unose',
    symptoms        TEXT           NOT NULL COMMENT 'Opis simptoma',
    treatment       TEXT                    COMMENT 'Propisana terapija',
    prescription    TEXT                    COMMENT 'Recept / propisani lekovi',
    follow_up_date  DATE                    COMMENT 'Datum kontrolnog pregleda',
    is_confidential TINYINT(1)     NOT NULL DEFAULT 0 COMMENT 'Osetljiv zapis',
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mr_patient     (patient_id),
    KEY idx_mr_doctor      (doctor_id),
    KEY idx_mr_appointment (appointment_id),
    CONSTRAINT fk_mr_patient
        FOREIGN KEY (patient_id) REFERENCES patients (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_mr_doctor
        FOREIGN KEY (doctor_id) REFERENCES doctors (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_mr_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointments (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Medicinski kartoni pacijenata';

-- ------------------------------------------------------------
INSERT INTO medical_records (patient_id, doctor_id, appointment_id, symptoms, treatment, prescription, follow_up_date) VALUES
    (1, 3, 1, 'Umor, blagi kašalj, temperatura 37.5°C', 'Odmor, povećan unos tečnosti', 'Paracetamol 500mg 3x1', '2025-05-10'),
    (2, 1, 2, 'Bol u grudima pri naporu, otežano disanje', 'EKG, holter monitor 24h', 'Aspirin 100mg 1x1, Atorvastatin 20mg 1x1', '2025-05-12');

-- ============================================================
-- 9. RECORD_DIAGNOSES
--    Vezna tabela: medical_records <–> diagnoses (N:M)
--    Jedan karton može imati više dijagnoza
-- ============================================================
CREATE TABLE record_diagnoses (
    id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    record_id     INT UNSIGNED   NOT NULL,
    diagnosis_id  INT UNSIGNED   NOT NULL,
    severity      ENUM('mild','moderate','severe') NOT NULL DEFAULT 'mild',
    note          VARCHAR(255)   COMMENT 'Napomena uz dijagnozu',
    PRIMARY KEY (id),
    UNIQUE KEY uq_rd_record_diag (record_id, diagnosis_id),
    KEY idx_rd_diagnosis (diagnosis_id),
    CONSTRAINT fk_rd_record
        FOREIGN KEY (record_id) REFERENCES medical_records (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_rd_diagnosis
        FOREIGN KEY (diagnosis_id) REFERENCES diagnoses (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Dijagnoze vezane za medicinski karton';

-- ------------------------------------------------------------
INSERT INTO record_diagnoses (record_id, diagnosis_id, severity) VALUES
    (1, 1, 'mild'),
    (2, 2, 'moderate'),
    (2, 9, 'moderate');

-- ============================================================
-- 10. MESSAGES
--     Interna poruka između korisnika (pacijent ↔ menadžer/admin)
--     Self-referencing FK na users
-- ============================================================
CREATE TABLE messages (
    id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    sender_id    INT UNSIGNED   NOT NULL,
    receiver_id  INT UNSIGNED   NOT NULL,
    subject      VARCHAR(200)   NOT NULL DEFAULT '(bez naslova)',
    body         TEXT           NOT NULL,
    is_read      TINYINT(1)     NOT NULL DEFAULT 0,
    parent_id    INT UNSIGNED            COMMENT 'Za threading – odgovor na poruku',
    sent_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_msg_sender   (sender_id),
    KEY idx_msg_receiver (receiver_id),
    KEY idx_msg_parent   (parent_id),
    CONSTRAINT fk_msg_sender
        FOREIGN KEY (sender_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_msg_receiver
        FOREIGN KEY (receiver_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_msg_parent
        FOREIGN KEY (parent_id) REFERENCES messages (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Interne poruke između korisnika';

-- ------------------------------------------------------------
INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES
    (4, 2, 'Pitanje o terminu', 'Poštovani, da li je moguće pomeriti termin od 5. maja?'),
    (2, 4, 'Re: Pitanje o terminu', 'Naravno, slobodni termin je 8. maja u 10:00h. Da potvrdimo?');

-- ============================================================
-- 11. ACTIVITY_LOG
--     Svaka akcija korisnika se beleži ovde
--     Ovo je srce zahteva #6 – log korisničkih akcija
-- ============================================================
CREATE TABLE activity_log (
    id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED            COMMENT 'NULL = gost/sistem',
    action       VARCHAR(100)   NOT NULL COMMENT 'npr. user.login, appointment.create',
    entity_type  VARCHAR(50)             COMMENT 'Tabela: appointment, patient...',
    entity_id    INT UNSIGNED            COMMENT 'ID entiteta na kojeg se akcija odnosi',
    details      JSON                    COMMENT 'Dodatni podaci u JSON formatu',
    ip_address   VARCHAR(45)             COMMENT 'IPv4 ili IPv6',
    user_agent   VARCHAR(255),
    created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_user    (user_id),
    KEY idx_log_action  (action),
    KEY idx_log_created (created_at),
    CONSTRAINT fk_log_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Log svih korisničkih akcija';

-- ------------------------------------------------------------
INSERT INTO activity_log (user_id, action, entity_type, entity_id, ip_address, details) VALUES
    (1, 'user.login',           NULL,          NULL, '192.168.1.1', '{"status":"success"}'),
    (4, 'appointment.create',   'appointment',    4, '10.0.0.5',   '{"doctor_id":1,"scheduled_at":"2025-05-05 14:00:00"}'),
    (2, 'appointment.cancel',   'appointment',    5, '192.168.1.2', '{"reason":"pacijent otkazao"}'),
    (1, 'user.create',          'user',           6, '192.168.1.1', '{"email":"jovana@email.com","role":"user"}');

-- ============================================================
-- 12. DOCTOR_UNAVAILABILITY  (bonus tabela)
--     Neradni dani i odmori lekara – sprečava double-booking
-- ============================================================
CREATE TABLE doctor_unavailability (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    doctor_id   INT UNSIGNED   NOT NULL,
    date_from   DATETIME       NOT NULL,
    date_to     DATETIME       NOT NULL,
    reason      VARCHAR(255)   COMMENT 'Godišnji odmor, kongres, bolovanje...',
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_unavail_doctor (doctor_id),
    KEY idx_unavail_dates  (date_from, date_to),
    CONSTRAINT fk_unavail_doctor
        FOREIGN KEY (doctor_id) REFERENCES doctors (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT chk_unavail_dates
        CHECK (date_to > date_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Neraspoloživost lekara (odmor, bolovanje)';

-- ============================================================
-- Korisni VIEW-ovi za izveštaje
-- ============================================================

-- Pregled termina sa imenima pacijenta i lekara
CREATE OR REPLACE VIEW v_appointments_full AS
SELECT
    a.id,
    a.scheduled_at,
    a.duration_min,
    a.status,
    a.reason,
    CONCAT(pu.first_name, ' ', pu.last_name)  AS patient_name,
    CONCAT(du.first_name, ' ', du.last_name)  AS doctor_name,
    s.name                                     AS specialization,
    a.created_at
FROM appointments a
JOIN patients  p  ON p.id   = a.patient_id
JOIN users     pu ON pu.id  = p.user_id
JOIN doctors   d  ON d.id   = a.doctor_id
JOIN users     du ON du.id  = d.user_id
JOIN specializations s ON s.id = d.specialization_id;

-- Pregled kartona sa dijagnozama
CREATE OR REPLACE VIEW v_records_with_diagnoses AS
SELECT
    mr.id              AS record_id,
    mr.created_at      AS record_date,
    CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name,
    CONCAT(du.first_name, ' ', du.last_name) AS doctor_name,
    mr.symptoms,
    mr.treatment,
    mr.prescription,
    GROUP_CONCAT(
        CONCAT(diag.icd_code, ' – ', diag.name)
        ORDER BY diag.icd_code
        SEPARATOR ' | '
    ) AS diagnoses
FROM medical_records mr
JOIN patients    p    ON p.id   = mr.patient_id
JOIN users       pu   ON pu.id  = p.user_id
JOIN doctors     d    ON d.id   = mr.doctor_id
JOIN users       du   ON du.id  = d.user_id
LEFT JOIN record_diagnoses rd   ON rd.record_id    = mr.id
LEFT JOIN diagnoses        diag ON diag.id         = rd.diagnosis_id
GROUP BY mr.id;

-- Statistika po lekaru
CREATE OR REPLACE VIEW v_doctor_stats AS
SELECT
    d.id                                        AS doctor_id,
    CONCAT(u.first_name, ' ', u.last_name)      AS doctor_name,
    s.name                                      AS specialization,
    COUNT(a.id)                                 AS total_appointments,
    SUM(a.status = 'completed')                 AS completed,
    SUM(a.status = 'cancelled')                 AS cancelled,
    SUM(a.status = 'no_show')                   AS no_show
FROM doctors d
JOIN users          u ON u.id  = d.user_id
JOIN specializations s ON s.id = d.specialization_id
LEFT JOIN appointments a ON a.doctor_id = d.id
GROUP BY d.id;

-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;
-- ============================================================
-- KRAJ SKRIPTE
-- Tabele: roles, users, specializations, doctors, patients,
--         appointments, diagnoses, medical_records,
--         record_diagnoses, messages, activity_log,
--         doctor_unavailability
-- View-ovi: v_appointments_full, v_records_with_diagnoses,
--           v_doctor_stats
-- ============================================================