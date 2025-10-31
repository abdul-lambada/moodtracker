CREATE DATABASE IF NOT EXISTS moodtracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moodtracker;

CREATE TABLE IF NOT EXISTS Posisi (
    id_posisi INT AUTO_INCREMENT PRIMARY KEY,
    nama_posisi VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_karyawan VARCHAR(150) NOT NULL,
    no_bundy VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_posisi INT NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'karyawan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_posisi FOREIGN KEY (id_posisi) REFERENCES Posisi (id_posisi) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Catatan_Harian (
    id_catatan INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    mood VARCHAR(50) NOT NULL,
    catatan_mood TEXT,
    output_harian TEXT,
    tanggal_catatan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_catatan_user FOREIGN KEY (id_user) REFERENCES Users (id_user) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Audit_Log (
    id_audit INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NULL,
    action VARCHAR(50) NOT NULL,
    entity VARCHAR(100) NOT NULL,
    entity_id INT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (id_user) REFERENCES Users (id_user) ON DELETE SET NULL ON UPDATE CASCADE
);

INSERT INTO Posisi (nama_posisi) VALUES
('Karu'),
('Skill Operator'),
('Stock Control'),
('Team Produksi')
ON DUPLICATE KEY UPDATE nama_posisi = VALUES(nama_posisi);

INSERT INTO Users (nama_karyawan, no_bundy, password_hash, id_posisi, role) VALUES
('Ayu Pratama', 'BD001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uhe.Wu/Fi', 1, 'admin'),
('Bima Saputra', 'BD002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uhe.Wu/Fi', 2, 'karyawan'),
('Citra Lestari', 'BD003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uhe.Wu/Fi', 3, 'karyawan')
ON DUPLICATE KEY UPDATE nama_karyawan = VALUES(nama_karyawan), id_posisi = VALUES(id_posisi), role = VALUES(role);

INSERT INTO Catatan_Harian (id_user, mood, catatan_mood, output_harian, tanggal_catatan) VALUES
(1, '🤩 Sangat Senang', 'Project dashboard selesai tepat waktu.', 'Merilis fitur analitik baru.', '2025-10-11 09:00:00'),
(2, '😄 Senang', NULL, 'Menyelesaikan laporan inventori mingguan.', '2025-10-11 10:30:00'),
(3, '😐 Netral', 'Menunggu sparepart datang.', 'Melakukan pengecekan mesin dan update status.', '2025-10-11 14:45:00')
ON DUPLICATE KEY UPDATE mood = VALUES(mood), catatan_mood = VALUES(catatan_mood), output_harian = VALUES(output_harian), tanggal_catatan = VALUES(tanggal_catatan);

INSERT INTO Audit_Log (id_user, action, entity, entity_id, description) VALUES
(1, 'seed', 'Initialization', NULL, 'Initial seed data created')
ON DUPLICATE KEY UPDATE description = VALUES(description);
