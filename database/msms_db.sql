-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 02:08 PM
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
-- Database: `msms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`, `created_at`) VALUES
(1, 'admin@msms.com', '$2y$10$ukrcPjXNrc26lHDWzOfave.PWm8XB2aiLI4cL2zQ5uG.7JxJMunX.', '2026-02-01 03:11:21');

-- --------------------------------------------------------

--
-- Table structure for table `avr_borrowed`
--

CREATE TABLE `avr_borrowed` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `Item` varchar(255) NOT NULL,
  `DateBorrowed` date NOT NULL,
  `DueDate` date NOT NULL,
  `Status` varchar(50) DEFAULT 'Active' COMMENT 'Active, Returned, Overdue'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `avr_borrowed`
--

INSERT INTO `avr_borrowed` (`ID`, `Name`, `Quantity`, `Item`, `DateBorrowed`, `DueDate`, `Status`) VALUES
(1, 'Mr. Juan Dela Cruz', 1, 'Projector', '2026-01-31', '2026-02-07', 'Overdue'),
(2, 'Ms. Maria Santos', 1, 'Sound System', '2026-02-01', '2026-02-08', 'Overdue'),
(3, 'Mr. Carlos Reyes', 2, 'Microphone Set', '2026-02-02', '2026-02-09', 'Overdue'),
(4, 'Ms. Ana Garcia', 1, 'Projector', '2026-01-23', '2026-01-30', 'Returned'),
(5, 'Mr. Roberto Tan', 1, 'LED Screen', '2026-01-25', '2026-02-01', 'Returned'),
(6, 'Ms. Jennifer Lim', 1, 'Video Camera', '2026-01-21', '2026-01-28', 'Overdue'),
(7, 'Mr. Michael Torres', 1, 'Audio Mixer', '2026-01-29', '2026-02-05', 'Overdue'),
(8, 'Ms. Sarah Martinez', 1, 'Laptop', '2026-01-27', '2026-02-03', 'Overdue'),
(9, 'Mr. David Ong', 1, 'Document Camera', '2026-01-13', '2026-01-20', 'Returned'),
(10, 'Ms. Michelle Ann Lim', 1, 'Projector', '2026-01-08', '2026-01-15', 'Returned'),
(11, 'Mr. Juan Dela Cruz', 1, 'Projector', '2026-01-31', '2026-02-07', 'Overdue'),
(12, 'Ms. Maria Santos', 1, 'Sound System', '2026-02-01', '2026-02-08', 'Overdue'),
(13, 'Mr. Carlos Reyes', 2, 'Microphone Set', '2026-02-02', '2026-02-09', 'Overdue'),
(14, 'Ms. Ana Garcia', 1, 'Projector', '2026-01-23', '2026-01-30', 'Returned'),
(15, 'Mr. Roberto Tan', 1, 'LED Screen', '2026-01-25', '2026-02-01', 'Returned'),
(16, 'Ms. Jennifer Lim', 1, 'Video Camera', '2026-01-21', '2026-01-28', 'Overdue'),
(17, 'Mr. Michael Torres', 1, 'Audio Mixer', '2026-01-29', '2026-02-05', 'Overdue'),
(18, 'Ms. Sarah Martinez', 1, 'Laptop', '2026-01-27', '2026-02-03', 'Overdue'),
(19, 'Mr. David Ong', 1, 'Document Camera', '2026-01-13', '2026-01-20', 'Returned'),
(20, 'Ms. Michelle Ann Lim', 1, 'Projector', '2026-01-08', '2026-01-15', 'Returned'),
(21, 'Science Dept', 1, 'Projector HD', '2026-02-12', '2026-02-15', 'Overdue');

-- --------------------------------------------------------

--
-- Table structure for table `avr_inventory`
--

CREATE TABLE `avr_inventory` (
  `ItemID` int(11) NOT NULL,
  `Item` varchar(255) NOT NULL,
  `Description` text DEFAULT NULL,
  `Model` varchar(255) DEFAULT NULL,
  `SerialNo` varchar(255) DEFAULT NULL,
  `DateReceived` date DEFAULT NULL,
  `Remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `avr_inventory`
--

INSERT INTO `avr_inventory` (`ItemID`, `Item`, `Description`, `Model`, `SerialNo`, `DateReceived`, `Remark`) VALUES
(1, 'Projector', 'HD Projector for presentations', 'EPSON-XP6000', 'AVR001', '2025-01-10', 'Good condition'),
(2, 'Sound System', 'PA System with speakers', 'BOSE-L1', 'AVR002', '2025-01-15', 'Fully functional'),
(3, 'Microphone Set', 'Wireless microphone system', 'SHURE-BLX', 'AVR003', '2025-01-20', '2 microphones included'),
(4, 'LED Screen', 'Large LED display screen', 'SAMSUNG-LED75', 'AVR004', '2025-01-25', 'Wall mounted'),
(5, 'Video Camera', 'HD Video camera', 'CANON-XA11', 'AVR005', '2025-02-01', 'With tripod'),
(6, 'Audio Mixer', '8-channel audio mixer', 'BEHRINGER-XENYX', 'AVR006', '2025-01-12', 'Professional grade'),
(7, 'Laptop', 'Presentation laptop', 'DELL-LATITUDE', 'AVR007', '2025-01-18', 'Windows 11'),
(8, 'Document Camera', 'Visual presenter/document camera', 'ELMO-TT12', 'AVR008', '2025-01-22', 'HD quality'),
(9, 'Projector', 'HD Projector for presentations', 'EPSON-XP6000', 'AVR001', '2025-01-10', 'Good condition'),
(10, 'Sound System', 'PA System with speakers', 'BOSE-L1', 'AVR002', '2025-01-15', 'Fully functional'),
(11, 'Microphone Set', 'Wireless microphone system', 'SHURE-BLX', 'AVR003', '2025-01-20', '2 microphones included'),
(12, 'LED Screen', 'Large LED display screen', 'SAMSUNG-LED75', 'AVR004', '2025-01-25', 'Wall mounted'),
(13, 'Video Camera', 'HD Video camera', 'CANON-XA11', 'AVR005', '2025-02-01', 'With tripod'),
(14, 'Audio Mixer', '8-channel audio mixer', 'BEHRINGER-XENYX', 'AVR006', '2025-01-12', 'Professional grade'),
(15, 'Laptop', 'Presentation laptop', 'DELL-LATITUDE', 'AVR007', '2025-01-18', 'Windows 11'),
(16, 'Document Camera', 'Visual presenter/document camera', 'ELMO-TT12', 'AVR008', '2025-01-22', 'HD quality'),
(17, 'Projector HD', 'HD Projector', 'EPSON ELP-100', NULL, '2026-02-12', 'New'),
(18, 'Speaker', 'Bluetooth Speaker', 'JBL X100', NULL, '2026-02-12', 'Good');

-- --------------------------------------------------------

--
-- Table structure for table `avr_reservation`
--

CREATE TABLE `avr_reservation` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Department` varchar(255) NOT NULL,
  `Date` date NOT NULL,
  `Time` time NOT NULL,
  `Purpose` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `avr_reservation`
--

INSERT INTO `avr_reservation` (`ID`, `Name`, `Department`, `Date`, `Time`, `Purpose`) VALUES
(1, 'Ms. Maria Santos', 'Science Department', '2026-02-02', '10:00:00', 'Science Fair Presentation'),
(2, 'Mr. Carlos Reyes', 'English Department', '2026-02-02', '14:00:00', 'Literary Reading Event'),
(3, 'Ms. Ana Garcia', 'Math Department', '2026-02-03', '09:00:00', 'Math Competition'),
(4, 'Mr. Roberto Tan', 'History Department', '2026-02-04', '13:00:00', 'Historical Presentation'),
(5, 'Ms. Jennifer Lim', 'Science Department', '2026-02-05', '10:30:00', 'Science Experiment Demo'),
(6, 'Mr. Michael Torres', 'PE Department', '2026-01-30', '08:00:00', 'Sports Day Event'),
(7, 'Ms. Sarah Martinez', 'Music Department', '2026-01-28', '15:00:00', 'Music Concert'),
(8, 'Mr. David Ong', 'Art Department', '2026-01-18', '11:00:00', 'Art Exhibition'),
(9, 'Ms. Michelle Ann Lim', 'Science Department', '2026-01-13', '09:30:00', 'Science Week Opening'),
(10, 'Ms. Maria Santos', 'Science Department', '2026-02-02', '10:00:00', 'Science Fair Presentation'),
(11, 'Mr. Carlos Reyes', 'English Department', '2026-02-02', '14:00:00', 'Literary Reading Event'),
(12, 'Ms. Ana Garcia', 'Math Department', '2026-02-03', '09:00:00', 'Math Competition'),
(13, 'Mr. Roberto Tan', 'History Department', '2026-02-04', '13:00:00', 'Historical Presentation'),
(14, 'Ms. Jennifer Lim', 'Science Department', '2026-02-05', '10:30:00', 'Science Experiment Demo'),
(15, 'Mr. Michael Torres', 'PE Department', '2026-01-30', '08:00:00', 'Sports Day Event'),
(16, 'Ms. Sarah Martinez', 'Music Department', '2026-01-28', '15:00:00', 'Music Concert'),
(17, 'Mr. David Ong', 'Art Department', '2026-01-18', '11:00:00', 'Art Exhibition'),
(18, 'Ms. Michelle Ann Lim', 'Science Department', '2026-01-13', '09:30:00', 'Science Week Opening'),
(19, 'Mr. Garcia', 'Math Dept', '2026-02-13', '09:00:00', 'Presentation');

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `edition` varchar(20) DEFAULT NULL,
  `number_of_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 0,
  `shelf_location` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_filename` varchar(255) DEFAULT NULL,
  `status` enum('Available','Borrowed','Lost','Damaged') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`book_id`, `title`, `author`, `isbn`, `category`, `publisher`, `publication_year`, `edition`, `number_of_copies`, `available_copies`, `shelf_location`, `description`, `image_filename`, `status`) VALUES
(1, 'Introduction to Chemistry', 'Dr. John Smith', '978-0123456789', 'Science', 'Academic Press', '2020', '3rd Edition', 5, 4, 'Shelf A-1', 'Comprehensive guide to chemistry fundamentals', NULL, 'Available'),
(2, 'Biology Essentials', 'Dr. Maria Johnson', '978-0123456790', 'Science', 'Science Publishers', '2021', '2nd Edition', 4, 2, 'Shelf A-2', 'Essential biology concepts for students', NULL, 'Available'),
(3, 'Physics Principles', 'Dr. Robert Brown', '978-0123456791', 'Science', 'Physics Press', '2019', '4th Edition', 6, 4, 'Shelf A-3', 'Fundamental physics principles', NULL, 'Available'),
(4, 'Mathematics for High School', 'Dr. Lisa White', '978-0123456792', 'Mathematics', 'Math Publishers', '2022', '1st Edition', 8, 6, 'Shelf B-1', 'Complete mathematics guide', NULL, 'Available'),
(5, 'English Literature', 'Dr. James Green', '978-0123456793', 'Literature', 'Literary Press', '2020', '2nd Edition', 5, 3, 'Shelf C-1', 'Classic and modern literature', NULL, 'Available'),
(6, 'World History', 'Dr. Patricia Black', '978-0123456794', 'History', 'History Publishers', '2021', '1st Edition', 4, 2, 'Shelf D-1', 'Comprehensive world history', NULL, 'Available'),
(7, 'Computer Science Basics', 'Dr. Michael Gray', '978-0123456795', 'Technology', 'Tech Press', '2023', '1st Edition', 7, 5, 'Shelf E-1', 'Introduction to computer science', NULL, 'Available'),
(8, 'Filipino Language', 'Dr. Anna Red', '978-0123456796', 'Language', 'Language Press', '2020', '3rd Edition', 6, 4, 'Shelf F-1', 'Filipino language and literature', NULL, 'Available'),
(9, 'Introduction to Chemistry', 'Dr. John Smith', '978-0123456789', 'Science', 'Academic Press', '2020', '3rd Edition', 5, 3, 'Shelf A-1', 'Comprehensive guide to chemistry fundamentals', NULL, 'Available'),
(10, 'Biology Essentials', 'Dr. Maria Johnson', '978-0123456790', 'Science', 'Science Publishers', '2021', '2nd Edition', 4, 2, 'Shelf A-2', 'Essential biology concepts for students', NULL, 'Available'),
(11, 'Physics Principles', 'Dr. Robert Brown', '978-0123456791', 'Science', 'Physics Press', '2019', '4th Edition', 6, 4, 'Shelf A-3', 'Fundamental physics principles', NULL, 'Available'),
(12, 'Mathematics for High School', 'Dr. Lisa White', '978-0123456792', 'Mathematics', 'Math Publishers', '2022', '1st Edition', 8, 6, 'Shelf B-1', 'Complete mathematics guide', NULL, 'Available'),
(13, 'English Literature', 'Dr. James Green', '978-0123456793', 'Literature', 'Literary Press', '2020', '2nd Edition', 5, 3, 'Shelf C-1', 'Classic and modern literature', NULL, 'Available'),
(14, 'World History', 'Dr. Patricia Black', '978-0123456794', 'History', 'History Publishers', '2021', '1st Edition', 4, 2, 'Shelf D-1', 'Comprehensive world history', NULL, 'Available'),
(15, 'Computer Science Basics', 'Dr. Michael Gray', '978-0123456795', 'Technology', 'Tech Press', '2023', '1st Edition', 7, 5, 'Shelf E-1', 'Introduction to computer science', NULL, 'Available'),
(16, 'Filipino Language', 'Dr. Anna Red', '978-0123456796', 'Language', 'Language Press', '2020', '3rd Edition', 6, 4, 'Shelf F-1', 'Filipino language and literature', NULL, 'Available'),
(17, 'General Chemistry', 'Author One', NULL, NULL, NULL, NULL, NULL, 5, 3, NULL, NULL, NULL, 'Available'),
(18, 'Biology 101', 'Author Two', NULL, NULL, NULL, NULL, NULL, 3, 2, NULL, NULL, NULL, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `borrowing_transaction`
--

CREATE TABLE `borrowing_transaction` (
  `transaction_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `librarian_id` int(11) DEFAULT NULL,
  `grade_section` varchar(255) NOT NULL,
  `rfid_number` varchar(255) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Reserved','Borrowed','Returned','Overdue') DEFAULT 'Reserved',
  `late_penalty` decimal(10,2) DEFAULT 0.00,
  `lost_penalty` decimal(10,2) DEFAULT 0.00,
  `damage_penalty` decimal(10,2) DEFAULT 0.00,
  `total_penalty` decimal(10,2) DEFAULT 0.00,
  `penalty_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowing_transaction`
--

INSERT INTO `borrowing_transaction` (`transaction_id`, `book_id`, `user_id`, `librarian_id`, `grade_section`, `rfid_number`, `borrow_date`, `due_date`, `return_date`, `status`, `late_penalty`, `lost_penalty`, `damage_penalty`, `total_penalty`, `penalty_updated_at`) VALUES
(1, 1, 1, 1, '10-A', 'RFID001', '2026-01-28', '2026-02-04', '2026-02-15', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(2, 2, 2, 1, '10-A', 'RFID002', '2026-01-30', '2026-02-06', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(3, 3, 3, 1, '10-B', 'RFID003', '2026-01-26', '2026-02-02', NULL, 'Overdue', 50.00, 0.00, 0.00, 50.00, NULL),
(4, 4, 1, 1, '10-A', 'RFID001', '2026-01-23', '2026-01-30', '2026-01-30', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(5, 5, 2, 1, '10-A', 'RFID002', '2026-01-25', '2026-02-01', '2026-02-01', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(6, 6, 4, 1, '11-A', 'RFID004', '2026-02-02', '2026-02-09', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(7, 7, 5, 1, '11-B', 'RFID005', '2026-02-02', '2026-02-09', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(8, 8, 1, 1, '10-A', 'RFID001', '2026-01-29', '2026-02-05', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(9, 1, 3, 1, '10-B', 'RFID003', '2026-01-27', '2026-02-03', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(10, 2, 4, 1, '11-A', 'RFID004', '2026-01-13', '2026-01-20', '2026-01-20', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(11, 3, 5, 1, '11-B', 'RFID005', '2026-01-08', '2026-01-15', '2026-01-15', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(12, 1, 1, 1, '10-A', 'RFID001', '2026-01-28', '2026-02-04', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(13, 2, 2, 1, '10-A', 'RFID002', '2026-01-30', '2026-02-06', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(14, 3, 3, 1, '10-B', 'RFID003', '2026-01-26', '2026-02-02', NULL, 'Overdue', 50.00, 0.00, 0.00, 50.00, NULL),
(15, 4, 1, 1, '10-A', 'RFID001', '2026-01-23', '2026-01-30', '2026-01-30', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(16, 5, 2, 1, '10-A', 'RFID002', '2026-01-25', '2026-02-01', '2026-02-01', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(17, 6, 4, 1, '11-A', 'RFID004', '2026-02-02', '2026-02-09', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(18, 7, 5, 1, '11-B', 'RFID005', '2026-02-02', '2026-02-09', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(19, 8, 1, 1, '10-A', 'RFID001', '2026-01-29', '2026-02-05', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(20, 1, 3, 1, '10-B', 'RFID003', '2026-01-27', '2026-02-03', NULL, 'Borrowed', 0.00, 0.00, 0.00, 0.00, NULL),
(21, 2, 4, 1, '11-A', 'RFID004', '2026-01-13', '2026-01-20', '2026-01-20', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL),
(22, 3, 5, 1, '11-B', 'RFID005', '2026-01-08', '2026-01-15', '2026-01-15', 'Returned', 0.00, 0.00, 0.00, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `grade` double NOT NULL DEFAULT 0,
  `section` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clinic_log`
--

CREATE TABLE `clinic_log` (
  `id` int(11) NOT NULL,
  `clinic_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `grade_section` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinic_log`
--

INSERT INTO `clinic_log` (`id`, `clinic_id`, `name`, `grade_section`, `date`, `time`) VALUES
(1, '2025-00101', 'Santos, Maria Elena', 'Grade 7 - Saint Francis', '2026-02-02', '08:15:00'),
(2, '2025-00102', 'Reyes, Juan Carlos', 'Grade 8 - Saint Augustine', '2026-02-02', '09:30:00'),
(3, '2025-00103', 'Cruz, Ana Patricia', 'Grade 9 - Saint Nicholas', '2026-02-02', '10:45:00'),
(4, '2025-00104', 'Garcia, Miguel Antonio', 'Grade 10 - Saint Therese', '2026-02-02', '11:20:00'),
(5, '2025-00105', 'Torres, Sofia Isabel', 'Grade 11 - STEM', '2026-02-02', '13:00:00'),
(6, '2025-00106', 'Lopez, Diego Emmanuel', 'Grade 12 - HUMSS', '2026-02-02', '14:15:00'),
(7, '2025-00107', 'Martinez, Carmen Rosa', 'Grade 7 - Saint Claire', '2026-02-01', '09:00:00'),
(8, '2025-00108', 'Fernandez, Luis Miguel', 'Grade 8 - Saint Stephen', '2026-02-01', '10:30:00'),
(9, '2025-00109', 'Ramirez, Elena Victoria', 'Grade 9 - Saint Ursula', '2026-01-31', '11:00:00'),
(10, '2025-00110', 'Gonzalez, Pablo Andres', 'Grade 10 - Saint Thomas', '2026-01-31', '14:00:00'),
(11, '2025-00111', 'Herrera, Lucia Maria', 'Grade 11 - ABM', '2026-01-30', '08:45:00'),
(12, '2025-00112', 'Diaz, Carlos Fernando', 'Grade 12 - Our Lady of Immaculate Conception', '2026-01-30', '12:30:00'),
(13, '2025-00113', 'Morales, Isabel Carmen', 'Grade 7 - Saint Elizabeth', '2026-01-29', '10:15:00'),
(14, '2025-00114', 'Ortiz, Rafael Jose', 'Kindergarten - St. Michael (AM)', '2026-01-28', '09:30:00'),
(15, '2025-00115', 'Rivera, Teresa Ana', 'Grade 8 - Saint Marie', '2026-01-27', '13:45:00'),
(16, 'C1', 'Juan Dela Cruz', '10-A', '2026-02-12', '10:11:35'),
(17, '2026-00110', 'Raymart Dave Silvosa', '12 - A-Rizal', '2026-02-18', '13:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `clinic_records`
--

CREATE TABLE `clinic_records` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `grade_section` varchar(50) NOT NULL,
  `complaint` text NOT NULL,
  `treatment` text NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinic_records`
--

INSERT INTO `clinic_records` (`id`, `student_id`, `name`, `grade_section`, `complaint`, `treatment`, `date`, `time`, `date_created`) VALUES
(1, '2025-00101', 'Santos, Maria Elena', 'Grade 7 - Saint Francis', 'Headache', 'Rest, paracetamol given', '2026-02-02', '08:20:00', '2026-02-02 05:51:15'),
(2, '2025-00102', 'Reyes, Juan Carlos', 'Grade 8 - Saint Augustine', 'Stomach ache', 'Antacid, advised to avoid spicy food', '2026-02-02', '09:35:00', '2026-02-02 05:51:15'),
(3, '2025-00103', 'Cruz, Ana Patricia', 'Grade 9 - Saint Nicholas', 'Fever', 'Temp checked 37.8??C, paracetamol, sent home', '2026-02-02', '10:50:00', '2026-02-02 05:51:15'),
(4, '2025-00104', 'Garcia, Miguel Antonio', 'Grade 10 - Saint Therese', 'Minor cut on finger', 'Cleaned, bandaged', '2026-02-02', '11:25:00', '2026-02-02 05:51:15'),
(5, '2025-00105', 'Torres, Sofia Isabel', 'Grade 11 - STEM', 'Dizziness', 'Rest in clinic, water, observed 30 min', '2026-02-02', '13:05:00', '2026-02-02 05:51:15'),
(6, '2025-00106', 'Lopez, Diego Emmanuel', 'Grade 12 - HUMSS', 'Cough and cold', 'Neosep, advised rest', '2026-02-02', '14:20:00', '2026-02-02 05:51:15'),
(7, '2025-00107', 'Martinez, Carmen Rosa', 'Grade 7 - Saint Claire', 'Allergy (itchy skin)', 'Antihistamine, advised to avoid trigger', '2026-02-01', '09:10:00', '2026-02-02 05:51:15'),
(8, '2025-00108', 'Fernandez, Luis Miguel', 'Grade 8 - Saint Stephen', 'Sprain ankle', 'Ice pack, compression, rest', '2026-02-01', '10:40:00', '2026-02-02 05:51:15'),
(9, '2025-00109', 'Ramirez, Elena Victoria', 'Grade 9 - Saint Ursula', 'Sore throat', 'Lozenges, warm water, advised to see doctor if persists', '2026-01-31', '11:10:00', '2026-02-02 05:51:15'),
(10, '2025-00110', 'Gonzalez, Pablo Andres', 'Grade 10 - Saint Thomas', 'Headache', 'Rest in clinic, paracetamol', '2026-01-31', '14:05:00', '2026-02-02 05:51:15'),
(11, '2025-00111', 'Herrera, Lucia Maria', 'Grade 11 - ABM', 'Nausea', 'Rest, water, light snack', '2026-01-30', '08:50:00', '2026-02-02 05:51:15'),
(12, '2025-00112', 'Diaz, Carlos Fernando', 'Grade 12 - Our Lady of Immaculate Conception', 'Asthma (mild)', 'Inhaler used, rest', '2026-01-30', '12:35:00', '2026-02-02 05:51:15'),
(13, '2025-00113', 'Morales, Isabel Carmen', 'Grade 7 - Saint Elizabeth', 'Scratches from fall', 'Cleaned, antiseptic, bandage', '2026-01-29', '10:20:00', '2026-02-02 05:51:15'),
(14, '2025-00114', 'Ortiz, Rafael Jose', 'Kindergarten - St. Michael (AM)', 'Small wound on knee', 'Cleaned, band-aid', '2026-01-28', '09:35:00', '2026-02-02 05:51:15'),
(15, '2025-00115', 'Rivera, Teresa Ana', 'Grade 8 - Saint Marie', 'Eye irritation', 'Eye wash, advised to avoid rubbing', '2026-01-27', '13:50:00', '2026-02-02 05:51:15'),
(16, '2025-00101', 'Santos, Maria Elena', 'Grade 7 - Saint Francis', 'Follow-up headache', 'Feeling better, no meds', '2026-01-31', '15:00:00', '2026-02-02 05:51:15'),
(17, '2025-00104', 'Garcia, Miguel Antonio', 'Grade 10 - Saint Therese', 'Headache', 'Paracetamol', '2026-01-29', '11:00:00', '2026-02-02 05:51:15'),
(18, 'S100001', 'Juan Dela Cruz', '10-A', 'Headache', 'Rest and water', NULL, NULL, '2026-02-12 10:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `counters`
--

CREATE TABLE `counters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `service_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_online` tinyint(1) DEFAULT 1,
  `current_customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counters`
--

INSERT INTO `counters` (`id`, `name`, `service_types`, `is_online`, `current_customer_id`) VALUES
(1, 'Counter 1', '[\"general\", \"payment\", \"inquiry\"]', 1, NULL),
(2, 'Counter 2', '[\"technical\", \"support\"]', 1, NULL),
(3, 'Counter 3', '[\"general\", \"payment\"]', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `queue_number` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `status` enum('waiting','serving','completed','cancelled') DEFAULT 'waiting',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `called_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `queue_number`, `name`, `service_type`, `status`, `created_at`, `called_at`, `completed_at`) VALUES
(1, 'Q001', 'John Michael Santos', 'general', 'completed', '2026-02-02 00:00:00', '2026-02-02 06:59:03', '2026-02-02 06:59:05'),
(2, 'Q002', 'Maria Cristina Reyes', 'payment', 'completed', '2026-02-02 00:15:00', '2026-02-02 06:58:58', '2026-02-02 06:59:05'),
(3, 'Q003', 'Carlos Antonio Cruz', 'inquiry', 'completed', '2026-02-02 00:30:00', '2026-02-02 02:21:04', '2026-02-02 06:58:57'),
(4, 'Q004', 'Ana Patricia Dela Cruz', 'general', 'completed', '2026-02-02 01:00:00', '2026-02-02 01:05:00', '2026-02-02 06:58:55'),
(5, 'Q005', 'Roberto Jose Garcia', 'payment', 'completed', '2026-02-02 01:15:00', '2026-02-02 01:20:00', '2026-02-02 02:23:09'),
(6, 'Q006', 'Jennifer Lynn Tan', 'inquiry', 'completed', '2026-02-02 00:45:00', '2026-02-02 00:50:00', '2026-02-02 01:00:00'),
(7, 'Q007', 'Michael Angelo Torres', 'general', 'completed', '2026-02-02 01:30:00', '2026-02-02 01:35:00', '2026-02-02 01:45:00'),
(8, 'Q008', 'Sarah Jane Martinez', 'payment', 'completed', '2026-02-01 00:00:00', '2026-02-01 00:05:00', '2026-02-01 00:15:00'),
(9, 'Q009', 'David Christopher Ong', 'inquiry', 'completed', '2026-02-01 01:00:00', '2026-02-01 01:05:00', '2026-02-01 01:20:00'),
(10, 'Q010', 'Michelle Ann Lim', 'general', 'completed', '2026-01-30 00:00:00', '2026-01-30 00:05:00', '2026-01-30 00:15:00'),
(11, 'Q011', 'James Patrick Sy', 'payment', 'completed', '2026-01-29 01:00:00', '2026-01-29 01:05:00', '2026-01-29 01:25:00'),
(12, 'Q012', 'Christine Marie Villanueva', 'inquiry', 'completed', '2026-01-28 02:00:00', '2026-01-28 02:05:00', '2026-01-28 02:20:00'),
(13, 'Q013', 'Mark Anthony Ramos', 'general', 'completed', '2026-01-18 00:00:00', '2026-01-18 00:05:00', '2026-01-18 00:18:00'),
(14, 'Q014', 'Grace Elizabeth Chua', 'payment', 'completed', '2026-01-13 01:00:00', '2026-01-13 01:05:00', '2026-01-13 01:22:00'),
(15, 'Q015', 'Paul Vincent Yu', 'inquiry', 'completed', '2026-01-08 02:00:00', '2026-01-08 02:05:00', '2026-01-08 02:25:00'),
(31, 'P001', 'nfghgfh', 'payment', 'completed', '2026-02-05 08:17:50', '2026-02-05 08:18:21', '2026-02-05 08:18:44'),
(32, 'Q20260212-001', 'Visitor One', 'inquiry', 'completed', '2026-02-12 10:11:35', '2026-02-12 10:45:07', '2026-02-12 10:45:14'),
(33, 'I001', 'sfsdfdf', 'inquiry', 'completed', '2026-02-12 10:44:37', '2026-02-12 10:44:58', '2026-02-12 10:45:05'),
(34, 'I002', 'qawewretwr', 'inquiry', 'completed', '2026-02-12 10:45:21', '2026-02-12 10:45:23', '2026-02-12 10:45:25'),
(38, 'I003', 'asfdsd', 'inquiry', 'completed', '2026-02-12 10:45:41', '2026-02-12 10:45:42', '2026-02-12 10:45:45'),
(56, 'G001', 'Test User', 'general', 'completed', '2026-02-17 12:24:12', '2026-02-17 12:25:42', '2026-02-17 12:25:51'),
(57, 'G002', 'Test User', 'general', 'completed', '2026-02-17 12:24:16', '2026-02-17 12:25:36', '2026-02-17 12:25:41'),
(59, 'P002', 'Raymart Dave Silvosa', 'payment', 'completed', '2026-02-17 12:24:59', '2026-02-17 12:25:17', '2026-02-17 12:25:35'),
(66, 'P003', 'Test', 'payment', 'completed', '2026-02-17 12:31:31', '2026-02-17 12:34:11', '2026-02-17 12:35:52'),
(67, 'P004', 'Test', 'payment', 'completed', '2026-02-17 12:31:35', '2026-02-17 12:35:55', '2026-02-17 12:35:56'),
(116, 'I004', 'Shaina Palmera', 'inquiry', 'completed', '2026-02-17 12:38:52', '2026-02-17 12:38:59', '2026-02-17 12:40:44'),
(117, 'I005', 'Raymart Dave Silvosa', 'inquiry', 'completed', '2026-02-17 12:42:51', '2026-02-17 12:42:55', '2026-02-17 12:44:25'),
(118, 'P005', 'Shaina Palmera', 'payment', 'completed', '2026-02-17 12:44:29', '2026-02-17 12:44:36', '2026-02-17 12:45:53'),
(119, 'P006', 'Raymart Dave Silvosa', 'payment', 'completed', '2026-02-17 12:45:59', '2026-02-17 12:46:00', '2026-02-17 12:47:18'),
(120, 'P007', 'Shaina Palmera', 'payment', 'completed', '2026-02-17 12:47:16', '2026-02-17 12:47:19', '2026-02-17 12:49:46'),
(121, 'P008', 'Raymart Dave Silvosa', 'payment', 'completed', '2026-02-17 12:49:44', '2026-02-17 12:49:47', '2026-02-17 12:50:28'),
(122, 'P009', 'Shaina Palmera', 'payment', 'completed', '2026-02-17 12:51:03', '2026-02-17 12:51:14', '2026-02-17 12:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `display_settings`
--

CREATE TABLE `display_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT 'Customer Service',
  `welcome_message` text DEFAULT NULL,
  `refresh_interval` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `display_settings`
--

INSERT INTO `display_settings` (`id`, `company_name`, `welcome_message`, `refresh_interval`) VALUES
(1, 'Holy Cross of Mintal, Inc.', 'Welcome to MSMS Queue Management', 10);

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `sent_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ict_categories`
--

CREATE TABLE `ict_categories` (
  `ID` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ict_categories`
--

INSERT INTO `ict_categories` (`ID`, `category_name`) VALUES
(1, 'RAM'),
(2, 'PROCESSOR'),
(3, 'SSD'),
(4, 'POWER SUPPLY'),
(5, 'UTP CABLE');

-- --------------------------------------------------------

--
-- Table structure for table `ict_inventory`
--

CREATE TABLE `ict_inventory` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `model_no` varchar(255) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `date_added` date NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `location_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ict_locations`
--

CREATE TABLE `ict_locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ict_locations`
--

INSERT INTO `ict_locations` (`location_id`, `location_name`) VALUES
(1, 'RACK 1'),
(2, 'RACK 2');

-- --------------------------------------------------------

--
-- Table structure for table `ict_logs`
--

CREATE TABLE `ict_logs` (
  `log_id` int(11) NOT NULL,
  `time_in` timestamp NULL DEFAULT NULL,
  `time_out` timestamp NULL DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ict_users`
--

CREATE TABLE `ict_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `student_id` varchar(255) DEFAULT NULL,
  `rfid_number` varchar(255) DEFAULT NULL,
  `grade_section` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ict_users`
--

INSERT INTO `ict_users` (`id`, `username`, `password`, `role`, `fullname`, `student_id`, `rfid_number`, `grade_section`) VALUES
(1, 'admin', '$2a$12$wHM2mbkWsag.4eesTKHlKeJSn3SmS/.l52rfMMwGT8rz15PzkCAJu', 'admin', 'ICT Admin', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `model_no` varchar(255) NOT NULL,
  `serial_no` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_added` date NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `location_id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `item_name`, `description`, `model_no`, `serial_no`, `quantity`, `date_added`, `remarks`, `location_id`, `lab_id`) VALUES
(1, 'Microscope', 'Compound light microscope for biology classes', 'M-2000', 'MS001', 15, '2025-01-15', 'Good condition', 1, 1),
(2, 'Bunsen Burner', 'Laboratory gas burner', 'BB-500', 'MS002', 25, '2025-01-20', 'All functional', 1, 1),
(3, 'Beaker Set', 'Set of 5 beakers (50ml, 100ml, 250ml, 500ml, 1000ml)', 'BS-COMPLETE', 'MS003', 30, '2025-01-18', 'Complete set', 1, 1),
(4, 'Test Tube Rack', 'Wooden test tube rack', 'TTR-24', 'MS004', 20, '2025-01-22', 'New stock', 1, 1),
(5, 'Digital Balance', 'Electronic weighing scale', 'DB-2000', 'MS005', 8, '2025-01-25', 'Calibrated', 1, 1),
(6, 'pH Meter', 'Digital pH meter', 'PH-PRO', 'MS006', 10, '2025-02-01', 'Recently calibrated', 1, 1),
(7, 'Safety Goggles', 'Protective eyewear', 'SG-100', 'MS007', 50, '2025-01-10', 'ANSI approved', 1, 1),
(8, 'Lab Coat', 'White laboratory coat', 'LC-STD', 'MS008', 40, '2025-01-12', 'Various sizes', 1, 1),
(9, 'Microscope', 'Compound light microscope for biology classes', 'M-2000', 'MS001', 15, '2025-01-15', 'Good condition', 1, 1),
(10, 'Bunsen Burner', 'Laboratory gas burner', 'BB-500', 'MS002', 25, '2025-01-20', 'All functional', 1, 1),
(11, 'Beaker Set', 'Set of 5 beakers (50ml, 100ml, 250ml, 500ml, 1000ml)', 'BS-COMPLETE', 'MS003', 30, '2025-01-18', 'Complete set', 1, 1),
(12, 'Test Tube Rack', 'Wooden test tube rack', 'TTR-24', 'MS004', 20, '2025-01-22', 'New stock', 1, 1),
(13, 'Digital Balance', 'Electronic weighing scale', 'DB-2000', 'MS005', 8, '2025-01-25', 'Calibrated', 1, 1),
(14, 'pH Meter', 'Digital pH meter', 'PH-PRO', 'MS006', 10, '2025-02-01', 'Recently calibrated', 1, 1),
(15, 'Safety Goggles', 'Protective eyewear', 'SG-100', 'MS007', 50, '2025-01-10', 'ANSI approved', 1, 1),
(16, 'Lab Coat', 'White laboratory coat', 'LC-STD', 'MS008', 40, '2025-01-12', 'Various sizes', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `lab_id` int(11) NOT NULL,
  `lab_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`lab_id`, `lab_name`) VALUES
(1, 'sciLab1'),
(2, 'sciLab2'),
(3, 'Chemistry Lab');

-- --------------------------------------------------------

--
-- Table structure for table `librarian`
--

CREATE TABLE `librarian` (
  `librarian_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Head','Working Student') NOT NULL,
  `institutional_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `librarian`
--

INSERT INTO `librarian` (`librarian_id`, `full_name`, `role`, `institutional_id`) VALUES
(1, 'Ms. Elizabeth Librarian', 'Head', 'LIB001'),
(2, 'Mr. Mark Assistant', 'Working Student', 'LIB002');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location_name`) VALUES
(1, 'skwelahan'),
(2, 'Annex Building'),
(3, 'Science Wing');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `student_id` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `grade` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL DEFAULT curtime()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `student_id`, `name`, `grade`, `section`, `date`, `time`) VALUES
(1, 'STU001', 'John Michael Santos', '10', 'A', '2026-02-02', '08:15:00'),
(2, 'STU002', 'Maria Cristina Reyes', '10', 'A', '2026-02-02', '08:20:00'),
(3, 'STU003', 'Carlos Antonio Cruz', '10', 'B', '2026-02-02', '08:25:00'),
(4, 'STU004', 'Ana Patricia Dela Cruz', '11', 'A', '2026-02-02', '09:00:00'),
(5, 'STU005', 'Roberto Jose Garcia', '11', 'B', '2026-02-02', '09:15:00'),
(6, 'STU006', 'Jennifer Lynn Tan', '12', 'A', '2026-02-02', '10:30:00'),
(7, 'STU007', 'Michael Angelo Torres', '10', 'C', '2026-02-01', '08:10:00'),
(8, 'STU008', 'Sarah Jane Martinez', '11', 'A', '2026-02-01', '08:30:00'),
(9, 'STU009', 'David Christopher Ong', '12', 'B', '2026-02-01', '09:45:00'),
(10, 'STU010', 'Michelle Ann Lim', '10', 'A', '2026-01-30', '08:00:00'),
(11, 'STU011', 'James Patrick Sy', '11', 'C', '2026-01-29', '09:00:00'),
(12, 'STU012', 'Christine Marie Villanueva', '12', 'A', '2026-01-28', '10:00:00'),
(13, 'STU013', 'Mark Anthony Ramos', '10', 'B', '2026-01-18', '08:20:00'),
(14, 'STU014', 'Grace Elizabeth Chua', '11', 'B', '2026-01-13', '09:30:00'),
(15, 'STU015', 'Paul Vincent Yu', '12', 'C', '2026-01-08', '10:15:00'),
(31, 'S100001', 'Juan Dela Cruz', '10', 'A', '2026-02-12', '10:11:35'),
(32, 'S100002', 'Maria Santos', '9', 'B', '2026-02-12', '09:00:00'),
(33, 'S100003', 'Pedro Reyes', '10', 'A', '2026-02-11', '09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `log_attendance`
--

CREATE TABLE `log_attendance` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `GradeSection` varchar(255) NOT NULL,
  `NoOfStudent` int(11) NOT NULL,
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_attendance`
--

INSERT INTO `log_attendance` (`ID`, `Name`, `GradeSection`, `NoOfStudent`, `Date`) VALUES
(1, 'Mr. Juan Dela Cruz', '10-A', 25, '2026-02-02'),
(2, 'Ms. Maria Santos', '11-B', 28, '2026-02-02'),
(3, 'Mr. Carlos Reyes', '12-A', 22, '2026-02-02'),
(4, 'Ms. Ana Garcia', '10-B', 30, '2026-02-01'),
(5, 'Mr. Roberto Tan', '11-A', 26, '2026-02-01'),
(6, 'Ms. Jennifer Lim', '10-C', 24, '2026-01-30'),
(7, 'Mr. Michael Torres', '11-C', 27, '2026-01-29'),
(8, 'Ms. Sarah Martinez', '12-B', 23, '2026-01-28'),
(9, 'Mr. David Ong', '10-A', 25, '2026-01-18'),
(10, 'Ms. Michelle Ann Lim', '11-B', 28, '2026-01-13'),
(11, 'Mr. James Patrick Sy', '12-C', 21, '2026-01-08'),
(12, 'Mr. Juan Dela Cruz', '10-A', 25, '2026-02-02'),
(13, 'Ms. Maria Santos', '11-B', 28, '2026-02-02'),
(14, 'Mr. Carlos Reyes', '12-A', 22, '2026-02-02'),
(15, 'Ms. Ana Garcia', '10-B', 30, '2026-02-01'),
(16, 'Mr. Roberto Tan', '11-A', 26, '2026-02-01'),
(17, 'Ms. Jennifer Lim', '10-C', 24, '2026-01-30'),
(18, 'Mr. Michael Torres', '11-C', 27, '2026-01-29'),
(19, 'Ms. Sarah Martinez', '12-B', 23, '2026-01-28'),
(20, 'Mr. David Ong', '10-A', 25, '2026-01-18'),
(21, 'Ms. Michelle Ann Lim', '11-B', 28, '2026-01-13'),
(22, 'Mr. James Patrick Sy', '12-C', 21, '2026-01-08'),
(23, 'Room 101', '10-A', 30, '2026-02-12');

-- --------------------------------------------------------

--
-- Table structure for table `log_book`
--

CREATE TABLE `log_book` (
  `ID` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `institutional_id` varchar(50) DEFAULT NULL,
  `grade_section` varchar(255) NOT NULL,
  `rfid_number` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `login_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_book`
--

INSERT INTO `log_book` (`ID`, `user_id`, `full_name`, `institutional_id`, `grade_section`, `rfid_number`, `email`, `user_type`, `login_at`) VALUES
(1, 1, 'John Michael Santos', 'STU001', '10-A', 'RFID001', 'john.santos@student.hcmi.edu', 'Student', '2026-02-02 08:00:00'),
(2, 2, 'Maria Cristina Reyes', 'STU002', '10-A', 'RFID002', 'maria.reyes@student.hcmi.edu', 'Student', '2026-02-02 08:15:00'),
(3, 3, 'Carlos Antonio Cruz', 'STU003', '10-B', 'RFID003', 'carlos.cruz@student.hcmi.edu', 'Student', '2026-02-02 08:30:00'),
(4, 4, 'Ana Patricia Dela Cruz', 'STU004', '11-A', 'RFID004', 'ana.delacruz@student.hcmi.edu', 'Student', '2026-02-02 09:00:00'),
(5, 5, 'Roberto Jose Garcia', 'STU005', '11-B', 'RFID005', 'roberto.garcia@student.hcmi.edu', 'Student', '2026-02-02 09:30:00'),
(6, 1, 'John Michael Santos', 'STU001', '10-A', 'RFID001', 'john.santos@student.hcmi.edu', 'Student', '2026-02-01 08:00:00'),
(7, 2, 'Maria Cristina Reyes', 'STU002', '10-A', 'RFID002', 'maria.reyes@student.hcmi.edu', 'Student', '2026-02-01 08:20:00'),
(8, 3, 'Carlos Antonio Cruz', 'STU003', '10-B', 'RFID003', 'carlos.cruz@student.hcmi.edu', 'Student', '2026-01-30 08:00:00'),
(9, 4, 'Ana Patricia Dela Cruz', 'STU004', '11-A', 'RFID004', 'ana.delacruz@student.hcmi.edu', 'Student', '2026-01-29 09:00:00'),
(10, 5, 'Roberto Jose Garcia', 'STU005', '11-B', 'RFID005', 'roberto.garcia@student.hcmi.edu', 'Student', '2026-01-28 09:30:00'),
(11, 1, 'John Michael Santos', 'STU001', '10-A', 'RFID001', 'john.santos@student.hcmi.edu', 'Student', '2026-01-18 08:00:00'),
(12, 2, 'Maria Cristina Reyes', 'STU002', '10-A', 'RFID002', 'maria.reyes@student.hcmi.edu', 'Student', '2026-01-13 08:15:00'),
(13, 1, 'John Michael Santos', 'STU001', '10-A', 'RFID001', 'john.santos@student.hcmi.edu', 'Student', '2026-02-02 08:00:00'),
(14, 2, 'Maria Cristina Reyes', 'STU002', '10-A', 'RFID002', 'maria.reyes@student.hcmi.edu', 'Student', '2026-02-02 08:15:00'),
(15, 3, 'Carlos Antonio Cruz', 'STU003', '10-B', 'RFID003', 'carlos.cruz@student.hcmi.edu', 'Student', '2026-02-02 08:30:00'),
(16, 4, 'Ana Patricia Dela Cruz', 'STU004', '11-A', 'RFID004', 'ana.delacruz@student.hcmi.edu', 'Student', '2026-02-02 09:00:00'),
(17, 5, 'Roberto Jose Garcia', 'STU005', '11-B', 'RFID005', 'roberto.garcia@student.hcmi.edu', 'Student', '2026-02-02 09:30:00'),
(18, 1, 'John Michael Santos', 'STU001', '10-A', 'RFID001', 'john.santos@student.hcmi.edu', 'Student', '2026-02-01 08:00:00'),
(19, 2, 'Maria Cristina Reyes', 'STU002', '10-A', 'RFID002', 'maria.reyes@student.hcmi.edu', 'Student', '2026-02-01 08:20:00'),
(20, 3, 'Carlos Antonio Cruz', 'STU003', '10-B', 'RFID003', 'carlos.cruz@student.hcmi.edu', 'Student', '2026-01-30 08:00:00'),
(21, 4, 'Ana Patricia Dela Cruz', 'STU004', '11-A', 'RFID004', 'ana.delacruz@student.hcmi.edu', 'Student', '2026-01-29 09:00:00'),
(22, 5, 'Roberto Jose Garcia', 'STU005', '11-B', 'RFID005', 'roberto.garcia@student.hcmi.edu', 'Student', '2026-01-28 09:30:00'),
(23, 1, 'John Michael Santos', 'STU001', '10-A', 'RFID001', 'john.santos@student.hcmi.edu', 'Student', '2026-01-18 08:00:00'),
(24, 2, 'Maria Cristina Reyes', 'STU002', '10-A', 'RFID002', 'maria.reyes@student.hcmi.edu', 'Student', '2026-01-13 08:15:00'),
(25, 11, 'Dashboard Test User', 'STU-DASH-001', '10-A', 'RFID-DASH', NULL, NULL, '2026-02-12 10:11:35'),
(26, 12, 'Raymart Dave Silvosa', '2026-00110', '12 - A-Rizal', '2026-00110', '', 'Student', '2026-02-18 20:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `msms_admins`
--

CREATE TABLE `msms_admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('Super Admin','Admin') DEFAULT 'Admin',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `msms_admins`
--

INSERT INTO `msms_admins` (`admin_id`, `username`, `password`, `full_name`, `email`, `role`, `status`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$OrxtSvL4ZysJf1VvFnA8oeyhYNhyChk0EO97t.f5Oqg0JXsPrM2cu', 'System Administrator', 'admin@hcmi.com', 'Super Admin', 'Active', '2026-02-01 08:04:25', '2026-02-18 12:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `usage_date` datetime NOT NULL,
  `grade_section` varchar(100) NOT NULL,
  `student_count` int(11) NOT NULL,
  `reference_no` varchar(200) NOT NULL,
  `booked_by` varchar(255) NOT NULL,
  `noted_by` varchar(255) NOT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `activity`, `usage_date`, `grade_section`, `student_count`, `reference_no`, `booked_by`, `noted_by`, `status`, `created_at`) VALUES
(1, 'Chemistry Experiment - Acids and Bases', '2026-02-02 10:00:00', '11-A', 25, 'REF-2026-001', 'Ms. Maria Santos', 'Dr. Juan Dela Cruz', 'approved', '2026-01-31 01:14:05'),
(2, 'Biology Lab - Microscope Usage', '2026-02-02 14:00:00', '10-B', 30, 'REF-2026-002', 'Mr. Carlos Reyes', 'Dr. Juan Dela Cruz', 'approved', '2026-02-01 01:14:05'),
(3, 'Physics Experiment - Motion', '2026-02-03 09:00:00', '12-A', 28, 'REF-2026-003', 'Ms. Ana Garcia', 'admin', 'approved', '2026-02-02 01:14:05'),
(4, 'Chemistry Lab - Titration', '2026-02-04 13:00:00', '11-B', 22, 'REF-2026-004', 'Mr. Roberto Tan', 'admin', 'approved', '2026-02-02 01:14:05'),
(5, 'Biology Dissection', '2026-01-30 09:14:05', '10-A', 25, 'REF-2026-005', 'Ms. Jennifer Lim', 'Dr. Juan Dela Cruz', 'approved', '2026-01-28 01:14:05'),
(6, 'Chemistry Lab - Organic Compounds', '2026-01-28 09:14:05', '11-A', 30, 'REF-2026-006', 'Mr. Michael Torres', 'Dr. Juan Dela Cruz', 'approved', '2026-01-26 01:14:05'),
(7, 'Physics Lab - Electricity', '2026-01-18 09:14:05', '12-B', 20, 'REF-2026-007', 'Ms. Sarah Martinez', 'Dr. Juan Dela Cruz', 'approved', '2026-01-16 01:14:05'),
(8, 'Biology Lab - Cell Structure', '2026-01-13 09:14:05', '10-C', 28, 'REF-2026-008', 'Mr. David Ong', 'Dr. Juan Dela Cruz', 'approved', '2026-01-11 01:14:05'),
(9, 'Chemistry Experiment - Acids and Bases', '2026-02-02 10:00:00', '11-A', 25, 'REF-2026-001', 'Ms. Maria Santos', 'Dr. Juan Dela Cruz', 'approved', '2026-01-31 01:14:14'),
(10, 'Biology Lab - Microscope Usage', '2026-02-02 14:00:00', '10-B', 30, 'REF-2026-002', 'Mr. Carlos Reyes', 'Dr. Juan Dela Cruz', 'approved', '2026-02-01 01:14:14'),
(11, 'Physics Experiment - Motion', '2026-02-03 09:00:00', '12-A', 28, 'REF-2026-003', 'Ms. Ana Garcia', 'admin', 'approved', '2026-02-02 01:14:14'),
(12, 'Chemistry Lab - Titration', '2026-02-04 13:00:00', '11-B', 22, 'REF-2026-004', 'Mr. Roberto Tan', 'admin', 'approved', '2026-02-02 01:14:14'),
(13, 'Biology Dissection', '2026-01-30 09:14:14', '10-A', 25, 'REF-2026-005', 'Ms. Jennifer Lim', 'Dr. Juan Dela Cruz', 'approved', '2026-01-28 01:14:14'),
(14, 'Chemistry Lab - Organic Compounds', '2026-01-28 09:14:14', '11-A', 30, 'REF-2026-006', 'Mr. Michael Torres', 'Dr. Juan Dela Cruz', 'approved', '2026-01-26 01:14:14'),
(15, 'Physics Lab - Electricity', '2026-01-18 09:14:14', '12-B', 20, 'REF-2026-007', 'Ms. Sarah Martinez', 'Dr. Juan Dela Cruz', 'approved', '2026-01-16 01:14:14'),
(16, 'Biology Lab - Cell Structure', '2026-01-13 09:14:14', '10-C', 28, 'REF-2026-008', 'Mr. David Ong', 'Dr. Juan Dela Cruz', 'approved', '2026-01-11 01:14:14'),
(17, 'Chemistry Lab', '2026-02-13 10:11:35', '10-A', 25, 'REF-001', 'Mr. Smith', 'admin', 'approved', '2026-02-12 10:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_items`
--

CREATE TABLE `reservation_items` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(100) NOT NULL,
  `rfid_number` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `grade` int(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `account_status` varchar(20) NOT NULL DEFAULT 'approved',
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `rfid_number`, `name`, `section`, `grade`, `password`, `account_status`, `email`) VALUES
(1, '2026-00110', NULL, 'Raymart Dave Silvosa', 'A-Rizal', 12, '$2y$10$pekwTym3JrP1sW.m5sK/HOZy0/0WCUxiFrPgRSb7EElwVc2tpv6Lm', 'approved', NULL),
(2, '2026-00111', NULL, 'Shaina Palmera', 'A-Rizal', 12, '$2y$10$GuyJ92TkrSwcqXkmcyfMf.adEhzrhzMVPLBLM5C0xXVAMEuX.CPci', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students_records`
--

CREATE TABLE `students_records` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `grade_section` varchar(100) NOT NULL,
  `rfid_number` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `institutional_id` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `user_type` varchar(100) DEFAULT NULL,
  `grade_section` varchar(255) NOT NULL,
  `rfid_number` varchar(255) NOT NULL,
  `history` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT 'Active',
  `balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `full_name`, `institutional_id`, `email`, `contact_number`, `user_type`, `grade_section`, `rfid_number`, `history`, `password`, `status`, `balance`, `created_at`) VALUES
(1, 'John Michael Santos', 'STU001', 'john.santos@student.hcmi.edu', '09123456789', 'Student', '10-A', 'RFID001', 'Active student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active', 110.00, '2026-02-17 12:00:17'),
(2, 'Maria Cristina Reyes', 'STU002', 'maria.reyes@student.hcmi.edu', '09123456790', 'Student', '10-A', 'RFID002', 'Active student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active', 10.00, '2026-02-17 12:03:44'),
(3, 'Carlos Antonio Cruz', 'STU003', 'carlos.cruz@student.hcmi.edu', '09123456791', 'Student', '10-B', 'RFID003', 'Active student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active', 0.00, '2026-02-02 01:14:05'),
(4, 'Ana Patricia Dela Cruz', 'STU004', 'ana.delacruz@student.hcmi.edu', '09123456792', 'Student', '11-A', 'RFID004', 'Active student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active', 0.00, '2026-02-02 01:14:05'),
(5, 'Roberto Jose Garcia', 'STU005', 'roberto.garcia@student.hcmi.edu', '09123456793', 'Student', '11-B', 'RFID005', 'Active student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active', 0.00, '2026-02-02 01:14:05'),
(11, 'Dashboard Test User', 'STU-DASH-001', NULL, NULL, NULL, '10-A', 'RFID-DASH', '[]', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Active', 0.00, '2026-02-12 10:11:35'),
(12, 'Raymart Dave Silvosa [2026-00110]', '2026-00110', '', NULL, 'Student', '12 - A-Rizal', '2026-00110', '', '$2y$10$YePXhG3QXlikjsPV9atCF.bkgurPu3IoNwuXlW3rMbFF.LpbtcN9C', 'Active', 0.00, '2026-02-18 12:19:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `avr_borrowed`
--
ALTER TABLE `avr_borrowed`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_item` (`Item`),
  ADD KEY `idx_status` (`Status`),
  ADD KEY `idx_date_borrowed` (`DateBorrowed`),
  ADD KEY `idx_due_date` (`DueDate`);

--
-- Indexes for table `avr_inventory`
--
ALTER TABLE `avr_inventory`
  ADD PRIMARY KEY (`ItemID`),
  ADD KEY `idx_item` (`Item`),
  ADD KEY `idx_date_received` (`DateReceived`);

--
-- Indexes for table `avr_reservation`
--
ALTER TABLE `avr_reservation`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_department` (`Department`),
  ADD KEY `idx_date` (`Date`),
  ADD KEY `idx_name` (`Name`);

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `borrowing_transaction`
--
ALTER TABLE `borrowing_transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `librarian_id` (`librarian_id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `clinic_log`
--
ALTER TABLE `clinic_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clinic_records`
--
ALTER TABLE `clinic_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `counters`
--
ALTER TABLE `counters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `current_customer_id` (`current_customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `queue_number` (`queue_number`);

--
-- Indexes for table `display_settings`
--
ALTER TABLE `display_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ict_categories`
--
ALTER TABLE `ict_categories`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `ict_inventory`
--
ALTER TABLE `ict_inventory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `ict_locations`
--
ALTER TABLE `ict_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `ict_logs`
--
ALTER TABLE `ict_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ict_users`
--
ALTER TABLE `ict_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`lab_id`);

--
-- Indexes for table `librarian`
--
ALTER TABLE `librarian`
  ADD PRIMARY KEY (`librarian_id`),
  ADD UNIQUE KEY `institutional_id` (`institutional_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD UNIQUE KEY `unique_student_day` (`student_id`,`date`);

--
-- Indexes for table `log_attendance`
--
ALTER TABLE `log_attendance`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_name` (`Name`),
  ADD KEY `idx_date` (`Date`),
  ADD KEY `idx_grade_section` (`GradeSection`);

--
-- Indexes for table `log_book`
--
ALTER TABLE `log_book`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `msms_admins`
--
ALTER TABLE `msms_admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservation_items`
--
ALTER TABLE `reservation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students_records`
--
ALTER TABLE `students_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `institutional_id` (`full_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `avr_borrowed`
--
ALTER TABLE `avr_borrowed`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `avr_inventory`
--
ALTER TABLE `avr_inventory`
  MODIFY `ItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `avr_reservation`
--
ALTER TABLE `avr_reservation`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `borrowing_transaction`
--
ALTER TABLE `borrowing_transaction`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clinic_log`
--
ALTER TABLE `clinic_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `clinic_records`
--
ALTER TABLE `clinic_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `counters`
--
ALTER TABLE `counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `display_settings`
--
ALTER TABLE `display_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ict_categories`
--
ALTER TABLE `ict_categories`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ict_inventory`
--
ALTER TABLE `ict_inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ict_locations`
--
ALTER TABLE `ict_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ict_logs`
--
ALTER TABLE `ict_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ict_users`
--
ALTER TABLE `ict_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `lab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `librarian`
--
ALTER TABLE `librarian`
  MODIFY `librarian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `log_attendance`
--
ALTER TABLE `log_attendance`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `log_book`
--
ALTER TABLE `log_book`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `msms_admins`
--
ALTER TABLE `msms_admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reservation_items`
--
ALTER TABLE `reservation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students_records`
--
ALTER TABLE `students_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowing_transaction`
--
ALTER TABLE `borrowing_transaction`
  ADD CONSTRAINT `borrowing_transaction_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowing_transaction_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowing_transaction_ibfk_3` FOREIGN KEY (`librarian_id`) REFERENCES `librarian` (`librarian_id`) ON DELETE SET NULL;

--
-- Constraints for table `counters`
--
ALTER TABLE `counters`
  ADD CONSTRAINT `counters_ibfk_1` FOREIGN KEY (`current_customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ict_inventory`
--
ALTER TABLE `ict_inventory`
  ADD CONSTRAINT `ict_inventory_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `ict_categories` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ict_inventory_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `ict_locations` (`location_id`) ON UPDATE CASCADE;

--
-- Constraints for table `ict_logs`
--
ALTER TABLE `ict_logs`
  ADD CONSTRAINT `ict_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `ict_users` (`id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`lab_id`),
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON UPDATE CASCADE;

--
-- Constraints for table `log_book`
--
ALTER TABLE `log_book`
  ADD CONSTRAINT `log_book_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `reservation_items`
--
ALTER TABLE `reservation_items`
  ADD CONSTRAINT `reservation_items_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
