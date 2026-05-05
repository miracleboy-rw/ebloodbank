-- ============================================
-- E-BloodBank Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS ebloodbank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ebloodbank;

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(20) UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('donor', 'pmi', 'rs') NOT NULL DEFAULT 'donor',
    blood_type ENUM('A', 'B', 'AB', 'O') DEFAULT NULL,
    rhesus ENUM('+', '-') DEFAULT NULL,
    phone VARCHAR(20),
    address TEXT,
    last_donation DATE DEFAULT NULL,
    total_donations INT DEFAULT 0,
    hospital_name VARCHAR(150) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- EVENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    quota INT NOT NULL DEFAULT 50,
    booked INT DEFAULT 0,
    created_by INT NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- BOOKINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    booking_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    qr_code VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('pending', 'confirmed', 'screened', 'donated', 'cancelled', 'failed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- BLOOD STOCK TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS blood_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_type ENUM('A', 'B', 'AB', 'O') NOT NULL,
    rhesus ENUM('+', '-') NOT NULL,
    component ENUM('Whole Blood', 'PRC', 'Trombosit', 'FFP', 'WB') DEFAULT 'Whole Blood',
    quantity INT DEFAULT 0,
    min_stock INT DEFAULT 10,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_stock (blood_type, rhesus, component)
) ENGINE=InnoDB;

-- ============================================
-- REQUESTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    blood_type ENUM('A', 'B', 'AB', 'O') NOT NULL,
    rhesus ENUM('+', '-') NOT NULL,
    component ENUM('Whole Blood', 'PRC', 'Trombosit', 'FFP', 'WB') DEFAULT 'Whole Blood',
    quantity INT NOT NULL,
    urgency ENUM('normal', 'emergency') DEFAULT 'normal',
    status ENUM('pending', 'approved', 'rejected', 'fulfilled', 'cancelled') DEFAULT 'pending',
    patient_name VARCHAR(100),
    patient_age INT,
    diagnosis TEXT,
    notes TEXT,
    approved_by INT DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- SCREENINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS screenings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    hb DECIMAL(4,1) NOT NULL,
    tensi_sistolik INT NOT NULL,
    tensi_diastolik INT NOT NULL,
    weight DECIMAL(5,2),
    temperature DECIMAL(4,1),
    pulse INT,
    status ENUM('pass', 'fail') NOT NULL,
    fail_reason TEXT,
    screened_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (screened_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- ACTIVITY LOGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Default Admin PMI (password: admin123)
INSERT INTO users (nik, name, email, password, role, blood_type, rhesus, phone) VALUES
('3171000000001', 'Admin PMI Jakarta', 'admin@pmi.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pmi', 'O', '+', '081234567890'),
('3171000000002', 'RSUP Dr. Cipto', 'admin@rscipto.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rs', NULL, NULL, '021-3456789'),
('3171000000003', 'Budi Santoso', 'budi@donor.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'A', '+', '081298765432');

UPDATE users SET hospital_name = 'RSUP Dr. Cipto Mangunkusumo' WHERE role = 'rs';
UPDATE users SET last_donation = '2025-12-01', total_donations = 5 WHERE name = 'Budi Santoso';

-- Blood Stock Initial Data
INSERT INTO blood_stock (blood_type, rhesus, component, quantity, min_stock) VALUES
('A', '+', 'Whole Blood', 45, 10),
('A', '-', 'Whole Blood', 8, 5),
('B', '+', 'Whole Blood', 32, 10),
('B', '-', 'Whole Blood', 5, 5),
('AB', '+', 'Whole Blood', 15, 5),
('AB', '-', 'Whole Blood', 3, 3),
('O', '+', 'Whole Blood', 60, 15),
('O', '-', 'Whole Blood', 12, 8),
('A', '+', 'PRC', 20, 5),
('B', '+', 'PRC', 18, 5),
('AB', '+', 'PRC', 10, 3),
('O', '+', 'PRC', 35, 10),
('A', '+', 'Trombosit', 8, 3),
('B', '+', 'Trombosit', 6, 3),
('O', '+', 'Trombosit', 12, 5);

-- Sample Events
INSERT INTO events (title, description, location, date, start_time, end_time, quota, created_by) VALUES
('Donor Darah Sukarela April 2026', 'Kegiatan donor darah rutin bulanan PMI Jakarta. Mari bergabung dan selamatkan nyawa!', 'Gedung PMI DKI Jakarta, Jl. Kramat Raya No. 47', '2026-04-25', '08:00:00', '14:00:00', 100, 1),
('Donor Darah HUT Kemerdekaan', 'Peringatan HUT RI ke-81, mari berbagi dengan sesama melalui donor darah.', 'Balai Kota Jakarta, Jl. Medan Merdeka Selatan', '2026-05-10', '07:00:00', '12:00:00', 150, 1),
('Mobile Blood Drive - Mall Taman Anggrek', 'Unit donor darah mobile hadir di Mall Taman Anggrek. Donor sambil belanja!', 'Mall Taman Anggrek Lt. 1 Atrium, Jakarta Barat', '2026-04-28', '10:00:00', '17:00:00', 80, 1);

-- ============================================
-- SEED BOOKINGS
-- ============================================
INSERT INTO bookings (user_id, event_id, qr_code, status, notes) VALUES
(3, 1, 'QR-BUD001ABC', 'donated',   'Donor pertama di event April'),
(3, 2, 'QR-BUD002DEF', 'confirmed', 'Terkonfirmasi, menunggu hari-H'),
(3, 3, 'QR-BUD003GHI', 'pending',   NULL);

-- Update event booked count accordingly
UPDATE events SET booked = 1 WHERE id = 1;
UPDATE events SET booked = 1 WHERE id = 2;
UPDATE events SET booked = 1 WHERE id = 3;

-- ============================================
-- SEED SCREENINGS
-- ============================================
INSERT INTO screenings (booking_id, hb, tensi_sistolik, tensi_diastolik, weight, temperature, pulse, status, fail_reason, screened_by) VALUES
(1, 14.5, 120, 80, 68.0, 36.5, 72, 'pass', NULL, 1),
(2, 13.8, 118, 76, 65.5, 36.7, 74, 'pass', NULL, 1),
(3, 11.2, 100, 70, 55.0, 37.1, 88, 'fail', 'HB di bawah batas minimal (12.5 g/dL)', 1);

-- Update booking status based on screening
UPDATE bookings SET status='screened' WHERE id=2;

-- ============================================
-- SEED REQUESTS
-- ============================================
INSERT INTO requests (hospital_id, blood_type, rhesus, component, quantity, urgency, patient_name, patient_age, diagnosis, notes, status, approved_by, approved_at) VALUES
(2, 'A',  '+', 'Whole Blood', 3, 'normal',    'Ahmad Fauzi',  45, 'Anemia berat pra-operasi',     'Dibutuhkan sebelum operasi tanggal 30 April', 'approved',  1, NOW()),
(2, 'O',  '+', 'PRC',         5, 'emergency', 'Siti Rahayu',  32, 'Perdarahan pasca melahirkan',  'SEGERA — kondisi kritis', 'fulfilled', 1, NOW()),
(2, 'B',  '-', 'Trombosit',   2, 'normal',    'Bapak Surono', 60, 'Trombositopenia',              NULL, 'pending', NULL, NULL);

-- ============================================
-- SEED ACTIVITY LOGS
-- ============================================
INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES
(1, 'LOGIN',          'User login berhasil',                                    '127.0.0.1'),
(2, 'LOGIN',          'User login berhasil',                                    '127.0.0.1'),
(3, 'LOGIN',          'User login berhasil',                                    '127.0.0.1'),
(3, 'REGISTER',       'Akun baru terdaftar: budi@donor.id',                     '127.0.0.1'),
(3, 'BOOKING_CREATE', 'Booking event #1 — QR: QR-BUD001ABC',                   '127.0.0.1'),
(2, 'REQUEST_CREATE', 'Request #1: A+ 3 ktg (normal)',                          '127.0.0.1'),
(2, 'REQUEST_CREATE', 'Request #2: O+ 5 ktg (emergency)',                       '127.0.0.1'),
(1, 'REQUEST_APPROVE','Approve request #1 dari RSUP Dr. Cipto',                 '127.0.0.1'),
(1, 'STOCK_UPDATE',   'Update stok O+ Whole Blood: +10 kantong',                '127.0.0.1'),
(1, 'EXPORT_CSV',     'Export CSV laporan bulan April 2026',                    '127.0.0.1');

