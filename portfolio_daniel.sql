-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2026 at 06:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `portfolio_daniel`
--

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_documents`
--

CREATE TABLE `portfolio_documents` (
  `ID` int(11) NOT NULL,
  `File_Name` varchar(255) DEFAULT NULL,
  `File_Path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio_documents`
--

INSERT INTO `portfolio_documents` (`ID`, `File_Name`, `File_Path`) VALUES
(8, 'Certificate_of_Completion_NEW.pdf', 'uploads/Certificate_of_Completion_NEW.pdf'),
(9, 'Revised_Questionnaire.docx', 'uploads/Revised_Questionnaire.docx'),
(10, '(TTL)_EVANGELISTA_DETAILEDLP.docx', 'uploads/(TTL)_EVANGELISTA_DETAILEDLP.docx');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_profile`
--

CREATE TABLE `portfolio_profile` (
  `Last_Name` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Age` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio_profile`
--

INSERT INTO `portfolio_profile` (`Last_Name`, `First_Name`, `Middle_Name`, `Age`) VALUES
('Evangelista', 'Daniel', 'Coton', 21);

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_projects`
--

CREATE TABLE `portfolio_projects` (
  `ID` int(11) NOT NULL,
  `Project_Title` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Screenshot_Path` varchar(255) DEFAULT NULL,
  `Project_Link` varchar(255) DEFAULT NULL,
  `Date_Created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portfolio_projects`
--

INSERT INTO `portfolio_projects` (`ID`, `Project_Title`, `Description`, `Screenshot_Path`, `Project_Link`, `Date_Created`) VALUES
(1, 'UniFind', 'This is my personal project. It focuses on the freshly-high school/senior high school graduates to find their dream university and courses in real-time. It will also helps you to enroll with no hassle online.', 'project_screenshots/DG.png', 'https://github.com/spidaniel45/unifind.git', '2026-01-13 13:51:26'),
(2, 'GamExplorer', 'Welcome to GamExplorer where you can browse games and products/services online', 'project_screenshots/DGFP.png', 'https://github.com/spidaniel45/GamExplorer.git', '2026-01-13 13:53:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `portfolio_documents`
--
ALTER TABLE `portfolio_documents`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `portfolio_projects`
--
ALTER TABLE `portfolio_projects`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `portfolio_documents`
--
ALTER TABLE `portfolio_documents`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `portfolio_projects`
--
ALTER TABLE `portfolio_projects`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
