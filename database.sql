-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 06 Nov 2025 pada 12.44
-- Versi server: 10.6.21-MariaDB-cll-lve
-- Versi PHP: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dpgwgcvf_mood`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `Audit_Log`
--

CREATE TABLE `Audit_Log` (
  `id_audit` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data untuk tabel `Audit_Log`
--

INSERT INTO `Audit_Log` (`id_audit`, `id_user`, `action`, `entity`, `entity_id`, `description`, `created_at`) VALUES
(1, 1, 'seed', 'Initialization', NULL, 'Initial seed data created', '2025-10-31 06:28:06'),
(2, 1, 'delete', 'Catatan_Harian', 2, 'Menghapus catatan mood.', '2025-10-31 10:04:52'),
(3, 1, 'update', 'Catatan_Harian', 3, 'Memperbarui catatan mood.', '2025-10-31 10:05:08'),
(4, 1, 'update', 'Catatan_Harian', 3, 'Memperbarui catatan mood.', '2025-10-31 10:17:16'),
(5, 1, 'update', 'Catatan_Harian', 1, 'Memperbarui catatan mood.', '2025-10-31 10:17:37'),
(6, 1, 'update', 'Catatan_Harian', 4, 'Memperbarui catatan mood.', '2025-11-05 13:43:28'),
(7, 1, 'update', 'Catatan_Harian', 3, 'Memperbarui catatan mood.', '2025-11-05 13:43:41'),
(8, 1, 'update', 'Catatan_Harian', 1, 'Memperbarui catatan mood.', '2025-11-05 13:43:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `Catatan_Harian`
--

CREATE TABLE `Catatan_Harian` (
  `id_catatan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `mood` varchar(50) NOT NULL,
  `catatan_mood` text DEFAULT NULL,
  `output_harian` enum('Sesuai Target','Di Bawah Target','Di Atas Target') NOT NULL,
  `output_harian_asli` text DEFAULT NULL,
  `tanggal_catatan` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `Catatan_Harian`
--

INSERT INTO `Catatan_Harian` (`id_catatan`, `id_user`, `mood`, `catatan_mood`, `output_harian`, `output_harian_asli`, `tanggal_catatan`, `created_at`, `updated_at`) VALUES
(1, 1, '🤩 Sangat Senang', 'Project dashboard selesai tepat waktu.', 'Sesuai Target', 'Merilis fitur analitik baru.', '2025-10-11 02:00:00', '2025-10-31 06:28:06', '2025-11-05 13:43:50'),
(3, 3, '😐 Netral', 'Menunggu sparepart datang.', 'Sesuai Target', 'Melakukan pengecekan mesin dan update status.', '2025-10-11 07:45:00', '2025-10-31 06:28:06', '2025-11-05 13:43:41'),
(4, 1, '🤩 Sangat Senang', 'senang kerjaan nya ringan', 'Sesuai Target', 'sangat senang', '2025-10-31 10:18:00', '2025-10-31 10:19:22', '2025-11-05 13:43:28'),
(5, 1, '😐 Netral', 'Biasa AJa', 'Sesuai Target', 'Sesuai Target', '2025-11-05 13:21:00', '2025-11-05 13:22:12', '2025-11-05 13:39:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `Catatan_Harian_backup`
--

CREATE TABLE `Catatan_Harian_backup` (
  `id_catatan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `mood` varchar(50) NOT NULL,
  `catatan_mood` text DEFAULT NULL,
  `output_harian` text DEFAULT NULL,
  `tanggal_catatan` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `Catatan_Harian_backup`
--

INSERT INTO `Catatan_Harian_backup` (`id_catatan`, `id_user`, `mood`, `catatan_mood`, `output_harian`, `tanggal_catatan`, `created_at`, `updated_at`) VALUES
(1, 1, '🤩 Sangat Senang', 'Project dashboard selesai tepat waktu.', 'Merilis fitur analitik baru.', '2025-10-11 02:00:00', '2025-10-31 06:28:06', '2025-10-31 10:17:37'),
(3, 3, '😐 Netral', 'Menunggu sparepart datang.', 'Melakukan pengecekan mesin dan update status.', '2025-10-11 07:45:00', '2025-10-31 06:28:06', '2025-10-31 10:17:16'),
(4, 1, '🤩 Sangat Senang', 'senang kerjaan nya ringan', 'sangat senang', '2025-10-31 10:18:00', '2025-10-31 10:19:22', '2025-10-31 10:19:22'),
(5, 1, '😐 Netral', 'Biasa AJa', 'Sesuai Target', '2025-11-05 13:21:00', '2025-11-05 13:22:12', '2025-11-05 13:22:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `Posisi`
--

CREATE TABLE `Posisi` (
  `id_posisi` int(11) NOT NULL,
  `nama_posisi` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `Posisi`
--

INSERT INTO `Posisi` (`id_posisi`, `nama_posisi`, `created_at`, `updated_at`) VALUES
(1, 'Karu', '2025-10-31 06:28:06', '2025-10-31 06:28:06'),
(2, 'Skill Operator', '2025-10-31 06:28:06', '2025-10-31 06:28:06'),
(3, 'Stock Control', '2025-10-31 06:28:06', '2025-10-31 06:28:06'),
(4, 'Team Produksi', '2025-10-31 06:28:06', '2025-10-31 06:28:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `Users`
--

CREATE TABLE `Users` (
  `id_user` int(11) NOT NULL,
  `nama_karyawan` varchar(150) NOT NULL,
  `no_bundy` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id_posisi` int(11) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'karyawan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `Users`
--

INSERT INTO `Users` (`id_user`, `nama_karyawan`, `no_bundy`, `password_hash`, `id_posisi`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Ayu Pratama', 'BD001', '$2y$10$0GEshi2eZfiGVtWvmz7o1.kjnN8mrlkrxmwkS4bNqXbihjQQUnhdC', 1, 'admin', '2025-10-31 06:28:06', '2025-10-31 09:58:53'),
(2, 'Bima Saputra', 'BD002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uhe.Wu/Fi', 2, 'karyawan', '2025-10-31 06:28:06', '2025-10-31 06:28:06'),
(3, 'Citra Lestari', 'BD003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uhe.Wu/Fi', 3, 'karyawan', '2025-10-31 06:28:06', '2025-10-31 06:28:06'),
(4, 'Abc', '123556', '$2y$10$VO8WxZz97tIY4/9tEuEVhOJXNKMOlukp/G4U9liEY/SNUpMT2CSNO', 1, 'karyawan', '2025-10-31 09:37:29', '2025-10-31 09:37:29'),
(5, 'Agus Septiana WK', '2042', '$2y$10$t7n8dRO.O2qXT/oasGuMrONHOsMcub9CQY7H.nlfHGXkvfFWbKJy2', 1, 'karyawan', '2025-11-01 04:59:03', '2025-11-01 04:59:03');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `Audit_Log`
--
ALTER TABLE `Audit_Log`
  ADD PRIMARY KEY (`id_audit`),
  ADD KEY `fk_audit_user` (`id_user`);

--
-- Indeks untuk tabel `Catatan_Harian`
--
ALTER TABLE `Catatan_Harian`
  ADD PRIMARY KEY (`id_catatan`),
  ADD KEY `fk_catatan_user` (`id_user`);

--
-- Indeks untuk tabel `Catatan_Harian_backup`
--
ALTER TABLE `Catatan_Harian_backup`
  ADD PRIMARY KEY (`id_catatan`),
  ADD KEY `fk_catatan_user` (`id_user`);

--
-- Indeks untuk tabel `Posisi`
--
ALTER TABLE `Posisi`
  ADD PRIMARY KEY (`id_posisi`),
  ADD UNIQUE KEY `nama_posisi` (`nama_posisi`);

--
-- Indeks untuk tabel `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `no_bundy` (`no_bundy`),
  ADD KEY `fk_users_posisi` (`id_posisi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `Audit_Log`
--
ALTER TABLE `Audit_Log`
  MODIFY `id_audit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `Catatan_Harian`
--
ALTER TABLE `Catatan_Harian`
  MODIFY `id_catatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `Catatan_Harian_backup`
--
ALTER TABLE `Catatan_Harian_backup`
  MODIFY `id_catatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `Posisi`
--
ALTER TABLE `Posisi`
  MODIFY `id_posisi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `Users`
--
ALTER TABLE `Users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `Audit_Log`
--
ALTER TABLE `Audit_Log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `Catatan_Harian`
--
ALTER TABLE `Catatan_Harian`
  ADD CONSTRAINT `fk_catatan_user` FOREIGN KEY (`id_user`) REFERENCES `Users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `fk_users_posisi` FOREIGN KEY (`id_posisi`) REFERENCES `Posisi` (`id_posisi`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
