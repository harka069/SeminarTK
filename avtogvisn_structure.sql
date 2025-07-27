-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 27, 2025 at 07:25 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `avtogvisn`
--

-- --------------------------------------------------------

--
-- Table structure for table `avtomobili`
--

CREATE TABLE `avtomobili` (
  `VIN` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `modeli_id` int(11) DEFAULT NULL,
  `gorivo_id` int(11) DEFAULT NULL,
  `prevozeni_kilometri` int(11) DEFAULT NULL,
  `datum_prve_registracije` date DEFAULT NULL,
  `znamka_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gorivo`
--

CREATE TABLE `gorivo` (
  `gorivo_id` int(11) NOT NULL,
  `gorivo_tip` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modeli`
--

CREATE TABLE `modeli` (
  `model_id` int(11) NOT NULL,
  `znamka_id` int(11) NOT NULL,
  `model_ime` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refresh_token`
--

CREATE TABLE `refresh_token` (
  `token_hash` varchar(255) NOT NULL,
  `expires_at` int(128) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rezultatiTP`
--

CREATE TABLE `rezultatiTP` (
  `IDvnosa` int(11) NOT NULL,
  `VIN` varchar(22) DEFAULT NULL,
  `TEHNICNI_ZAPISNIK_RAZLOG` varchar(29) DEFAULT NULL,
  `TEHNICNI_PREGLED_STATUS` varchar(30) DEFAULT NULL,
  `VELJA_OD` varchar(10) DEFAULT NULL,
  `VELJA_DO` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `IDuser` int(11) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Surname` varchar(20) NOT NULL,
  `Password` varchar(64) NOT NULL,
  `Mail` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_queries`
--

CREATE TABLE `user_queries` (
  `query_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `znamka` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `fuel` varchar(50) DEFAULT NULL,
  `max_km` int(11) DEFAULT NULL,
  `min_km` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `znamke`
--

CREATE TABLE `znamke` (
  `znamka_id` int(11) NOT NULL,
  `znamka_ime` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_slovenian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `avtomobili`
--
ALTER TABLE `avtomobili`
  ADD PRIMARY KEY (`VIN`),
  ADD KEY `modeli_id` (`modeli_id`),
  ADD KEY `gorivo_id` (`gorivo_id`),
  ADD KEY `MotStatByQuerry` (`znamka_id`,`modeli_id`,`datum_prve_registracije`,`gorivo_id`,`prevozeni_kilometri`);

--
-- Indexes for table `gorivo`
--
ALTER TABLE `gorivo`
  ADD PRIMARY KEY (`gorivo_id`),
  ADD KEY `gorivo_id` (`gorivo_id`,`gorivo_tip`);

--
-- Indexes for table `modeli`
--
ALTER TABLE `modeli`
  ADD PRIMARY KEY (`model_id`),
  ADD KEY `znamka_id` (`znamka_id`),
  ADD KEY `idx_model_ime` (`model_ime`);

--
-- Indexes for table `refresh_token`
--
ALTER TABLE `refresh_token`
  ADD PRIMARY KEY (`token_hash`);

--
-- Indexes for table `rezultatiTP`
--
ALTER TABLE `rezultatiTP`
  ADD PRIMARY KEY (`IDvnosa`),
  ADD KEY `idx_rezultati_tp_vin` (`VIN`),
  ADD KEY `VIN` (`VIN`,`TEHNICNI_PREGLED_STATUS`,`VELJA_OD`,`VELJA_DO`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`IDuser`);

--
-- Indexes for table `user_queries`
--
ALTER TABLE `user_queries`
  ADD PRIMARY KEY (`query_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `znamke`
--
ALTER TABLE `znamke`
  ADD PRIMARY KEY (`znamka_id`),
  ADD UNIQUE KEY `znamka_ime` (`znamka_ime`),
  ADD KEY `idx_znamka_ime` (`znamka_ime`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gorivo`
--
ALTER TABLE `gorivo`
  MODIFY `gorivo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modeli`
--
ALTER TABLE `modeli`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rezultatiTP`
--
ALTER TABLE `rezultatiTP`
  MODIFY `IDvnosa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `IDuser` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_queries`
--
ALTER TABLE `user_queries`
  MODIFY `query_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `znamke`
--
ALTER TABLE `znamke`
  MODIFY `znamka_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `avtomobili`
--
ALTER TABLE `avtomobili`
  ADD CONSTRAINT `avtomobili_ibfk_1` FOREIGN KEY (`modeli_id`) REFERENCES `modeli` (`model_id`),
  ADD CONSTRAINT `avtomobili_ibfk_2` FOREIGN KEY (`gorivo_id`) REFERENCES `gorivo` (`gorivo_id`),
  ADD CONSTRAINT `avtomobili_ibfk_3` FOREIGN KEY (`VIN`) REFERENCES `rezultatiTP` (`VIN`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cars_znamka` FOREIGN KEY (`znamka_id`) REFERENCES `znamke` (`znamka_id`);

--
-- Constraints for table `modeli`
--
ALTER TABLE `modeli`
  ADD CONSTRAINT `modeli_ibfk_1` FOREIGN KEY (`znamka_id`) REFERENCES `znamke` (`znamka_id`);

--
-- Constraints for table `user_queries`
--
ALTER TABLE `user_queries`
  ADD CONSTRAINT `user_queries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`IDuser`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
