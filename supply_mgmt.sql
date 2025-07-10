-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2025 at 06:56 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `supply_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `invoice` varchar(255) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `orderDate` datetime DEFAULT NULL,
  `date_requested` datetime DEFAULT NULL,
  `date_required` datetime DEFAULT NULL,
  `outlet` varchar(255) DEFAULT NULL,
  `admin_acknowledged` tinyint(1) DEFAULT NULL,
  `admin_acknowledged_at` datetime DEFAULT NULL,
  `employee_acknowledged` tinyint(1) DEFAULT NULL,
  `employee_acknowledged_at` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date_prepared` datetime DEFAULT NULL,
  `date_delivered` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `invoice`, `userID`, `orderDate`, `date_requested`, `date_required`, `outlet`, `admin_acknowledged`, `admin_acknowledged_at`, `employee_acknowledged`, `employee_acknowledged_at`, `status`, `date_prepared`, `date_delivered`) VALUES
(1, '', 2, '2025-05-01 04:14:02', NULL, NULL, NULL, 1, '2025-05-01 10:14:28', 1, '2025-05-01 10:14:57', '', NULL, NULL),
(2, '250508-001', 2, '2025-05-08 05:42:35', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(3, '250508-002', 2, '2025-05-08 05:46:38', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(4, '250508-003', 2, '2025-05-08 05:54:15', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(5, '250508-004', 2, '2025-05-08 05:54:54', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(6, '250508-005', 2, '2025-05-08 05:55:52', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(7, '250508-006', 2, '2025-05-08 06:03:05', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(8, '250512-001', 2, '2025-05-12 09:52:38', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(9, '250512-002', 2, '2025-05-12 10:05:34', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(10, '250402-001', 4, '2025-04-02 12:00:00', NULL, NULL, NULL, 1, '2025-04-02 14:00:00', 1, '2025-04-03 12:00:00', NULL, '2025-04-03 12:00:00', '2025-04-03 12:00:00'),
(11, '250609-001', 4, '2025-06-09 13:53:12', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(12, '250609-002', 2, '2025-06-09 13:53:58', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(13, '250609-003', 2, '2025-06-09 14:04:51', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(14, '250609-004', 2, '2025-06-09 14:04:58', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(15, '250609-005', 2, '2025-06-09 14:19:04', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(16, '250609-006', 2, '2025-06-09 14:19:11', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(17, '250609-007', 2, '2025-06-09 14:37:27', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(18, '250609-008', 2, '2025-06-09 15:19:39', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(19, '250610-001', 2, '2025-06-10 13:46:17', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(20, '250610-002', 2, '2025-06-10 13:46:20', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(21, '250610-003', 2, '2025-06-10 17:20:43', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(22, '250611-001', 2, '2025-06-11 09:32:25', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(23, '250611-002', 2, '2025-06-11 09:37:26', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(24, '250611-003', 2, '2025-06-11 13:52:11', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(25, '250611-004', 2, '2025-06-11 13:52:13', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(26, '250611-005', 2, '2025-06-11 13:52:20', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(27, '250611-006', 2, '2025-06-11 13:52:24', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(28, '250611-007', 2, '2025-06-11 14:58:56', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(29, '250611-008', 2, '2025-06-11 15:00:20', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(30, '250612-001', 2, '2025-06-12 09:52:27', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(31, '250612-002', 2, '2025-06-12 09:54:04', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(32, '250612-003', 2, '2025-06-12 13:44:39', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(33, '250707-001', 2, '2025-07-07 10:21:01', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(34, '250707-002', 2, '2025-07-07 10:37:31', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(35, '250707-003', 2, '2025-07-07 10:42:15', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(36, '250707-004', 2, '2025-07-07 10:43:59', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(37, '250707-005', 2, '2025-07-07 10:44:02', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(38, '250707-006', 2, '2025-07-07 10:45:30', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(39, '250707-007', 2, '2025-07-07 10:46:00', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(40, '250707-008', 2, '2025-07-07 10:46:05', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(41, '250707-009', 2, '2025-07-07 10:46:09', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(42, '250707-010', 2, '2025-07-07 10:54:43', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(43, '250707-011', 2, '2025-07-07 10:57:35', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(44, '250707-012', 2, '2025-07-07 10:57:50', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(45, '250707-013', 2, '2025-07-07 11:16:56', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(46, '250707-014', 2, '2025-07-07 11:24:09', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(47, '250707-015', 2, '2025-07-07 11:24:10', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(48, '250707-016', 2, '2025-07-07 11:24:11', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(49, '250707-017', 2, '2025-07-07 11:24:40', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(50, '250707-018', 2, '2025-07-07 11:26:32', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(51, '250707-019', 2, '2025-07-07 11:29:49', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(52, '250707-020', 2, '2025-07-07 11:31:19', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(53, '250707-021', 2, '2025-07-07 11:43:32', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(54, '250707-022', 2, '2025-07-07 11:58:03', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(55, '250707-023', 2, '2025-07-07 15:52:27', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(56, '250707-024', 2, '2025-07-07 15:52:29', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(57, '250707-025', 2, '2025-07-07 15:52:40', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(58, '250707-026', 2, '2025-07-07 15:52:49', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(59, '250707-027', 2, '2025-07-07 16:36:02', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(60, '250707-028', 2, '2025-07-07 16:36:51', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(61, '250707-029', 2, '2025-07-07 17:19:50', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(62, '250708-001', 2, '2025-07-08 11:32:12', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(63, '250708-002', 2, '2025-07-08 11:34:41', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(64, '250708-003', 2, '2025-07-08 14:56:30', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(65, '250708-004', 2, '2025-07-08 14:56:38', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(66, '250708-005', 2, '2025-07-08 14:56:48', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(67, '250708-006', 2, '2025-07-08 14:59:06', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(68, '250708-007', 2, '2025-07-08 15:11:36', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(69, '250708-008', 2, '2025-07-08 15:49:05', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL),
(70, '250710-001', 2, '2025-07-10 13:46:24', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders_items`
--

CREATE TABLE `orders_items` (
  `itemID` int(11) NOT NULL,
  `orderID` int(11) DEFAULT NULL,
  `supply_ID` int(11) DEFAULT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `UOM` varchar(255) NOT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `req_qty` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_items`
--

INSERT INTO `orders_items` (`itemID`, `orderID`, `supply_ID`, `quantity`, `UOM`, `remark`, `req_qty`) VALUES
(1, 1, 0, '5KG', '', NULL, NULL),
(2, 2, 0, '50', '', NULL, NULL),
(3, 2, 0, '100', '', NULL, NULL),
(4, 2, 0, '200', '', NULL, NULL),
(5, 3, 0, '50', '', NULL, NULL),
(6, 3, 0, '100', '', NULL, NULL),
(7, 3, 0, '200', '', NULL, NULL),
(8, 4, 0, '50', '', NULL, NULL),
(9, 4, 0, '100', '', NULL, NULL),
(10, 4, 0, '200', '', NULL, NULL),
(11, 5, 0, '50', '', NULL, NULL),
(12, 5, 0, '100', '', NULL, NULL),
(13, 5, 0, '200', '', NULL, NULL),
(14, 6, 0, '50', '', NULL, NULL),
(15, 6, 0, '100', '', NULL, NULL),
(16, 6, 0, '200', '', NULL, NULL),
(17, 7, 0, '50', '', NULL, NULL),
(18, 7, 0, '100', '', NULL, NULL),
(19, 7, 0, '200', '', NULL, NULL),
(20, 8, 0, '50', '', NULL, NULL),
(21, 8, 0, '100', '', NULL, NULL),
(22, 8, 0, '200', '', NULL, NULL),
(23, 9, 0, '50', '', NULL, NULL),
(24, 9, 0, '100', '', NULL, NULL),
(25, 9, 0, '200', '', NULL, NULL),
(26, 70, 18, '1', 'CTN', NULL, 24000);

-- --------------------------------------------------------

--
-- Table structure for table `supplies`
--

CREATE TABLE `supplies` (
  `supply_ID` int(11) NOT NULL,
  `SKU` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `warehouse` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplies`
--

INSERT INTO `supplies` (`supply_ID`, `SKU`, `item_name`, `warehouse`) VALUES
(1, 'ZUS-AMDI-0006A', 'CATCHER GOURMET CHOCOLATE SAUCE', 'LAMBAK'),
(3, 'ZUS-AMDI-0007', 'BROWN SUGAR SACHET', 'LAMBAK'),
(4, 'ZUS-AMDI-0008A', 'COFFEE JELLY', 'LAMBAK'),
(5, 'ZUS-AMDI-0009A', 'CATCHER SALTED CARAMEL SAUCE', 'LAMBAK'),
(6, 'ZUS-AMDI-0012A', 'ZUS MINERAL WATER', 'LAMBAK'),
(7, 'ZBN-AMDI-0018A', 'MOSA CREAM CHARGER', 'BERIBI'),
(8, 'ZBN-AMDI-0023A', 'MILKLAB ALMOND MILK', 'BERIBI'),
(9, 'ZBN-AMDI-0051A', 'GOLD COIN KOPI & TEH TARIK SWEETENED CREAMER', 'BERIBI'),
(10, 'ZBN-AMDI-0065A', 'GOLD COIN EVAPORATED CREAMER', 'BERIBI'),
(11, 'ZBN-AMDI-0066A', 'YARRA FARM MASTER BARISTA MILK', 'LAMBAK'),
(12, 'ZUS-AMDI-0056A', 'FREENOW BARISTA COCONUT MILK', 'LAMBAK'),
(13, 'ZUS-PACK-0136A', 'ZUS NAPKIN', 'LAMBAK'),
(14, 'ZUS-PACK-0035A', 'ICE ZUS CUP 16OZ (B1000F20)', 'LAMBAK'),
(15, 'ZBN-AMDI-0068A', 'SCHWEPPES SODA WATER', 'BERIBI'),
(16, 'ZUS-PACK-0045A', 'STRAWLESS LID - CFC', 'LAMBAK'),
(17, 'ZBN-FRDI-0003A', 'EVER-WHIP V WHIPPING CREAM', 'BERIBI'),
(18, 'ZUS-PACK-0010A', '1L PLAIN PET BOTTLE 28MM', 'LAMBAK'),
(19, 'ZUS-PACK-0149A', 'ZUS PLM STRAW', 'LAMBAK'),
(20, 'ZUS-PACK-0113A', '4 CUP PAPER BAG ZUS LOGO', 'LAMBAK'),
(21, 'ZUS-PACK-0146A', 'ZUS SLEEVE (WHITE) - CFC', 'LAMBAK');

-- --------------------------------------------------------

--
-- Table structure for table `supply_mgmt`
--

CREATE TABLE `supply_mgmt` (
  `userID` int(255) NOT NULL,
  `userFullName` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `userpass` varchar(255) NOT NULL,
  `useremail` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply_mgmt`
--

INSERT INTO `supply_mgmt` (`userID`, `userFullName`, `username`, `userpass`, `useremail`, `role`, `branch`) VALUES
(1, 'Zus Supply Chain Employee, 'ZusSCEmployee1', '12345', 'example123@gmail.com', 'supply chain employee', 'Setia Point'),
(2, 'Zus Employee 1', 'ZusEmployee1', '12345', '', 'employee', 'The Curve'),
(3, 'Zus Super Admin', 'SuperAdmin', 'Admin1234', '', 'super admin', 'Office'),
(4, 'Sample1', 'Sample1', '12345', '', 'employee', 'Setia Point');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `orders_items`
--
ALTER TABLE `orders_items`
  ADD PRIMARY KEY (`itemID`),
  ADD KEY `orderID` (`orderID`);

--
-- Indexes for table `supplies`
--
ALTER TABLE `supplies`
  ADD PRIMARY KEY (`supply_ID`);

--
-- Indexes for table `supply_mgmt`
--
ALTER TABLE `supply_mgmt`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `orders_items`
--
ALTER TABLE `orders_items`
  MODIFY `itemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `supplies`
--
ALTER TABLE `supplies`
  MODIFY `supply_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `supply_mgmt`
--
ALTER TABLE `supply_mgmt`
  MODIFY `userID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `supply_mgmt` (`userID`);

--
-- Constraints for table `orders_items`
--
ALTER TABLE `orders_items`
  ADD CONSTRAINT `orders_items_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
