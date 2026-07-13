-- phpMyAdmin SQL Dump
-- Database: `db_pendekar`
-- PENDEKAR v2 - Penyimpanan Dokumen Elektronik Perkantoran

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

CREATE TABLE `dokumen` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `diunggah_oleh` int(11) DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`) VALUES
(1, 'Dokumen Perencanaan', 'Dokumen perencanaan kegiatan dan program'),
(2, 'Dokumen Laporan Keuangan', 'Dokumen laporan keuangan dan pertanggungjawaban'),
(3, 'Dokumen Bidang Kearsipan', 'Dokumen bidang kearsipan'),
(4, 'Dokumen Bidang Perpustakaan', 'Dokumen bidang perpustakaan'),
(5, 'Dokumen Pengadaan Barang dan Jasa', 'Dokumen pengadaan barang dan jasa'),
(6, 'Dokumen Rencana Kerja', 'Dokumen rencana kerja');

-- --------------------------------------------------------

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `pengguna_id` int(11) DEFAULT NULL,
  `aktivitas` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','struktural') NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pengguna` (`id`, `nama`, `username`, `password`, `role`, `dibuat_pada`) VALUES
(1, 'Admin', 'admin', '123456', 'admin', '2026-04-18 06:36:00'),
(2, 'Staff 1', 'staff1', '123456', 'staff', '2026-04-18 06:36:00'),
(3, 'Pimpinan', 'pimpinan', '123456', 'struktural', '2026-04-18 06:36:00');

-- --------------------------------------------------------

ALTER TABLE `dokumen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `diunggah_oleh` (`diunggah_oleh`);

ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengguna_id` (`pengguna_id`);

ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `dokumen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `dokumen`
  ADD CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dokumen_ibfk_2` FOREIGN KEY (`diunggah_oleh`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
