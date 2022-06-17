-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2022 at 09:31 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `koperasi_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cicilan`
--

CREATE TABLE `cicilan` (
  `id_pinjaman` int(11) NOT NULL,
  `angsuran_ke` int(11) NOT NULL,
  `received_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cicilan`
--

INSERT INTO `cicilan` (`id_pinjaman`, `angsuran_ke`, `received_by`, `created_at`) VALUES
(5, 1, 2, '2022-06-17 14:07:33'),
(5, 2, 2, '2022-06-17 14:07:47'),
(5, 3, 2, '2022-06-17 14:07:54'),
(5, 4, 2, '2022-06-17 14:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `pinjaman`
--

CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL,
  `no_pinjaman` varchar(32) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_agent` int(11) DEFAULT NULL,
  `nominal` float NOT NULL,
  `suku_bunga` float NOT NULL,
  `nominal_bunga` float NOT NULL,
  `biaya_adm` float NOT NULL,
  `total` float NOT NULL,
  `angsuran` varchar(24) NOT NULL,
  `sisa_angsuran` float NOT NULL,
  `lama_angsuran` int(11) NOT NULL,
  `sisa_nyicil` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `appr_rej_by` int(11) DEFAULT NULL,
  `appr_rej_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pinjaman`
--

INSERT INTO `pinjaman` (`id`, `no_pinjaman`, `id_user`, `id_agent`, `nominal`, `suku_bunga`, `nominal_bunga`, `biaya_adm`, `total`, `angsuran`, `sisa_angsuran`, `lama_angsuran`, `sisa_nyicil`, `status`, `appr_rej_by`, `appr_rej_date`, `created_at`, `updated_at`) VALUES
(1, 'YNS-202206171354-1', 3, 2, 2000000, 2, 480000, 6000, 2486000, '207167', 2486000, 12, 12, 2, 2, '2022-06-17 13:56:47', '2022-06-17 13:54:15', '2022-06-17 06:56:47'),
(2, 'YNS-202206171354-2', 3, 2, 10000000, 2, 2400000, 6000, 12406000, '1033834', 12406000, 12, 12, 4, 2, '2022-06-17 13:56:57', '2022-06-17 13:54:29', '2022-06-17 06:56:57'),
(3, 'YNS-202206171354-3', 3, 2, 60000000, 2, 6000000, 6000, 66006000, '13201200', 66006000, 5, 5, 1, NULL, NULL, '2022-06-17 13:54:42', '2022-06-17 06:54:42'),
(4, 'YNS-202206171355-4', 3, 2, 30000000, 2, 6000000, 6000, 36006000, '3600600', 36006000, 10, 10, 1, NULL, NULL, '2022-06-17 13:55:25', '2022-06-17 06:55:25'),
(5, 'YNS-202206171355-5', 3, 2, 80000000, 2, 6400000, 6000, 86406000, '21601500', 0, 4, 0, 3, 2, '2022-06-17 13:57:09', '2022-06-17 13:55:35', '2022-06-17 07:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE `setting` (
  `id` int(64) NOT NULL,
  `setting_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `setting_val` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`id`, `setting_key`, `setting_val`) VALUES
(1, 'suku_bunga', '2'),
(2, 'mail_pass', 'pass_email'),
(3, 'send_mail', 'koperasiku@mail.com'),
(4, 'content_forgotPass', '<p>Halo [#name#],</p><p>&nbsp;</p><p>Permintaan Perubahan Password Berhasil untuk Login Akun Anda :</p><p>Email : [#email#]</p><p>Password : [#new_pass#]</p>');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(24) NOT NULL,
  `nik` varchar(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_agen` int(11) DEFAULT 0,
  `type` int(1) DEFAULT NULL,
  `foto_ktp` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nik`, `name`, `email`, `phone`, `password`, `id_agen`, `type`, `foto_ktp`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`) VALUES
(1, NULL, 'Admin', 'admin@mail.com', '08777', '$2y$10$8pi/TT0zF1fIvajQYJ561eiJP8onbGyTKJQPMcPwxcM/iB3wxELpW', 0, 1, NULL, '2022-06-16 23:52:05', NULL, '2022-06-16 16:52:05', NULL, NULL, NULL),
(2, '122334', 'Agen', 'agen@mail.com', '08777', '$2y$10$KGYKFRfHkaDe8VfJwrz.7OKm/Jcin9cblgbC0vFPNkqBmAdk9Ldz2', 0, 2, NULL, '2022-06-17 00:24:52', NULL, '2022-06-17 07:26:37', NULL, NULL, NULL),
(3, '1111', 'Anggota', 'anggota@mail.com', '08777', '$2y$10$ZOr4zJw.IAkB7cX0A9uDmuXr51nTyHqZ6oZxTfGil/phLtOrPFoo6', 2, 3, 'http://localhost/koperasi_yansen/uploads/photo_ktp/MjAyMjA2MTcwMDI1cGtlS0Vhc1A=.jpg', '2022-06-17 00:25:33', NULL, '2022-06-17 06:04:02', NULL, NULL, NULL),
(7, '12333', 'Hansen', 'hanssn88@gmail.com', '081314398031', '$2y$10$nvVQ7Ce0Zbq1PTj7merGHuuHvTJGAlaHJjveFJ8psvYqE.V1MRJeG', 1, 3, 'http://localhost/koperasi_yansen/uploads/photo_ktp/MjAyMjA2MTcwOTMyT2FrRVNzclA=.jpg', '2022-06-17 09:32:12', NULL, '2022-06-17 02:32:12', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `setting`
--
ALTER TABLE `setting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pinjaman`
--
ALTER TABLE `pinjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `setting`
--
ALTER TABLE `setting`
  MODIFY `id` int(64) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(24) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
