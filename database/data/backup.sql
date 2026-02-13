-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 13, 2026 at 01:07 AM
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
-- Database: `tripBookingPOS`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `accountID` int(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `permission` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `account`
--

TRUNCATE TABLE `account`;
--
-- Dumping data for table `account`
--

INSERT INTO `account` (`accountID`, `email`, `password`, `permission`) VALUES
(1, 'admin@email.com', 'admin123', 'admin'),
(2, 'michael.s@gmail.com', 'pass123', 'user'),
(3, 'sarah.c@yahoo.com', 'pass123', 'user'),
(4, 'kenji.t@outlook.com', 'pass123', 'user'),
(5, 'somchai.b@me.com', 'pass123', 'user'),
(6, 'lisa.m@gmail.com', 'pass123', 'user'),
(7, 'sunsarun@email.com', '123123', 'admin'),
(8, 'user@email.com', '123123', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `addressID` int(20) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `houseNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `address`
--

TRUNCATE TABLE `address`;
--
-- Dumping data for table `address`
--

INSERT INTO `address` (`addressID`, `country`, `province`, `district`, `street`, `houseNumber`) VALUES
(1, 'Bangkok royal heritage walk.', 'Bangkok', 'Pathum Wan', 'Rama I Rd', '991'),
(2, 'Ultra-luxury pavilion.', 'Phuket', 'Kathu', 'Patong Beach Rd', '45/1'),
(3, 'Colonial style luxury.', 'Hanoi', 'Hoan Kiem', 'Hang Bac', '22'),
(4, 'SkyPark access included.', 'Singapore', 'Downtown', 'Marina Blvd', '10'),
(5, 'Chauffeur driven Alphard.', 'Kuala Lumpur', 'Bukit Bintang', 'Jalan Ampang', '145'),
(6, '', 'Bali', 'Ubud', 'Jl. Raya Ubud', '8'),
(7, 'Early morning guided tour.', 'Siem Reap', 'Angkor', 'Charles de Gaulle', '17'),
(8, 'Exclusive lagoon tour.', 'Palawan', 'El Nido', 'Serena St', '5'),
(9, '', 'Chiang Mai', 'Mueang', 'Nimmanhemin', '12'),
(10, 'Historical architecture tour.', 'Ho Chi Minh', 'District 1', 'Dong Khoi', '151');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `bookingID` int(20) NOT NULL,
  `accountID` int(20) NOT NULL,
  `spotID` int(20) NOT NULL,
  `purchaseDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `unit` int(20) DEFAULT 1,
  `paymentID` int(20) NOT NULL,
  `checkinDate` date DEFAULT NULL,
  `checkoutDate` date DEFAULT NULL,
  `totalPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `booking`
--

TRUNCATE TABLE `booking`;
--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`bookingID`, `accountID`, `spotID`, `purchaseDate`, `unit`, `paymentID`, `checkinDate`, `checkoutDate`, `totalPrice`) VALUES
(1, 2, 2, '2026-02-02 19:11:14', 1, 1, '2024-12-01', '2024-12-03', 1500.00),
(2, 3, 4, '2026-02-02 19:11:14', 1, 2, '2024-11-15', '2024-11-16', 1000.00),
(3, 4, 7, '2026-02-02 19:11:14', 2, 3, '2024-10-10', '2024-10-10', 90.00),
(4, 5, 1, '2026-02-02 19:11:14', 4, 4, '2024-09-01', '2024-09-01', 220.00),
(5, 6, 6, '2026-02-02 19:11:14', 1, 5, '2024-08-20', '2024-08-25', 1750.00);

-- --------------------------------------------------------

--
-- Table structure for table `paymentInfo`
--

CREATE TABLE `paymentInfo` (
  `paymentID` int(20) NOT NULL,
  `userID` int(20) DEFAULT NULL,
  `paymentType` varchar(20) DEFAULT NULL,
  `cardCode` varchar(50) DEFAULT NULL,
  `expireDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `paymentInfo`
--

TRUNCATE TABLE `paymentInfo`;
--
-- Dumping data for table `paymentInfo`
--

INSERT INTO `paymentInfo` (`paymentID`, `userID`, `paymentType`, `cardCode`, `expireDate`) VALUES
(1, 2, 'Visa', '4532-7812-9044-1238', '2026-12-31'),
(2, 3, 'MasterCard', '5412-8821-0033-5566', '2025-08-15'),
(3, 4, 'JCB', '3568-1234-5678-9012', '2027-01-20'),
(4, 5, 'Visa', '4916-0000-1111-2222', '2026-05-10'),
(5, 6, 'MasterCard', '5105-1021-9938-1212', '2029-12-31');

-- --------------------------------------------------------

--
-- Table structure for table `spot`
--

CREATE TABLE `spot` (
  `spotID` int(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `addressID` int(20) NOT NULL,
  `detail` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `photo` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `spot`
--

TRUNCATE TABLE `spot`;
--
-- Dumping data for table `spot`
--

INSERT INTO `spot` (`spotID`, `name`, `type`, `status`, `phone`, `addressID`, `detail`, `price`, `discount`, `photo`) VALUES
(1, 'Grand Palace Tour', 'Travel-Packages', 'Available', '+6622221088', 1, NULL, 60.00, 5.00, '1770061598_6980ff1eea7c6.jpg'),
(2, 'Amanpuri Suites', 'Luxury-Hotels', 'Available', '+6676324433', 2, NULL, 1500.00, 0.00, '1770061512_6980fec85f2c4.jpg'),
(3, 'Hanoi French Suite', 'Luxury-Hotels', 'Available', '+8424382500', 3, NULL, 300.00, 40.00, '1770061457_6980fe9183f4b.jpg'),
(4, 'Marina Bay Sands', 'Luxury-Hotels', 'Available', '+6566888888', 4, NULL, 1100.00, 100.00, '1770061419_6980fe6b4b15b.webp'),
(5, 'KL Executive Van', 'Vehicle-Rental', 'Available', '+603238888', 5, NULL, 250.00, 0.00, '1770061383_6980fe478306c.webp'),
(6, 'Ubud Jungle Villa', 'Room-Rentals', 'Available', '+623619754', 6, NULL, 400.00, 50.00, '1770061282_6980fde2add0a.jpg'),
(7, 'Angkor Wat Sunrise', 'Travel-Packages', 'Available', '+855639634', 7, NULL, 45.00, 0.00, '1770061086_6980fd1e026e9.webp'),
(8, 'El Nido Boat Tour', 'Vehicle-Rental', 'Available', '+639178881', 8, NULL, 120.00, 10.00, '1770061047_6980fcf7380b5.webp'),
(9, 'Nimman Studio', 'Room-Rentals', 'Available', '+665322446', 9, NULL, 45.00, 0.00, '1770061012_6980fcd404f5d.jpg'),
(10, 'Saigon City Walk', 'Travel-Packages', 'Available', '+84283824', 10, NULL, 30.00, 5.00, '1770061003_6980fccbb4755.webp');

-- --------------------------------------------------------

--
-- Table structure for table `userinfo`
--

CREATE TABLE `userinfo` (
  `userID` int(20) NOT NULL,
  `accountID` int(20) DEFAULT NULL,
  `FName` varchar(50) DEFAULT NULL,
  `LName` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile` varchar(200) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `userinfo`
--

TRUNCATE TABLE `userinfo`;
--
-- Dumping data for table `userinfo`
--

INSERT INTO `userinfo` (`userID`, `accountID`, `FName`, `LName`, `gender`, `DOB`, `phone`, `createdDate`, `profile`, `address`) VALUES
(1, 1, 'System', 'Admin', NULL, NULL, '+662000000', '2026-02-02 19:11:14', NULL, 'HQ Bangkok'),
(2, 2, 'Michael', 'Scott', NULL, NULL, '+14155551234', '2026-02-02 19:11:14', NULL, 'Scranton, USA'),
(3, 3, 'Sarah', 'Connor', NULL, NULL, '+44207123456', '2026-02-02 19:11:14', NULL, 'London, UK'),
(4, 4, 'Kenji', 'Tanaka', NULL, NULL, '+8190123456', '2026-02-02 19:11:14', NULL, 'Osaka, JP'),
(5, 5, 'Somchai', 'Boonmee', NULL, NULL, '+6681234567', '2026-02-02 19:11:14', NULL, 'Bangkok, TH'),
(6, 6, 'Lisa', 'Manoban', NULL, NULL, '+6689888777', '2026-02-02 19:11:14', NULL, 'Buriram, TH'),
(7, 7, 'Sun', 'Sarun', 'Male', '2025-08-04', '0123123123', '2026-02-01 17:00:00', '1770059595_face_20260203_002403_2.jpg', 'Phnom Penh'),
(8, 8, 'Sun', 'Sarun', 'Male', '2025-09-01', '0123123123', '2026-02-01 17:00:00', '1770061735_face_20260203_002429_12.jpg', 'Phnom Penh');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`accountID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`addressID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`bookingID`),
  ADD KEY `fk_booking_account` (`accountID`),
  ADD KEY `fk_booking_spot` (`spotID`),
  ADD KEY `fk_booking_payment` (`paymentID`);

--
-- Indexes for table `paymentInfo`
--
ALTER TABLE `paymentInfo`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `fk_payment_user` (`userID`);

--
-- Indexes for table `spot`
--
ALTER TABLE `spot`
  ADD PRIMARY KEY (`spotID`),
  ADD KEY `fk_spot_address` (`addressID`);

--
-- Indexes for table `userinfo`
--
ALTER TABLE `userinfo`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `accountID` (`accountID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `accountID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `addressID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `bookingID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `paymentInfo`
--
ALTER TABLE `paymentInfo`
  MODIFY `paymentID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `spot`
--
ALTER TABLE `spot`
  MODIFY `spotID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `userinfo`
--
ALTER TABLE `userinfo`
  MODIFY `userID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_booking_account` FOREIGN KEY (`accountID`) REFERENCES `account` (`accountID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_payment` FOREIGN KEY (`paymentID`) REFERENCES `paymentInfo` (`paymentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_spot` FOREIGN KEY (`spotID`) REFERENCES `spot` (`spotID`) ON DELETE CASCADE;

--
-- Constraints for table `paymentInfo`
--
ALTER TABLE `paymentInfo`
  ADD CONSTRAINT `fk_payment_user` FOREIGN KEY (`userID`) REFERENCES `userinfo` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `spot`
--
ALTER TABLE `spot`
  ADD CONSTRAINT `fk_spot_address` FOREIGN KEY (`addressID`) REFERENCES `address` (`addressID`) ON DELETE CASCADE;

--
-- Constraints for table `userinfo`
--
ALTER TABLE `userinfo`
  ADD CONSTRAINT `fk_user_account` FOREIGN KEY (`accountID`) REFERENCES `account` (`accountID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
