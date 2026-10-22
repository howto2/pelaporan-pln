-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 05:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `data_godzilla`
--

-- --------------------------------------------------------

--
-- Table structure for table `energy_stocks`
--

CREATE TABLE `energy_stocks` (
  `stock_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `fuel_type` varchar(10) NOT NULL,
  `received` decimal(10,2) NOT NULL,
  `used` decimal(10,2) NOT NULL,
  `stock` decimal(10,2) NOT NULL,
  `HOP` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `energy_stocks`
--

INSERT INTO `energy_stocks` (`stock_id`, `report_id`, `fuel_type`, `received`, `used`, `stock`, `HOP`) VALUES
(139, 70, 'HSD', 0.00, 0.00, 12.00, NULL),
(140, 70, 'MFO', 0.00, 0.00, 12.00, NULL),
(143, 72, 'HSD', 0.00, 0.00, 12.00, NULL),
(144, 72, 'MFO', 122.00, 18.00, 58.00, NULL),
(145, 73, 'HSD', 11.00, 100.00, 2.00, NULL),
(146, 73, 'MFO', 0.00, 0.00, 10.00, NULL),
(189, 97, 'HSD', 0.00, 0.00, 0.00, 1),
(190, 97, 'MFO', 0.00, 0.00, 0.00, 1),
(195, 102, 'HSD', 1.00, 1.00, 1.00, 11),
(196, 102, 'MFO', 1.00, 1.00, 11.00, 11),
(197, 103, 'HSD', 1.00, 1.00, 1.00, 11),
(198, 103, 'MFO', 1.00, 1.00, 11.00, 11),
(199, 104, 'HSD', 1.00, 1.00, 1.00, 11),
(200, 104, 'MFO', 1.00, 1.00, 11.00, 11),
(201, 105, 'HSD', 1.00, 1.00, 1.00, 11),
(202, 105, 'MFO', 1.00, 1.00, 11.00, 11),
(203, 106, 'HSD', 1.00, 1.00, 1.00, 11),
(204, 106, 'MFO', 1.00, 1.00, 11.00, 11),
(205, 107, 'HSD', 1.00, 1.00, 1.00, 11),
(206, 107, 'MFO', 1.00, 1.00, 11.00, 11),
(209, 113, 'HSD', 1.00, 1.00, 1.00, 1),
(210, 113, 'MFO', 1.00, 1.00, 1.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `operational_reports`
--

CREATE TABLE `operational_reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 1,
  `report_date` date DEFAULT curdate(),
  `report_time` varchar(50) DEFAULT '',
  `Cuaca` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT '',
  `total_mw` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operational_reports`
--

INSERT INTO `operational_reports` (`report_id`, `user_id`, `report_date`, `report_time`, `Cuaca`, `location`, `total_mw`, `notes`, `created_at`) VALUES
(70, 3, '2025-04-28', '15.00 WITA', 'Tidak tercatat', 'karang_asam', 240.00, 'Laporan operasi PLTD KARANG ASAM', '2025-04-28 07:09:00'),
(72, 2, '2025-04-28', '12.00 WITA', 'Tidak tercatat', 'keledang', 194.00, 'Laporan operasi PLTD KELEDANG', '2025-04-28 08:14:23'),
(73, 7, '2025-04-28', '15.00 WITA', 'Tidak tercatat', 'bontang', 12.00, 'Laporan operasi PLTD-MG Bontang', '2025-04-28 08:16:55'),
(81, 1, '2023-11-15', '07.00 WITA', 'Cerah', 'keledang', 0.00, 'Test data', '2025-05-02 01:52:50'),
(97, 5, '2025-05-02', '07.00 WITA', 'Cerah', 'sentawar', 0.00, 'Laporan operasi PLTD SENTAWAR', '2025-05-02 07:44:10'),
(98, 5, '2025-05-02', '15.00 WITA', 'Mendung', 'sentawar', 12.00, 'Laporan operasi PLTD SENTAWAR', '2025-05-02 07:47:14'),
(102, 3, '2025-05-03', '07.00 WITA', 'Gerimis', 'karang_asam', 122.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:00:09'),
(103, 3, '2025-05-03', '07.00 WITA', 'Gerimis', 'karang_asam', 122.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:00:10'),
(104, 3, '2025-05-03', '07.00 WITA', 'Gerimis', 'karang_asam', 122.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:00:10'),
(105, 3, '2025-05-03', '07.00 WITA', 'Gerimis', 'karang_asam', 122.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:00:10'),
(106, 3, '2025-05-03', '07.00 WITA', 'Gerimis', 'karang_asam', 122.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:00:10'),
(107, 3, '2025-05-03', '07.00 WITA', 'Gerimis', 'karang_asam', 122.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:00:10'),
(108, 3, '2025-05-03', '12.00 WITA', 'Gerimis', 'karang_asam', 12.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-03 11:29:05'),
(110, 6, '2025-05-05', '19.00 WITA', 'Cerah', 'sambera', 122.00, 'Laporan operasi PLTG SAMBERA', '2025-05-05 00:27:09'),
(111, 6, '2025-05-05', '19.00 WITA', 'Cerah', 'sambera', 122.00, 'Laporan operasi PLTD SAMBERA', '2025-05-05 00:39:02'),
(112, 6, '2025-05-05', '19.00 WITA', 'Cerah', 'sambera', 122.00, 'Laporan operasi PLTD SAMBERA', '2025-05-05 00:41:10'),
(113, 3, '2025-05-05', '07.00 WITA', 'Berawan', 'karang_asam', 67.00, 'Laporan operasi PLTD KARANG ASAM', '2025-05-05 00:48:53'),
(114, 7, '2025-05-05', '15.00 WITA', 'Mendung', 'bontang', 0.00, 'Laporan operasi PLTD-MG BONTANG', '2025-05-05 02:18:41'),
(115, 2, '2025-05-05', '15.00 WITA', 'Mendung', 'keledang', 0.00, 'Laporan operasi PLTD KELEDANG', '2025-05-05 02:54:45');

-- --------------------------------------------------------

--
-- Table structure for table `personil_piket`
--

CREATE TABLE `personil_piket` (
  `piket_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `jabatan` varchar(50) NOT NULL,
  `nama_personil` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personil_piket`
--

INSERT INTO `personil_piket` (`piket_id`, `report_id`, `jabatan`, `nama_personil`, `location`, `created_at`) VALUES
(1, 1, 'PENGAWAS', 'M.IRVAN S.J', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(2, 1, 'PENGAWAS', 'M ZAKKI NABILAH', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(3, 1, 'PELAKSANA', 'ADE HARYANTO', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(4, 1, 'PELAKSANA', 'WENDI ANUGRAH A.', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(5, 1, 'PELAKSANA', 'SETIAN JORDI', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(6, 1, 'PELAKSANA', 'AHMAD FURQON', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(7, 1, 'PELAKSANA', 'ACHMAD VERDI V.', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(8, 1, 'PELAKSANA', 'P.KURNIAWAN SANDI', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(9, 1, 'PELAKSANA', 'BASTIANSYAH', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(10, 1, 'PELAKSANA', 'PURWOKO', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(11, 1, 'PELAKSANA', 'SAMAD ABDULLAH', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(12, 1, 'PELAKSANA', 'BUDIONO', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(13, 1, 'PELAKSANA', 'AGUNG RIYANTO', 'PLTD WAHAU', '2025-04-29 01:49:52'),
(14, 1, 'PELAKSANA', 'AGUS PRASETYO', 'PLTD WAHAU', '2025-04-29 01:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `pltd_parameters`
--

CREATE TABLE `pltd_parameters` (
  `id` int(11) NOT NULL,
  `dts` decimal(10,2) NOT NULL,
  `dmn` decimal(10,2) NOT NULL,
  `dmp` decimal(10,2) NOT NULL,
  `bp` decimal(10,2) NOT NULL,
  `surplus` decimal(10,2) NOT NULL,
  `padam_jaringan` decimal(10,2) NOT NULL,
  `padam_defisit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `unit_statuses`
--

CREATE TABLE `unit_statuses` (
  `status_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `unit_number` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `load_mw` decimal(10,2) NOT NULL,
  `last_operation` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_statuses`
--

INSERT INTO `unit_statuses` (`status_id`, `report_id`, `unit_number`, `status`, `load_mw`, `last_operation`, `notes`) VALUES
(297, 70, 'Mesin Unit #1', 'OPERASI', 120.00, '', 'test'),
(298, 70, 'Mesin Unit #2', 'OPERASI', 120.00, '', ''),
(299, 70, 'Mesin Unit #3', 'RU', 0.00, '', ''),
(300, 70, 'Mesin Unit #4', 'RS', 0.00, '', ''),
(301, 70, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(302, 70, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(303, 70, 'Mesin Unit #7', 'SF', 0.00, '', ''),
(304, 70, 'Mesin Unit #8', 'SF', 0.00, '', ''),
(312, 72, 'Mesin Unit #1', 'OPERASI', 122.00, '17 september', 'ters'),
(313, 72, 'Mesin Unit #2', 'OPERASI', 12.00, '12 april', ''),
(314, 72, 'Mesin Unit #3', 'OPERASI', 12.00, '', 'waw'),
(315, 72, 'Mesin Unit #4', 'OPERASI', 12.00, '', ''),
(316, 72, 'Mesin Unit #5', 'OPERASI', 12.00, '', ''),
(317, 72, 'Mesin Unit #6', 'OPERASI', 12.00, '', ''),
(318, 72, 'Mesin Unit #7', 'OPERASI', 12.00, '', ''),
(319, 73, 'WARTSILA 1', 'OPERASI', 12.00, '', 'test'),
(320, 73, 'WARTSILA 2', 'FO', 0.00, '12 april', 'wqe'),
(321, 73, 'MAK 1', 'MO', 0.00, '12 mei', ''),
(322, 73, 'MAK 2', 'MO', 0.00, '', ''),
(323, 73, 'MAK 3', 'SF', 0.00, '', ''),
(324, 73, 'MAK 4', 'FO', 0.00, '', ''),
(479, 97, 'Mesin Unit #1', 'RS', 0.00, '', ''),
(480, 97, 'Mesin Unit #2', 'RU', 0.00, '', ''),
(481, 97, 'Mesin Unit #3', 'RU', 0.00, '', ''),
(482, 97, 'Mesin Unit #4', 'RS', 0.00, '', ''),
(483, 97, 'Mesin Unit #5', 'RU', 0.00, '', ''),
(484, 97, 'Mesin Unit #6', 'RU', 0.00, '', ''),
(485, 97, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(486, 97, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(487, 97, 'Mesin Unit #9', 'PO', 0.00, '', ''),
(488, 97, 'Mesin Unit #10', 'RS', 0.00, '', ''),
(489, 97, 'Mesin Unit #11', 'PO', 0.00, '', ''),
(490, 97, 'Mesin Unit #12', 'PO', 0.00, '', ''),
(491, 97, 'Mesin Unit #13', 'RU', 0.00, '', ''),
(492, 97, 'Mesin Unit #14', 'RS', 0.00, '', ''),
(493, 97, 'Mesin Unit #15', 'FO', 0.00, '', ''),
(494, 97, 'Mesin Unit #16', 'FO', 0.00, '', ''),
(495, 97, 'Mesin Unit #17', 'RU', 0.00, '', ''),
(496, 97, 'Mesin Unit #18', 'FO', 0.00, '', ''),
(497, 97, 'Mesin Unit #19', 'RU', 0.00, '', ''),
(498, 97, 'Mesin Unit #20', 'RU', 0.00, '', ''),
(499, 97, 'Mesin Unit #21', 'MO', 0.00, '', ''),
(500, 97, 'Mesin Unit #22', 'RS', 0.00, '', ''),
(501, 97, 'Mesin Unit #23', 'RU', 0.00, '', ''),
(502, 97, 'Mesin Unit #24', 'RU', 0.00, '', ''),
(503, 97, 'Mesin Unit #25', 'PO', 0.00, '', ''),
(504, 97, 'Mesin Unit #26', 'MO', 0.00, '', ''),
(505, 97, 'Mesin Unit #27', 'FO', 0.00, '', ''),
(506, 97, 'Mesin Unit #28', 'RU', 0.00, '', ''),
(507, 97, 'Mesin Unit #29', 'RU', 0.00, '', ''),
(508, 97, 'Mesin Unit #30', 'FO', 0.00, '', ''),
(509, 97, 'Mesin Unit #31', 'RU', 0.00, '', ''),
(510, 97, 'Sewatama #1', 'RU', 0.00, '', ''),
(511, 97, 'Sewatama #2', 'PO', 0.00, '', ''),
(512, 97, 'Sewatama #3', 'PO', 0.00, '', ''),
(513, 98, 'Mesin Unit #1', 'FO', 0.00, '', ''),
(514, 98, 'Mesin Unit #2', 'MO', 0.00, '', ''),
(515, 98, 'Mesin Unit #3', 'RS', 0.00, '', ''),
(516, 98, 'Mesin Unit #4', 'RU', 0.00, '', ''),
(517, 98, 'Mesin Unit #5', 'SF', 0.00, '', ''),
(518, 98, 'Mesin Unit #6', 'FO', 0.00, '', ''),
(519, 98, 'Mesin Unit #7', 'MO', 0.00, '', ''),
(520, 98, 'Mesin Unit #8', 'RU', 0.00, '', ''),
(521, 98, 'Mesin Unit #9', 'FO', 0.00, '', ''),
(522, 98, 'Mesin Unit #10', 'OPERASI', 12.00, '', ''),
(523, 98, 'Mesin Unit #11', 'MO', 0.00, '', ''),
(524, 98, 'Mesin Unit #12', 'MO', 0.00, '', ''),
(525, 98, 'Mesin Unit #13', 'RU', 0.00, '', ''),
(526, 98, 'Mesin Unit #14', 'FO', 0.00, '', ''),
(527, 98, 'Mesin Unit #15', 'RS', 0.00, '', ''),
(528, 98, 'Mesin Unit #16', 'MO', 0.00, '', ''),
(529, 98, 'Mesin Unit #17', 'MO', 0.00, '', ''),
(530, 98, 'Mesin Unit #18', 'PO', 0.00, '', ''),
(531, 98, 'Mesin Unit #19', 'FO', 0.00, '', ''),
(532, 98, 'Mesin Unit #20', 'PO', 0.00, '', ''),
(533, 98, 'Mesin Unit #21', 'PO', 0.00, '', ''),
(534, 98, 'Mesin Unit #22', 'PO', 0.00, '', ''),
(535, 98, 'Mesin Unit #23', 'PO', 0.00, '', ''),
(536, 98, 'Mesin Unit #24', 'RS', 0.00, '', ''),
(537, 98, 'Mesin Unit #25', 'MO', 0.00, '', ''),
(538, 98, 'Mesin Unit #26', 'PO', 0.00, '', ''),
(539, 98, 'Mesin Unit #27', 'PO', 0.00, '', ''),
(540, 98, 'Mesin Unit #28', 'SF', 0.00, '', ''),
(541, 98, 'Mesin Unit #29', 'FO', 0.00, '', ''),
(542, 98, 'Mesin Unit #30', 'MO', 0.00, '', ''),
(543, 98, 'Mesin Unit #31', 'MO', 0.00, '', ''),
(544, 98, 'Sewatama #1', 'RS', 0.00, '', ''),
(545, 98, 'Sewatama #2', 'MO', 0.00, '', ''),
(546, 98, 'Sewatama #3', 'SF', 0.00, '', ''),
(568, 102, 'Mesin Unit #1', 'OPERASI', 122.00, '', ''),
(569, 102, 'Mesin Unit #2', 'PO', 0.00, '', ''),
(570, 102, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(571, 102, 'Mesin Unit #4', 'MO', 0.00, '', ''),
(572, 102, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(573, 102, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(574, 102, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(575, 102, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(576, 103, 'Mesin Unit #1', 'OPERASI', 122.00, '', ''),
(577, 103, 'Mesin Unit #2', 'PO', 0.00, '', ''),
(578, 103, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(579, 103, 'Mesin Unit #4', 'MO', 0.00, '', ''),
(580, 103, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(581, 103, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(582, 103, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(583, 103, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(584, 104, 'Mesin Unit #1', 'OPERASI', 122.00, '', ''),
(585, 104, 'Mesin Unit #2', 'PO', 0.00, '', ''),
(586, 104, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(587, 104, 'Mesin Unit #4', 'MO', 0.00, '', ''),
(588, 104, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(589, 104, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(590, 104, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(591, 104, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(592, 105, 'Mesin Unit #1', 'OPERASI', 122.00, '', ''),
(593, 105, 'Mesin Unit #2', 'PO', 0.00, '', ''),
(594, 105, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(595, 105, 'Mesin Unit #4', 'MO', 0.00, '', ''),
(596, 105, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(597, 105, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(598, 105, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(599, 105, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(600, 106, 'Mesin Unit #1', 'OPERASI', 122.00, '', ''),
(601, 106, 'Mesin Unit #2', 'PO', 0.00, '', ''),
(602, 106, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(603, 106, 'Mesin Unit #4', 'MO', 0.00, '', ''),
(604, 106, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(605, 106, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(606, 106, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(607, 106, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(608, 107, 'Mesin Unit #1', 'OPERASI', 122.00, '', ''),
(609, 107, 'Mesin Unit #2', 'PO', 0.00, '', ''),
(610, 107, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(611, 107, 'Mesin Unit #4', 'MO', 0.00, '', ''),
(612, 107, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(613, 107, 'Mesin Unit #6', 'PO', 0.00, '', ''),
(614, 107, 'Mesin Unit #7', 'PO', 0.00, '', ''),
(615, 107, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(616, 108, 'Mesin Unit #1', 'OPERASI', 12.00, '', ''),
(617, 108, 'Mesin Unit #2', 'FO', 0.00, '', ''),
(618, 108, 'Mesin Unit #3', 'RS', 0.00, '', ''),
(619, 108, 'Mesin Unit #4', 'PO', 0.00, '', ''),
(620, 108, 'Mesin Unit #5', 'PO', 0.00, '', ''),
(621, 108, 'Mesin Unit #6', 'RU', 0.00, '', ''),
(622, 108, 'Mesin Unit #7', 'MO', 0.00, '', ''),
(623, 108, 'Mesin Unit #8', 'RU', 0.00, '', ''),
(631, 110, 'TM #1', 'OPERASI', 111.00, '', ''),
(632, 110, 'TM #2', 'OPERASI', 11.00, '', ''),
(633, 111, 'TM #1', 'OPERASI', 111.00, '', ''),
(634, 111, 'TM #2', 'OPERASI', 11.00, '', ''),
(635, 112, 'TM #1', 'OPERASI', 111.00, '', ''),
(636, 112, 'TM #2', 'OPERASI', 11.00, '', ''),
(637, 113, 'Mesin Unit #1', 'OPERASI', 12.00, '', ''),
(638, 113, 'Mesin Unit #2', 'OPERASI', 11.00, '', ''),
(639, 113, 'Mesin Unit #3', 'OPERASI', 11.00, '', ''),
(640, 113, 'Mesin Unit #4', 'OPERASI', 11.00, '', ''),
(641, 113, 'Mesin Unit #5', 'OPERASI', 11.00, '', ''),
(642, 113, 'Mesin Unit #6', 'OPERASI', 11.00, '', ''),
(643, 113, 'Mesin Unit #7', 'RS', 0.00, '', ''),
(644, 113, 'Mesin Unit #8', 'PO', 0.00, '', ''),
(645, 114, 'MG #1', 'RU', 0.00, '', ''),
(646, 114, 'MG #2', 'PO', 0.00, '', ''),
(647, 114, 'MAK #1', 'PO', 0.00, '', ''),
(648, 114, 'MAK #2', 'PO', 0.00, '', ''),
(649, 114, 'MAK #3', 'MO', 0.00, '', ''),
(650, 114, 'MAK #4', 'MO', 0.00, '', ''),
(651, 114, 'MPP #1', 'MO', 0.00, '', ''),
(652, 114, 'MPP #2', 'FO', 0.00, '', ''),
(653, 114, 'MPP #3', 'MO', 0.00, '', ''),
(654, 114, 'MPP #4', 'FO', 0.00, '', ''),
(655, 115, 'Mesin Unit #1', 'PO', 0.00, '', ''),
(656, 115, 'Mesin Unit #2', 'MO', 0.00, '', ''),
(657, 115, 'Mesin Unit #3', 'PO', 0.00, '', ''),
(658, 115, 'Mesin Unit #4', 'FO', 0.00, '', ''),
(659, 115, 'Mesin Unit #5', 'MO', 0.00, '', ''),
(660, 115, 'Mesin Unit #6', 'FO', 0.00, '', ''),
(661, 115, 'Mesin Unit #7', 'PO', 0.00, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `user_type`, `Location`) VALUES
(1, 'ADMIN', 'admin@example.com', 'password123', 'admin', 'admin'),
(2, 'PLTD KELEDANG', 'polnes@example.com', 'password123', 'user', 'keledang'),
(3, 'PLTD KARANG ASAM', 'melimart@example.com', 'password123', 'user', 'karang_asam'),
(4, 'PLTGU TANJUNG BATU', 'tb@example.com', 'password123', 'user', 'tanjung_batu'),
(5, 'PLTD SENTAWAR', 'sentawarrr@example.com', 'password123', 'user', 'sentawar'),
(6, 'PLTG SAMBERA', 'sambera@example.com', 'password123', 'user', 'sambera'),
(7, 'PLTD-MG BONTANG', 'kpcpride@example.com', 'password123', 'user', 'bontang'),
(8, 'PLTD MUARA WAHAU', 'wahau@example.com', 'password123', 'user', 'wahau'),
(9, 'PLTD MUARA BENGKAL', 'bengkalfc@example.com', 'password123', 'user', 'bengkal'),
(10, 'PLTD LONG BAGUN', 'bagun@example.com', 'password123', 'user', 'long_bagun'),
(11, 'PLTD LONG IRAM', 'iram@example.com', 'password123', 'user', 'long_iram');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `energy_stocks`
--
ALTER TABLE `energy_stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `operational_reports`
--
ALTER TABLE `operational_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `personil_piket`
--
ALTER TABLE `personil_piket`
  ADD PRIMARY KEY (`piket_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `pltd_parameters`
--
ALTER TABLE `pltd_parameters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `unit_statuses`
--
ALTER TABLE `unit_statuses`
  ADD PRIMARY KEY (`status_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `energy_stocks`
--
ALTER TABLE `energy_stocks`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `operational_reports`
--
ALTER TABLE `operational_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `personil_piket`
--
ALTER TABLE `personil_piket`
  MODIFY `piket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pltd_parameters`
--
ALTER TABLE `pltd_parameters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `unit_statuses`
--
ALTER TABLE `unit_statuses`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=662;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `energy_stocks`
--
ALTER TABLE `energy_stocks`
  ADD CONSTRAINT `energy_stocks_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `operational_reports` (`report_id`);

--
-- Constraints for table `operational_reports`
--
ALTER TABLE `operational_reports`
  ADD CONSTRAINT `operational_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `unit_statuses`
--
ALTER TABLE `unit_statuses`
  ADD CONSTRAINT `unit_statuses_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `operational_reports` (`report_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
