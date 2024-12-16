-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2024 at 08:29 PM
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
-- Database: `dmpcs_dashboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `award_type`
--

CREATE TABLE `award_type` (
  `awardTypeID` varchar(3) NOT NULL,
  `awardType` varchar(18) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `award_type`
--

INSERT INTO `award_type` (`awardTypeID`, `awardType`) VALUES
('CL', 'Cum Laude'),
('CS', 'College Scholar'),
('MCL', 'Magna cum Laude'),
('SCL', 'Summa cum Laude'),
('US', 'University Scholar');

-- --------------------------------------------------------

--
-- Table structure for table `college_degree`
--

CREATE TABLE `college_degree` (
  `degID` varchar(9) NOT NULL,
  `yearLevel` int(1) DEFAULT NULL,
  `degprogID` varchar(4) DEFAULT NULL,
  `timeID` varchar(4) DEFAULT NULL,
  `count` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `college_degree`
--

INSERT INTO `college_degree` (`degID`, `yearLevel`, `degprogID`, `timeID`, `count`) VALUES
('1BSAM23-2', 1, 'BSAM', '23-2', 19),
('1BSCS23-2', 1, 'BSCS', '23-2', 42),
('1BSDS23-1', 1, 'BSDS', '23-1', 37),
('1BSDS23-2', 1, 'BSDS', '23-2', 34),
('2BSAM23-1', 2, 'BSAM', '23-1', 17),
('2BSAM23-2', 2, 'BSAM', '23-2', 18),
('2BSCS23-1', 2, 'BSCS', '23-1', 19),
('2BSDS26-2', 2, 'BSDS', '26-2', 23),
('3BSAM23-1', 3, 'BSAM', '23-1', 25),
('3BSAM23-2', 3, 'BSAM', '23-2', 25),
('3BSCS23-1', 3, 'BSCS', '23-1', 27),
('3BSCS23-2', 3, 'BSCS', '23-2', 25),
('3BSCS24-1', 3, 'BSCS', '24-1', 34),
('3BSFT24-1', 3, 'BSFT', '24-1', 23),
('3BSFT26-2', 3, 'BSFT', '26-2', 23),
('4BSAM23-1', 4, 'BSAM', '23-1', 15),
('4BSAM23-2', 4, 'BSAM', '23-2', 13),
('4BSCS23-1', 4, 'BSCS', '23-1', 34),
('4BSCS23-2', 4, 'BSCS', '23-2', 30);

-- --------------------------------------------------------

--
-- Table structure for table `deg_prog`
--

CREATE TABLE `deg_prog` (
  `degprogID` varchar(4) NOT NULL,
  `name` varchar(43) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `deg_prog`
--

INSERT INTO `deg_prog` (`degprogID`, `name`) VALUES
('BSAM', 'Bachelor of Science in  Applied Mathematics'),
('BSCS', 'Bachelor of Science in Computer Science'),
('BSDS', 'Bachelor of Science in Data Science'),
('BSFT', 'FOOD TEK');

-- --------------------------------------------------------

--
-- Table structure for table `educ_attainment`
--

CREATE TABLE `educ_attainment` (
  `educAttainmentID` varchar(2) NOT NULL,
  `attainment` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `educ_attainment`
--

INSERT INTO `educ_attainment` (`educAttainmentID`, `attainment`) VALUES
('A1', 'Ph.D.'),
('A2', 'M.Sc.'),
('A3', 'M.M'),
('A4', 'MSCS, MICT');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventID` int(11) NOT NULL,
  `eventName` varchar(10) DEFAULT NULL,
  `timeID` varchar(4) DEFAULT NULL,
  `count` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`eventID`, `eventName`, `timeID`, `count`) VALUES
(1, 'Event 1', '23-1', 50),
(2, 'Event 2', '23-1', 45),
(3, 'Event 3', '23-2', 98),
(4, 'Event 4', '23-1', 35),
(5, 'Event 5', '23-1', 8),
(19, 'Event 6', '24-1', 56),
(20, 'Event 7', '26-1', 56);

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `facultyID` int(11) NOT NULL,
  `rankID` varchar(7) DEFAULT NULL,
  `educAttainmentID` varchar(2) DEFAULT NULL,
  `timeID` varchar(4) DEFAULT NULL,
  `count` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`facultyID`, `rankID`, `educAttainmentID`, `timeID`, `count`) VALUES
(3, 'ASSOCP5', 'A4', '23-1', 5),
(6, 'ASSISP3', 'A4', '23-2', 23),
(8, 'ASSISP6', 'A4', '23-1', 2),
(9, 'INS1', 'A2', '23-2', 2),
(10, 'INS2', 'A3', '23-1', 4),
(13, 'LEC', 'A2', '23-1', 5),
(15, 'P3', 'A2', '23-1', 8),
(17, 'P5', 'A2', '23-1', 10),
(19, 'ASSOCP1', 'A3', '23-1', 6),
(20, 'ASSOCP2', 'A4', '23-2', 11),
(21, 'ASSOCP3', 'A2', '23-1', 13),
(24, 'ASSOCP2', 'A3', '26-1', 5),
(28, 'ASSISP1', 'A1', '24-1', 6),
(29, 'ASSISP1', 'A1', '24-1', 4),
(30, 'ASSOCP4', 'A3', '24-1', 12),
(33, 'LEC', 'A3', '26-1', 12),
(35, 'INS3', 'A1', '24-2', 1),
(43, 'P3', 'A3', '26-1', 23);

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `publicationID` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `timeID` varchar(4) DEFAULT NULL,
  `count` int(1) DEFAULT NULL,
  `is_indexed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `publication`
--

INSERT INTO `publication` (`publicationID`, `title`, `timeID`, `count`, `is_indexed`) VALUES
(1, 'Research 1', '23-2', 3, 0),
(2, 'Research 2', '23-1', 2, 0),
(3, 'Research 3', '23-2', 4, 0),
(27, 'Pasyente Project', '23-2', 12, 0);



-- --------------------------------------------------------

--
-- Table structure for table `rank_title`
--

CREATE TABLE `rank_title` (
  `rankID` varchar(7) NOT NULL,
  `title` varchar(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `rank_title`
--

INSERT INTO `rank_title` (`rankID`, `title`) VALUES
('ASSISP1', 'Assistant Professor 1'),
('ASSISP2', 'Assistant Professor 2'),
('ASSISP3', 'Assistant Professor 3'),
('ASSISP4', 'Assistant Professor 4'),
('ASSISP5', 'Assistant Professor 5'),
('ASSISP6', 'Assistant Professor 6'),
('ASSOCP1', 'Associate Professor 1'),
('ASSOCP2', 'Associate Professor 2'),
('ASSOCP3', 'Associate Professor 3'),
('ASSOCP4', 'Associate Professor 4'),
('ASSOCP5', 'Associate Professor 5'),
('INS1', 'Instructor 1'),
('INS2', 'Instructor 2'),
('INS3', 'Instructor 3'),
('LEC', 'Lecturer'),
('P1', 'Professor 1'),
('P2', 'Professor 2'),
('P3', 'Professor 3'),
('P4', 'Professor 4'),
('P5', 'Professor 5'),
('P6', 'Professor 6'),
('SLEC', 'Senior Lecturer');

-- --------------------------------------------------------

--
-- Table structure for table `student_awards`
--

CREATE TABLE `student_awards` (
  `awardID` int(11) NOT NULL,
  `awardTypeID` varchar(3) DEFAULT NULL,
  `degID` varchar(9) DEFAULT NULL,
  `count` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student_awards`
--

INSERT INTO `student_awards` (`awardID`, `awardTypeID`, `degID`, `count`) VALUES
(38, 'MCL', '4BSCS23-2', 23),
(39, 'US', '1BSAM23-2', 24),
(41, 'US', '1BSDS23-1', 46),
(42, 'SCL', '4BSCS23-2', 34);

-- --------------------------------------------------------

--
-- Table structure for table `time_period`
--

CREATE TABLE `time_period` (
  `timeID` varchar(4) NOT NULL,
  `SchoolYear` varchar(9) DEFAULT NULL,
  `semester` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `time_period`
--

INSERT INTO `time_period` (`timeID`, `SchoolYear`, `semester`) VALUES
('23-1', '2022-2023', 1),
('23-2', '2022-2023', 2),
('24-1', '2023-2024', 1),
('24-2', '2023-2024', 2),
('26-1', '2025-2026', 1),
('26-2', '2025-2026', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `award_type`
--
ALTER TABLE `award_type`
  ADD PRIMARY KEY (`awardTypeID`);

--
-- Indexes for table `college_degree`
--
ALTER TABLE `college_degree`
  ADD PRIMARY KEY (`degID`),
  ADD KEY `fk_degprogID` (`degprogID`),
  ADD KEY `fk_timeID` (`timeID`);

--
-- Indexes for table `deg_prog`
--
ALTER TABLE `deg_prog`
  ADD PRIMARY KEY (`degprogID`);

--
-- Indexes for table `educ_attainment`
--
ALTER TABLE `educ_attainment`
  ADD PRIMARY KEY (`educAttainmentID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`eventID`),
  ADD KEY `fk_tm_timeID` (`timeID`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`facultyID`),
  ADD KEY `fk_rankID` (`rankID`),
  ADD KEY `fk_educAttainmentID` (`educAttainmentID`),
  ADD KEY `fk_t_timeID` (`timeID`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`publicationID`),
  ADD KEY `fk_tim_timeID` (`timeID`);

--
-- Indexes for table `rank_title`
--
ALTER TABLE `rank_title`
  ADD PRIMARY KEY (`rankID`);

--
-- Indexes for table `student_awards`
--
ALTER TABLE `student_awards`
  ADD PRIMARY KEY (`awardID`),
  ADD KEY `fk_awardTypeID` (`awardTypeID`),
  ADD KEY `fk_degID` (`degID`);

--
-- Indexes for table `time_period`
--
ALTER TABLE `time_period`
  ADD PRIMARY KEY (`timeID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `facultyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `publicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `student_awards`
--
ALTER TABLE `student_awards`
  MODIFY `awardID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `college_degree`
--
ALTER TABLE `college_degree`
  ADD CONSTRAINT `fk_degprogID` FOREIGN KEY (`degprogID`) REFERENCES `deg_prog` (`degprogID`),
  ADD CONSTRAINT `fk_timeID` FOREIGN KEY (`timeID`) REFERENCES `time_period` (`timeID`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `fk_tm_timeID` FOREIGN KEY (`timeID`) REFERENCES `time_period` (`timeID`);

--
-- Constraints for table `faculty`
--
ALTER TABLE `faculty`
  ADD CONSTRAINT `fk_educAttainmentID` FOREIGN KEY (`educAttainmentID`) REFERENCES `educ_attainment` (`educAttainmentID`),
  ADD CONSTRAINT `fk_rankID` FOREIGN KEY (`rankID`) REFERENCES `rank_title` (`rankID`),
  ADD CONSTRAINT `fk_t_timeID` FOREIGN KEY (`timeID`) REFERENCES `time_period` (`timeID`);

--
-- Constraints for table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `fk_tim_timeID` FOREIGN KEY (`timeID`) REFERENCES `time_period` (`timeID`),
  ADD CONSTRAINT `fk_tme_timeID` FOREIGN KEY (`timeID`) REFERENCES `time_period` (`timeID`);

--
-- Constraints for table `student_awards`
--
ALTER TABLE `student_awards`
  ADD CONSTRAINT `fk_awardTypeID` FOREIGN KEY (`awardTypeID`) REFERENCES `award_type` (`awardTypeID`),
  ADD CONSTRAINT `fk_degID` FOREIGN KEY (`degID`) REFERENCES `college_degree` (`degID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
