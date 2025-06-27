-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Jun 2025 pada 03.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spmi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `akreditasi`
--

CREATE TABLE `akreditasi` (
  `akreditasi_id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akreditasi`
--

INSERT INTO `akreditasi` (`akreditasi_id`, `nama`) VALUES
(1, 'A'),
(2, 'B'),
(3, 'C'),
(4, 'Baik'),
(5, 'Baik Sekali'),
(6, 'Unggul');

-- --------------------------------------------------------

--
-- Struktur dari tabel `akses`
--

CREATE TABLE `akses` (
  `akses_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akses`
--

INSERT INTO `akses` (`akses_id`, `nama`) VALUES
(1, 'akses admin'),
(2, 'akses auditor'),
(3, 'akses prodi'),
(4, 'akses gkm');

-- --------------------------------------------------------

--
-- Struktur dari tabel `auditor`
--

CREATE TABLE `auditor` (
  `role_id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `auditor`
--

INSERT INTO `auditor` (`role_id`, `nama`, `jabatan`, `instansi`, `user_id`) VALUES
(123456, 'Nisrina Aliya', 'Ahli Sandi', 'Pusat Penjaminan Mutu', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `auditor_prodi`
--

CREATE TABLE `auditor_prodi` (
  `auditor_prodi_id` int(11) NOT NULL,
  `auditor_id` int(11) DEFAULT NULL,
  `on_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen`
--

CREATE TABLE `dokumen` (
  `dokumen_id` int(11) NOT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `path_file` text DEFAULT NULL,
  `tipe_dokumen` enum('temuan','data_dukung') DEFAULT NULL,
  `kd_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp(),
  `tahun` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokumen`
--

INSERT INTO `dokumen` (`dokumen_id`, `nama_file`, `path_file`, `tipe_dokumen`, `kd_id`, `user_id`, `tanggal_upload`, `tahun`) VALUES
(2, 'PDDIKTI.pdf', '685b4ae8011d2_PDDIKTI.pdf', 'temuan', 1, 1, '2025-06-24 20:17:11', 1999);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen_edi`
--

CREATE TABLE `dokumen_edi` (
  `dokumen_edi_id` int(11) NOT NULL,
  `dokumen_id` int(11) DEFAULT NULL,
  `edi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen_prodi`
--

CREATE TABLE `dokumen_prodi` (
  `dokumen_prodi_id` int(11) NOT NULL,
  `dokumen_id` int(11) DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokumen_prodi`
--

INSERT INTO `dokumen_prodi` (`dokumen_prodi_id`, `dokumen_id`, `prodi_id`) VALUES
(3, 2, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dp`
--

CREATE TABLE `dp` (
  `dp_id` int(11) NOT NULL,
  `on_id` int(11) DEFAULT NULL,
  `pertanyaan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `edesk`
--

CREATE TABLE `edesk` (
  `edesk_id` int(11) NOT NULL,
  `auditor_id` int(11) NOT NULL,
  `on_id` int(11) NOT NULL,
  `nm_id` int(11) NOT NULL,
  `temuan` varchar(255) DEFAULT NULL,
  `jt_id` int(11) DEFAULT NULL,
  `pertanyaan` varchar(255) DEFAULT NULL,
  `subssm_id` int(11) DEFAULT NULL,
  `dokumen_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `edesk`
--

INSERT INTO `edesk` (`edesk_id`, `auditor_id`, `on_id`, `nm_id`, `temuan`, `jt_id`, `pertanyaan`, `subssm_id`, `dokumen_id`) VALUES
(3, 123456, 11, 5, 'coba', NULL, 'coba', 19, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `edi`
--

CREATE TABLE `edi` (
  `edi_id` int(11) NOT NULL,
  `subssm_id` int(11) DEFAULT NULL,
  `nm_id` int(11) DEFAULT NULL,
  `on_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `gkm`
--

CREATE TABLE `gkm` (
  `role_id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gkm`
--

INSERT INTO `gkm` (`role_id`, `nama`, `keterangan`, `user_id`) VALUES
(1, 'Unit Perpustakaan', NULL, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `grup`
--

CREATE TABLE `grup` (
  `grup_id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `grup`
--

INSERT INTO `grup` (`grup_id`, `nama`) VALUES
(1, 'Auditee'),
(2, 'Auditor'),
(3, 'Admin'),
(4, 'GKM');

-- --------------------------------------------------------

--
-- Struktur dari tabel `indikator`
--

CREATE TABLE `indikator` (
  `indikator_id` int(11) NOT NULL,
  `jenis` enum('Kualitatif','Kuantitatif') DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `nm_id` int(11) DEFAULT NULL,
  `on_id` int(11) DEFAULT NULL,
  `subssm_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `indikator`
--

INSERT INTO `indikator` (`indikator_id`, `jenis`, `keterangan`, `nm_id`, `on_id`, `subssm_id`) VALUES
(1, 'Kualitatif', NULL, NULL, NULL, NULL),
(2, 'Kuantitatif', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jd`
--

CREATE TABLE `jd` (
  `jd_id` int(11) NOT NULL,
  `nama_jd` varchar(255) DEFAULT NULL,
  `kd_id` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jd`
--

INSERT INTO `jd` (`jd_id`, `nama_jd`, `kd_id`, `keterangan`) VALUES
(1, 'Dokumen', 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jt`
--

CREATE TABLE `jt` (
  `jt_id` int(11) NOT NULL,
  `kt_id` int(11) DEFAULT NULL,
  `nama_jt` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jt`
--

INSERT INTO `jt` (`jt_id`, `kt_id`, `nama_jt`, `keterangan`) VALUES
(1, 2, 'KTS', NULL),
(2, 2, 'Kesesuaian', NULL),
(3, 1, 'Observasi', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kd`
--

CREATE TABLE `kd` (
  `kd_id` int(11) NOT NULL,
  `nama_kd` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kd`
--

INSERT INTO `kd` (`kd_id`, `nama_kd`, `keterangan`) VALUES
(1, 'STANDAR KOMPETENSI LULUSAN', NULL),
(3, 'test', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kt`
--

CREATE TABLE `kt` (
  `kt_id` int(11) NOT NULL,
  `nama_kt` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kt`
--

INSERT INTO `kt` (`kt_id`, `nama_kt`, `keterangan`) VALUES
(1, 'Mayor', NULL),
(2, 'Minor', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `la`
--

CREATE TABLE `la` (
  `la_id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `la`
--

INSERT INTO `la` (`la_id`, `nama`, `keterangan`) VALUES
(1, 'SPMI', NULL),
(2, 'LAM INFOKOM', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `lat`
--

CREATE TABLE `lat` (
  `lat_id` int(11) NOT NULL,
  `tahun_id` int(11) DEFAULT NULL,
  `la_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lat`
--

INSERT INTO `lat` (`lat_id`, `tahun_id`, `la_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 1, 2),
(5, 2, 2),
(7, 3, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilaimutu`
--

CREATE TABLE `nilaimutu` (
  `nm_id` int(11) NOT NULL,
  `lat_id` int(11) DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `nilaimutu`
--

INSERT INTO `nilaimutu` (`nm_id`, `lat_id`, `nilai`, `keterangan`) VALUES
(2, 2, 1, 'Tercapai'),
(4, 2, 0, 'Tidak Tercapai'),
(5, 1, 1, 'test');

-- --------------------------------------------------------

--
-- Struktur dari tabel `objeknilai`
--

CREATE TABLE `objeknilai` (
  `on_id` int(11) NOT NULL,
  `lat_id` int(11) DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `standar_id` int(11) DEFAULT NULL,
  `nilai_edi` int(11) DEFAULT NULL,
  `nilai_edesk` int(11) DEFAULT NULL,
  `nilai_akhir` int(11) DEFAULT NULL,
  `rata` int(11) DEFAULT NULL,
  `target` int(11) DEFAULT NULL,
  `periode_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `objeknilai`
--

INSERT INTO `objeknilai` (`on_id`, `lat_id`, `prodi_id`, `standar_id`, `nilai_edi`, `nilai_edesk`, `nilai_akhir`, `rata`, `target`, `periode_id`) VALUES
(1, 2, 2, 1, NULL, NULL, NULL, NULL, 75, 1),
(11, 1, 2, 4, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 3, 2, 4, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `periode`
--

CREATE TABLE `periode` (
  `periode_id` int(11) NOT NULL,
  `periode_edisi_start` date DEFAULT NULL,
  `periode_edisi_end` date DEFAULT NULL,
  `periode_edesk_start` date DEFAULT NULL,
  `periode_edesk_end` date DEFAULT NULL,
  `periode_visitatif_start` date DEFAULT NULL,
  `periode_visitatif_end` date DEFAULT NULL,
  `lat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `periode`
--

INSERT INTO `periode` (`periode_id`, `periode_edisi_start`, `periode_edisi_end`, `periode_edesk_start`, `periode_edesk_end`, `periode_visitatif_start`, `periode_visitatif_end`, `lat_id`) VALUES
(1, '2024-09-13', '2024-09-27', '2024-11-17', '2024-12-10', '2024-11-17', '2024-12-10', 2),
(2, '2024-11-01', '2024-11-30', '2024-11-01', '2024-11-30', '2024-11-01', '2024-11-30', 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `perubahan_psm`
--

CREATE TABLE `perubahan_psm` (
  `perubahan_id` int(11) NOT NULL,
  `psm_id` int(11) NOT NULL,
  `on_id` int(11) NOT NULL,
  `kolom` varchar(255) NOT NULL,
  `oldval` text DEFAULT NULL,
  `newval` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perubahan_psm`
--

INSERT INTO `perubahan_psm` (`perubahan_id`, `psm_id`, `on_id`, `kolom`, `oldval`, `newval`, `username`, `updated_at`) VALUES
(44, 31, 12, 'nama', NULL, NULL, 'current_user', '2025-06-15 21:34:33'),
(45, 31, 11, 'data_dukung', 'main', '2023', 'current_user', '2025-06-15 22:05:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perubahan_ssm`
--

CREATE TABLE `perubahan_ssm` (
  `perubahan_id` int(11) NOT NULL,
  `ssm_id` int(11) NOT NULL,
  `on_id` int(11) NOT NULL,
  `kolom` varchar(255) NOT NULL,
  `oldval` text DEFAULT NULL,
  `newval` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `perubahan_standar`
--

CREATE TABLE `perubahan_standar` (
  `perubahan_id` int(11) NOT NULL,
  `standar_id` int(11) NOT NULL,
  `on_id` int(11) NOT NULL,
  `kolom` varchar(255) NOT NULL,
  `oldval` text DEFAULT NULL,
  `newval` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perubahan_standar`
--

INSERT INTO `perubahan_standar` (`perubahan_id`, `standar_id`, `on_id`, `kolom`, `oldval`, `newval`, `username`, `updated_at`) VALUES
(3, 4, 11, 'data_dukung', '', '2023lagi', 'current_user', '2025-06-15 19:35:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perubahan_subssm`
--

CREATE TABLE `perubahan_subssm` (
  `perubahan_id` int(11) NOT NULL,
  `subssm_id` int(11) NOT NULL,
  `on_id` int(11) NOT NULL,
  `kolom` varchar(255) NOT NULL,
  `oldval` text DEFAULT NULL,
  `newval` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `prodi`
--

CREATE TABLE `prodi` (
  `role_id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `tanggal_akreditasi` date DEFAULT NULL,
  `tanggal_expired` date DEFAULT NULL,
  `nomorSK` varchar(255) DEFAULT NULL,
  `akreditasi_id` int(11) DEFAULT NULL,
  `dokumenakreditasi` mediumblob DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `prodi`
--

INSERT INTO `prodi` (`role_id`, `nama`, `tanggal_akreditasi`, `tanggal_expired`, `nomorSK`, `akreditasi_id`, `dokumenakreditasi`, `user_id`) VALUES
(2, 'Rekayasa Kriptografi', '2024-08-09', '2029-08-09', '119/SK/LAM-INFOKOM/Ak/STr/VIII/2024', 5, NULL, 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `psm`
--

CREATE TABLE `psm` (
  `psm_id` int(11) NOT NULL,
  `standar_id` int(11) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `data_dukung` varchar(255) DEFAULT NULL,
  `bobot_nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `psm`
--

INSERT INTO `psm` (`psm_id`, `standar_id`, `nama`, `keterangan`, `data_dukung`, `bobot_nilai`) VALUES
(1, 1, 'STANDAR PENDIDIKAN', NULL, NULL, NULL),
(2, 1, 'STANDAR PENELITIAN', NULL, NULL, NULL),
(3, 1, 'STANDAR PENGABDIAN KEPADA MASYARAKAT', '', NULL, NULL),
(4, 1, 'STANDAR TAMBAHAN', '', NULL, NULL),
(31, 4, 'nis', '', 'main', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `ssm`
--

CREATE TABLE `ssm` (
  `ssm_id` int(11) NOT NULL,
  `psm_id` int(11) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `data_dukung` varchar(255) DEFAULT NULL,
  `bobot_nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ssm`
--

INSERT INTO `ssm` (`ssm_id`, `psm_id`, `nama`, `keterangan`, `data_dukung`, `bobot_nilai`) VALUES
(1, 1, 'STANDAR KOMPETENSI LULUSAN', NULL, NULL, NULL),
(2, 1, 'STANDAR ISI PEMBELAJARAN', NULL, NULL, NULL),
(3, 1, 'STANDAR PROSES PEMBELAJARAN', NULL, NULL, NULL),
(4, 1, 'STANDAR PENILAIAN PEMBELAJARAN', NULL, NULL, NULL),
(5, 1, 'STANDAR DOSEN DAN TENAGA KEPENDIDIKAN', NULL, NULL, NULL),
(6, 1, 'STANDAR SARANA DAN PRASARANA PEMBELAJARAN', NULL, NULL, NULL),
(7, 1, 'STANDAR PENGELOLAAN PEMBELAJARAN', NULL, NULL, NULL),
(8, 1, 'STANDAR PEMBIAYAAN PEMBELAJARAN', NULL, NULL, NULL),
(21, 31, 'ri', '', 'main!!!!!', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `standar`
--

CREATE TABLE `standar` (
  `standar_id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `data_dukung` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `bobot_nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `standar`
--

INSERT INTO `standar` (`standar_id`, `nama`, `data_dukung`, `keterangan`, `bobot_nilai`) VALUES
(1, 'SN-DIKTI', NULL, NULL, NULL),
(4, 'testing', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `status`
--

INSERT INTO `status` (`status_id`, `nama`) VALUES
(1, 'Aktif'),
(2, 'Tidak Aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subssm`
--

CREATE TABLE `subssm` (
  `subssm_id` int(11) NOT NULL,
  `ssm_id` int(11) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `data_dukung` varchar(255) DEFAULT NULL,
  `bobot_nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `subssm`
--

INSERT INTO `subssm` (`subssm_id`, `ssm_id`, `nama`, `keterangan`, `data_dukung`, `bobot_nilai`) VALUES
(1, 1, '1.1 Direktur memastikan bahwa setiap lulusan mengikuti minimal satu sertifikasi kompetensi/profesi/ industri setiap tahun.', NULL, NULL, NULL),
(19, 21, 'ntesting', '', 'mainadaga', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun`
--

CREATE TABLE `tahun` (
  `tahun_id` int(11) NOT NULL,
  `nama` year(4) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun`
--

INSERT INTO `tahun` (`tahun_id`, `nama`, `status_id`) VALUES
(1, '2023', 2),
(2, '2024', 1),
(3, '2025', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `grup_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `status`, `keterangan`, `grup_id`) VALUES
(1, 'aliya', 'nisrinaahana@gmail.com', 'hana2003', 1, 'null', 2),
(2, 'Pengguna Prodi RK', 'rk@gmail.com', 'rk', 1, 'null', 1),
(3, 'Pengguna Unit Perpustakaan', 'perpustakaan@gmail.com', 'perpus', 1, 'null', 4);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `akreditasi`
--
ALTER TABLE `akreditasi`
  ADD PRIMARY KEY (`akreditasi_id`);

--
-- Indeks untuk tabel `akses`
--
ALTER TABLE `akses`
  ADD PRIMARY KEY (`akses_id`);

--
-- Indeks untuk tabel `auditor`
--
ALTER TABLE `auditor`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `auditor_prodi`
--
ALTER TABLE `auditor_prodi`
  ADD PRIMARY KEY (`auditor_prodi_id`),
  ADD KEY `auditor_id` (`auditor_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `dokumen`
--
ALTER TABLE `dokumen`
  ADD PRIMARY KEY (`dokumen_id`),
  ADD KEY `kd_id` (`kd_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `dokumen_edi`
--
ALTER TABLE `dokumen_edi`
  ADD PRIMARY KEY (`dokumen_edi_id`),
  ADD KEY `dokumen_id` (`dokumen_id`),
  ADD KEY `edi_id` (`edi_id`);

--
-- Indeks untuk tabel `dokumen_prodi`
--
ALTER TABLE `dokumen_prodi`
  ADD PRIMARY KEY (`dokumen_prodi_id`),
  ADD KEY `dokumen_id` (`dokumen_id`),
  ADD KEY `prodi_id` (`prodi_id`);

--
-- Indeks untuk tabel `dp`
--
ALTER TABLE `dp`
  ADD PRIMARY KEY (`dp_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `edesk`
--
ALTER TABLE `edesk`
  ADD PRIMARY KEY (`edesk_id`),
  ADD KEY `nm_id` (`nm_id`),
  ADD KEY `on_id` (`on_id`),
  ADD KEY `jt_id` (`jt_id`),
  ADD KEY `auditor_id` (`auditor_id`),
  ADD KEY `fk_edesk_subssm` (`subssm_id`),
  ADD KEY `dokumen_id` (`dokumen_id`);

--
-- Indeks untuk tabel `edi`
--
ALTER TABLE `edi`
  ADD PRIMARY KEY (`edi_id`),
  ADD KEY `subssm_id` (`subssm_id`),
  ADD KEY `nm_id` (`nm_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `gkm`
--
ALTER TABLE `gkm`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `grup`
--
ALTER TABLE `grup`
  ADD PRIMARY KEY (`grup_id`);

--
-- Indeks untuk tabel `indikator`
--
ALTER TABLE `indikator`
  ADD PRIMARY KEY (`indikator_id`),
  ADD KEY `nm_id` (`nm_id`),
  ADD KEY `subssm_id` (`subssm_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `jd`
--
ALTER TABLE `jd`
  ADD PRIMARY KEY (`jd_id`),
  ADD KEY `kd_id` (`kd_id`);

--
-- Indeks untuk tabel `jt`
--
ALTER TABLE `jt`
  ADD PRIMARY KEY (`jt_id`),
  ADD KEY `kt_id` (`kt_id`);

--
-- Indeks untuk tabel `kd`
--
ALTER TABLE `kd`
  ADD PRIMARY KEY (`kd_id`);

--
-- Indeks untuk tabel `kt`
--
ALTER TABLE `kt`
  ADD PRIMARY KEY (`kt_id`);

--
-- Indeks untuk tabel `la`
--
ALTER TABLE `la`
  ADD PRIMARY KEY (`la_id`);

--
-- Indeks untuk tabel `lat`
--
ALTER TABLE `lat`
  ADD PRIMARY KEY (`lat_id`),
  ADD KEY `tahun_id` (`tahun_id`),
  ADD KEY `la_id` (`la_id`);

--
-- Indeks untuk tabel `nilaimutu`
--
ALTER TABLE `nilaimutu`
  ADD PRIMARY KEY (`nm_id`),
  ADD KEY `lat_id` (`lat_id`);

--
-- Indeks untuk tabel `objeknilai`
--
ALTER TABLE `objeknilai`
  ADD PRIMARY KEY (`on_id`),
  ADD KEY `lat_id` (`lat_id`),
  ADD KEY `prodi_id` (`prodi_id`),
  ADD KEY `standar_id` (`standar_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indeks untuk tabel `periode`
--
ALTER TABLE `periode`
  ADD PRIMARY KEY (`periode_id`),
  ADD KEY `lat_id` (`lat_id`);

--
-- Indeks untuk tabel `perubahan_psm`
--
ALTER TABLE `perubahan_psm`
  ADD PRIMARY KEY (`perubahan_id`),
  ADD KEY `psm_id` (`psm_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `perubahan_ssm`
--
ALTER TABLE `perubahan_ssm`
  ADD PRIMARY KEY (`perubahan_id`),
  ADD KEY `ssm_id` (`ssm_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `perubahan_standar`
--
ALTER TABLE `perubahan_standar`
  ADD PRIMARY KEY (`perubahan_id`),
  ADD KEY `standar_id` (`standar_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `perubahan_subssm`
--
ALTER TABLE `perubahan_subssm`
  ADD PRIMARY KEY (`perubahan_id`),
  ADD KEY `subssm_id` (`subssm_id`),
  ADD KEY `on_id` (`on_id`);

--
-- Indeks untuk tabel `prodi`
--
ALTER TABLE `prodi`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `akreditasi_id` (`akreditasi_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `psm`
--
ALTER TABLE `psm`
  ADD PRIMARY KEY (`psm_id`),
  ADD KEY `standar_id` (`standar_id`);

--
-- Indeks untuk tabel `ssm`
--
ALTER TABLE `ssm`
  ADD PRIMARY KEY (`ssm_id`),
  ADD KEY `psm_id` (`psm_id`);

--
-- Indeks untuk tabel `standar`
--
ALTER TABLE `standar`
  ADD PRIMARY KEY (`standar_id`);

--
-- Indeks untuk tabel `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indeks untuk tabel `subssm`
--
ALTER TABLE `subssm`
  ADD PRIMARY KEY (`subssm_id`),
  ADD KEY `ssm_id` (`ssm_id`);

--
-- Indeks untuk tabel `tahun`
--
ALTER TABLE `tahun`
  ADD PRIMARY KEY (`tahun_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `grup_id` (`grup_id`),
  ADD KEY `fk_status_id` (`status`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `akreditasi`
--
ALTER TABLE `akreditasi`
  MODIFY `akreditasi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `akses`
--
ALTER TABLE `akses`
  MODIFY `akses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `auditor`
--
ALTER TABLE `auditor`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123457;

--
-- AUTO_INCREMENT untuk tabel `auditor_prodi`
--
ALTER TABLE `auditor_prodi`
  MODIFY `auditor_prodi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `dokumen`
--
ALTER TABLE `dokumen`
  MODIFY `dokumen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `dokumen_edi`
--
ALTER TABLE `dokumen_edi`
  MODIFY `dokumen_edi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `dokumen_prodi`
--
ALTER TABLE `dokumen_prodi`
  MODIFY `dokumen_prodi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `dp`
--
ALTER TABLE `dp`
  MODIFY `dp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `edesk`
--
ALTER TABLE `edesk`
  MODIFY `edesk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `edi`
--
ALTER TABLE `edi`
  MODIFY `edi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `gkm`
--
ALTER TABLE `gkm`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `grup`
--
ALTER TABLE `grup`
  MODIFY `grup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `indikator`
--
ALTER TABLE `indikator`
  MODIFY `indikator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `jd`
--
ALTER TABLE `jd`
  MODIFY `jd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `jt`
--
ALTER TABLE `jt`
  MODIFY `jt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kd`
--
ALTER TABLE `kd`
  MODIFY `kd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kt`
--
ALTER TABLE `kt`
  MODIFY `kt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `la`
--
ALTER TABLE `la`
  MODIFY `la_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `lat`
--
ALTER TABLE `lat`
  MODIFY `lat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `nilaimutu`
--
ALTER TABLE `nilaimutu`
  MODIFY `nm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `objeknilai`
--
ALTER TABLE `objeknilai`
  MODIFY `on_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `periode`
--
ALTER TABLE `periode`
  MODIFY `periode_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `perubahan_psm`
--
ALTER TABLE `perubahan_psm`
  MODIFY `perubahan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `perubahan_ssm`
--
ALTER TABLE `perubahan_ssm`
  MODIFY `perubahan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `perubahan_standar`
--
ALTER TABLE `perubahan_standar`
  MODIFY `perubahan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `perubahan_subssm`
--
ALTER TABLE `perubahan_subssm`
  MODIFY `perubahan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `prodi`
--
ALTER TABLE `prodi`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `psm`
--
ALTER TABLE `psm`
  MODIFY `psm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `ssm`
--
ALTER TABLE `ssm`
  MODIFY `ssm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `standar`
--
ALTER TABLE `standar`
  MODIFY `standar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `subssm`
--
ALTER TABLE `subssm`
  MODIFY `subssm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `tahun`
--
ALTER TABLE `tahun`
  MODIFY `tahun_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `auditor`
--
ALTER TABLE `auditor`
  ADD CONSTRAINT `auditor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `auditor_prodi`
--
ALTER TABLE `auditor_prodi`
  ADD CONSTRAINT `auditor_prodi_ibfk_1` FOREIGN KEY (`auditor_id`) REFERENCES `auditor` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auditor_prodi_ibfk_3` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dokumen`
--
ALTER TABLE `dokumen`
  ADD CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`kd_id`) REFERENCES `kd` (`kd_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dokumen_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dokumen_edi`
--
ALTER TABLE `dokumen_edi`
  ADD CONSTRAINT `dokumen_edi_ibfk_1` FOREIGN KEY (`dokumen_id`) REFERENCES `dokumen` (`dokumen_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dokumen_edi_ibfk_2` FOREIGN KEY (`edi_id`) REFERENCES `edi` (`edi_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dokumen_prodi`
--
ALTER TABLE `dokumen_prodi`
  ADD CONSTRAINT `dokumen_prodi_ibfk_1` FOREIGN KEY (`dokumen_id`) REFERENCES `dokumen` (`dokumen_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dokumen_prodi_ibfk_2` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`role_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dp`
--
ALTER TABLE `dp`
  ADD CONSTRAINT `dp_ibfk_1` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `edesk`
--
ALTER TABLE `edesk`
  ADD CONSTRAINT `edesk_ibfk_1` FOREIGN KEY (`nm_id`) REFERENCES `nilaimutu` (`nm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edesk_ibfk_2` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edesk_ibfk_3` FOREIGN KEY (`jt_id`) REFERENCES `jt` (`jt_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edesk_ibfk_4` FOREIGN KEY (`auditor_id`) REFERENCES `auditor` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edesk_ibfk_5` FOREIGN KEY (`dokumen_id`) REFERENCES `dokumen` (`dokumen_id`),
  ADD CONSTRAINT `fk_edesk_subssm` FOREIGN KEY (`subssm_id`) REFERENCES `subssm` (`subssm_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `edi`
--
ALTER TABLE `edi`
  ADD CONSTRAINT `edi_ibfk_1` FOREIGN KEY (`subssm_id`) REFERENCES `subssm` (`subssm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edi_ibfk_2` FOREIGN KEY (`nm_id`) REFERENCES `nilaimutu` (`nm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edi_ibfk_3` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `gkm`
--
ALTER TABLE `gkm`
  ADD CONSTRAINT `gkm_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `indikator`
--
ALTER TABLE `indikator`
  ADD CONSTRAINT `indikator_ibfk_1` FOREIGN KEY (`nm_id`) REFERENCES `nilaimutu` (`nm_id`),
  ADD CONSTRAINT `indikator_ibfk_2` FOREIGN KEY (`subssm_id`) REFERENCES `subssm` (`subssm_id`),
  ADD CONSTRAINT `indikator_ibfk_3` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`);

--
-- Ketidakleluasaan untuk tabel `jd`
--
ALTER TABLE `jd`
  ADD CONSTRAINT `jd_ibfk_1` FOREIGN KEY (`kd_id`) REFERENCES `kd` (`kd_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jt`
--
ALTER TABLE `jt`
  ADD CONSTRAINT `jt_ibfk_1` FOREIGN KEY (`kt_id`) REFERENCES `kt` (`kt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lat`
--
ALTER TABLE `lat`
  ADD CONSTRAINT `lat_ibfk_1` FOREIGN KEY (`tahun_id`) REFERENCES `tahun` (`tahun_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lat_ibfk_2` FOREIGN KEY (`la_id`) REFERENCES `la` (`la_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `nilaimutu`
--
ALTER TABLE `nilaimutu`
  ADD CONSTRAINT `nilaimutu_ibfk_1` FOREIGN KEY (`lat_id`) REFERENCES `lat` (`lat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `objeknilai`
--
ALTER TABLE `objeknilai`
  ADD CONSTRAINT `objeknilai_ibfk_1` FOREIGN KEY (`lat_id`) REFERENCES `lat` (`lat_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `objeknilai_ibfk_2` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `objeknilai_ibfk_3` FOREIGN KEY (`standar_id`) REFERENCES `standar` (`standar_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `objeknilai_ibfk_4` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`periode_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `periode`
--
ALTER TABLE `periode`
  ADD CONSTRAINT `periode_ibfk_2` FOREIGN KEY (`lat_id`) REFERENCES `lat` (`lat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perubahan_psm`
--
ALTER TABLE `perubahan_psm`
  ADD CONSTRAINT `perubahan_psm_ibfk_1` FOREIGN KEY (`psm_id`) REFERENCES `psm` (`psm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `perubahan_psm_ibfk_2` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perubahan_ssm`
--
ALTER TABLE `perubahan_ssm`
  ADD CONSTRAINT `perubahan_ssm_ibfk_1` FOREIGN KEY (`ssm_id`) REFERENCES `ssm` (`ssm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `perubahan_ssm_ibfk_2` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perubahan_standar`
--
ALTER TABLE `perubahan_standar`
  ADD CONSTRAINT `perubahan_standar_ibfk_1` FOREIGN KEY (`standar_id`) REFERENCES `standar` (`standar_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `perubahan_standar_ibfk_2` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perubahan_subssm`
--
ALTER TABLE `perubahan_subssm`
  ADD CONSTRAINT `perubahan_subssm_ibfk_1` FOREIGN KEY (`subssm_id`) REFERENCES `subssm` (`subssm_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `perubahan_subssm_ibfk_2` FOREIGN KEY (`on_id`) REFERENCES `objeknilai` (`on_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `prodi`
--
ALTER TABLE `prodi`
  ADD CONSTRAINT `prodi_ibfk_3` FOREIGN KEY (`akreditasi_id`) REFERENCES `akreditasi` (`akreditasi_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodi_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Ketidakleluasaan untuk tabel `psm`
--
ALTER TABLE `psm`
  ADD CONSTRAINT `psm_ibfk_1` FOREIGN KEY (`standar_id`) REFERENCES `standar` (`standar_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ssm`
--
ALTER TABLE `ssm`
  ADD CONSTRAINT `ssm_ibfk_1` FOREIGN KEY (`psm_id`) REFERENCES `psm` (`psm_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `subssm`
--
ALTER TABLE `subssm`
  ADD CONSTRAINT `subssm_ibfk_1` FOREIGN KEY (`ssm_id`) REFERENCES `ssm` (`ssm_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tahun`
--
ALTER TABLE `tahun`
  ADD CONSTRAINT `tahun_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_status_id` FOREIGN KEY (`status`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`grup_id`) REFERENCES `grup` (`grup_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
