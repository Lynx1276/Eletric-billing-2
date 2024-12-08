-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2024 at 05:24 AM
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
-- Database: `billings`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `password` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `phone_number`, `password`, `created_at`) VALUES
(1, 'John Doe', 'john@gmail.com', '1234567890', 'password123', '2024-12-02 08:34:36'),
(2, 'Jane Smith', 'jane.smith@example.com', '0987654321', 'hashed_password_2', '2024-12-02 08:34:36'),
(3, 'Mark Brown', 'mark.brown@example.com', '1122334455', 'hashed_password_3', '2024-12-02 08:34:36'),
(4, 'Emily Davis', 'emily.davis@example.com', '2233445566', 'hashed_password_4', '2024-12-02 08:34:36'),
(5, 'Michael Wilson', 'michael.wilson@example.com', '3344556677', 'hashed_password_5', '2024-12-02 08:34:36'),
(6, 'Sarah Johnson', 'sarah.johnson@example.com', '4455667788', 'hashed_password_6', '2024-12-02 08:34:36'),
(7, 'Robert Lee', 'robert.lee@example.com', '5566778899', 'hashed_password_7', '2024-12-02 08:34:36'),
(8, 'Laura Miller', 'laura.miller@example.com', '6677889900', 'hashed_password_8', '2024-12-02 08:34:36'),
(9, 'David Garcia', 'david.garcia@example.com', '7788990011', 'hashed_password_9', '2024-12-02 08:34:36'),
(10, 'Olivia Martinez', 'olivia.martinez@example.com', '8899001122', 'hashed_password_10', '2024-12-02 08:34:36'),
(11, 'Kyu Chan', 'kyu@gmail.com', '09087868', 'kyu123', '2024-12-02 09:42:26'),
(12, 'admin', 'admin@gmail.com', '090875789', 'admin123', '2024-12-02 09:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `auditlogs`
--

CREATE TABLE `auditlogs` (
  `log_id` int(11) NOT NULL,
  `action_performed` text NOT NULL,
  `performed_by_admin_id` int(11) DEFAULT NULL,
  `affected_user_id` int(11) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auditlogs`
--

INSERT INTO `auditlogs` (`log_id`, `action_performed`, `performed_by_admin_id`, `affected_user_id`, `table_name`, `record_id`, `performed_at`) VALUES
(1, 'Created new tariff plan', 1, NULL, 'Tariffs', 1, '2024-11-01 02:15:00'),
(2, 'Updated outage schedule', 2, NULL, 'Outages', 3, '2024-11-02 06:30:00'),
(3, 'Paid billing amount', NULL, 1, 'Billings', 5, '2024-11-03 01:00:00'),
(4, 'Created new user', 3, NULL, 'Users', 10, '2024-11-04 03:45:00'),
(5, 'Updated billing information', 1, NULL, 'Billings', 2, '2024-11-05 04:00:00'),
(6, 'Scheduled outage maintenance', 4, NULL, 'Outages', 2, '2024-11-06 05:15:00'),
(7, 'Deleted tariff plan', 2, NULL, 'Tariffs', 4, '2024-11-07 06:00:00'),
(8, 'User requested payment method change', NULL, 2, 'Users', 7, '2024-11-08 07:45:00'),
(9, 'Updated payment status', 1, NULL, 'Payments', 6, '2024-11-09 08:30:00'),
(10, 'Paid overdue bill', NULL, 3, 'Billings', 8, '2024-11-10 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `bill_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `meter_id` int(11) NOT NULL,
  `billing_month` date NOT NULL,
  `units_consumed` float NOT NULL,
  `cost_per_unit` decimal(5,2) DEFAULT 5.00,
  `total_cost` decimal(10,2) GENERATED ALWAYS AS (`units_consumed` * `cost_per_unit`) STORED,
  `due_date` date NOT NULL,
  `bill_status` enum('Paid','Unpaid') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing`
--

INSERT INTO `billing` (`bill_id`, `user_id`, `connection_id`, `admin_id`, `meter_id`, `billing_month`, `units_consumed`, `cost_per_unit`, `due_date`, `bill_status`) VALUES
(6, 1, 1, 1, 18, '2024-12-05', 5, 5.00, '2025-01-04', 'Paid'),
(8, 16, 11, 1, 20, '2024-12-05', 40, 5.00, '2025-01-04', 'Unpaid'),
(9, 16, 11, 1, 21, '2024-12-05', 250, 5.00, '2025-01-04', 'Unpaid'),
(10, 1, 1, 1, 22, '2024-12-05', 344, 5.00, '2025-01-04', 'Unpaid'),
(11, 10, 10, 1, 23, '2024-12-05', 200, 5.00, '2025-01-04', 'Paid'),
(12, 16, 11, 1, 24, '2024-12-05', 200, 5.00, '2025-01-04', 'Unpaid'),
(13, 3, 3, 1, 25, '2024-12-05', 180, 7.00, '2025-01-04', 'Unpaid'),
(14, 1, 1, 1, 28, '0000-00-00', 300, 5.00, '2025-01-05', 'Unpaid'),
(15, 1, 1, 1, 29, '0000-00-00', 200, 5.00, '2025-01-05', 'Unpaid'),
(16, 2, 2, 1, 30, '0000-00-00', 422, 5.00, '2025-01-05', 'Unpaid'),
(17, 1, 1, 1, 31, '2024-12-06', 150, 5.00, '2025-01-05', 'Unpaid'),
(18, 10, 10, 1, 32, '2024-12-06', 80, 5.00, '2025-01-05', 'Unpaid'),
(19, 1, 1, 1, 33, '2024-12-06', 250, 5.00, '2025-01-05', 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `complaint_type` varchar(50) NOT NULL,
  `complaint_details` text NOT NULL,
  `status` enum('Open','In Progress','Resolved') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `user_id`, `complaint_type`, `complaint_details`, `status`, `created_at`) VALUES
(1, 1, 'Service', 'ang bagal ng service niyo', '', '2024-12-03 02:54:39'),
(2, 1, 'Service', 'fwaeg v', '', '2024-12-03 15:00:54');

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `connection_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `connection_type` enum('Residential','Commercial') NOT NULL,
  `connection_status` enum('Active','Inactive') DEFAULT 'Active',
  `meter_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`connection_id`, `user_id`, `connection_type`, `connection_status`, `meter_number`, `created_at`) VALUES
(1, 1, 'Residential', 'Active', 'MTR-0001', '2024-12-02 08:35:08'),
(2, 2, 'Residential', 'Active', 'MTR-0002', '2024-12-02 08:35:08'),
(3, 3, 'Commercial', 'Active', 'MTR-0003', '2024-12-02 08:35:08'),
(4, 4, 'Residential', 'Inactive', 'MTR-0004', '2024-12-02 08:35:08'),
(5, 5, 'Residential', 'Active', 'MTR-0005', '2024-12-02 08:35:08'),
(6, 6, 'Commercial', 'Inactive', 'MTR-0006', '2024-12-02 08:35:08'),
(7, 7, 'Residential', 'Active', 'MTR-0007', '2024-12-02 08:35:08'),
(8, 8, 'Residential', 'Active', 'MTR-0008', '2024-12-02 08:35:08'),
(9, 9, 'Commercial', 'Active', 'MTR-0009', '2024-12-02 08:35:08'),
(10, 10, 'Residential', 'Inactive', 'MTR-0010', '2024-12-02 08:35:08'),
(11, 16, 'Residential', 'Active', 'MT-3000', '2024-12-05 10:03:55');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `rating` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meters`
--

CREATE TABLE `meters` (
  `meter_id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `previous_reading` float NOT NULL,
  `current_reading` float NOT NULL,
  `reading_date` date NOT NULL,
  `units_consumed` float GENERATED ALWAYS AS (`current_reading` - `previous_reading`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meters`
--

INSERT INTO `meters` (`meter_id`, `connection_id`, `admin_id`, `previous_reading`, `current_reading`, `reading_date`) VALUES
(18, 1, 1, 100, 105, '2024-12-05'),
(20, 11, 1, 210, 250, '2024-12-05'),
(21, 11, 1, 200, 450, '2024-12-05'),
(22, 1, 1, 56, 400, '2024-12-05'),
(23, 10, 1, 300, 500, '2024-12-05'),
(24, 11, 1, 100, 300, '2024-12-05'),
(25, 3, 1, 120, 300, '2024-12-05'),
(28, 1, 1, 200, 500, '2024-12-06'),
(29, 1, 1, 100, 300, '2024-12-06'),
(30, 2, 1, 78, 500, '2024-12-06'),
(31, 1, 1, 50, 200, '2024-12-06'),
(32, 10, 1, 120, 200, '2024-12-06'),
(33, 1, 1, 50, 300, '2024-12-06');

-- --------------------------------------------------------

--
-- Table structure for table `outages`
--

CREATE TABLE `outages` (
  `outage_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `area` varchar(100) NOT NULL,
  `outage_type` enum('Scheduled','Unscheduled') NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outages`
--

INSERT INTO `outages` (`outage_id`, `admin_id`, `area`, `outage_type`, `start_time`, `end_time`) VALUES
(1, 1, 'Downtown', 'Scheduled', '2024-11-01 02:00:00', '2024-11-01 12:00:00'),
(2, 2, 'Suburb Area', 'Unscheduled', '2024-11-02 06:00:00', '2024-11-02 15:30:00'),
(3, 3, 'Industrial Zone', 'Scheduled', '2024-11-03 00:00:00', '2024-11-03 10:30:00'),
(4, 4, 'City Center', 'Unscheduled', '2024-11-04 03:00:00', '2024-11-04 13:00:00'),
(5, 2, 'North District', 'Scheduled', '2024-11-05 01:00:00', '2024-11-05 11:00:00'),
(6, 1, 'East Side', 'Unscheduled', '2024-11-06 08:00:00', '2024-11-06 17:15:00'),
(7, 3, 'West End', 'Scheduled', '2024-11-06 23:00:00', '2024-11-07 09:00:00'),
(8, 4, 'South Park', 'Unscheduled', '2024-11-08 05:00:00', '2024-11-08 14:45:00'),
(9, 1, 'Central Park', 'Scheduled', '2024-11-09 10:00:00', '2024-11-09 19:30:00'),
(10, 2, 'Outer Ring', 'Unscheduled', '2024-11-10 12:00:00', '2024-11-10 22:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('Gcash','Maya','Paypal') NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `bill_id`, `payment_date`, `payment_method`, `amount_paid`) VALUES
(25, 6, '2024-12-05 13:14:55', 'Paypal', 25.00),
(26, 11, '2024-12-06 02:44:41', 'Gcash', 1000.00);

--
-- Triggers `payments`
--
DELIMITER $$
CREATE TRIGGER `update_bill_status` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    UPDATE billing
    SET bill_status = 'Paid'
    WHERE bill_id = NEW.bill_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tariffs`
--

CREATE TABLE `tariffs` (
  `tariff_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `connection_type` enum('Residential','Commercial') NOT NULL,
  `cost_per_unit` decimal(5,2) NOT NULL,
  `effective_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tariffs`
--

INSERT INTO `tariffs` (`tariff_id`, `admin_id`, `connection_type`, `cost_per_unit`, `effective_date`) VALUES
(41, 1, 'Residential', 0.90, '2024-12-02'),
(42, 3, 'Commercial', 0.20, '2024-12-02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone_number`, `address`, `created_at`) VALUES
(1, 'Alice White', 'alice@gmail.com', 'pass', '1234509876', '123 Elm Street', '2024-12-02 08:34:36'),
(2, 'Bob Green', 'bob.green@example.com', 'hashed_password_12', '9876501234', '456 Maple Ave', '2024-12-02 08:34:36'),
(3, 'Charlie Black', 'charlie.black@example.com', 'hashed_password_13', '6543217890', '789 Oak Blvd', '2024-12-02 08:34:36'),
(4, 'Diana Blue', 'diana.blue@example.com', 'hashed_password_14', '3210987654', '101 Pine Road', '2024-12-02 08:34:36'),
(5, 'Eve Brown', 'eve.brown@example.com', 'hashed_password_15', '5678901234', '202 Cedar Dr', '2024-12-02 08:34:36'),
(6, 'Frank Yellow', 'frank.yellow@example.com', 'hashed_password_16', '8901234567', '303 Birch Way', '2024-12-02 08:34:36'),
(7, 'Grace Pink', 'grace.pink@example.com', 'hashed_password_17', '1236789098', '404 Walnut St', '2024-12-02 08:34:36'),
(8, 'Henry Orange', 'henry.orange@example.com', 'hashed_password_18', '7890561234', '505 Cherry Ln', '2024-12-02 08:34:36'),
(9, 'Ivy Purple', 'ivy.purple@example.com', 'hashed_password_19', '4567890123', '606 Palm Ave', '2024-12-02 08:34:36'),
(10, 'Jack Gray', 'jack@gmail.com', 'abcd', '6789012345', '707 Spruce Ct', '2024-12-02 08:34:36'),
(16, 'Albert G', 'albert@gmail.com', '1234', '0987625389', 'doon', '2024-12-05 10:03:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `performed_by_admin_id` (`performed_by_admin_id`),
  ADD KEY `affected_user_id` (`affected_user_id`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `connection_id` (`connection_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meter_id` (`meter_id`),
  ADD KEY `billing_ibfk_2` (`admin_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`connection_id`),
  ADD UNIQUE KEY `meter_number` (`meter_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meters`
--
ALTER TABLE `meters`
  ADD PRIMARY KEY (`meter_id`),
  ADD KEY `connection_id` (`connection_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `outages`
--
ALTER TABLE `outages`
  ADD PRIMARY KEY (`outage_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `tariffs`
--
ALTER TABLE `tariffs`
  ADD PRIMARY KEY (`tariff_id`),
  ADD UNIQUE KEY `connection_type` (`connection_type`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `auditlogs`
--
ALTER TABLE `auditlogs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `connection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meters`
--
ALTER TABLE `meters`
  MODIFY `meter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `outages`
--
ALTER TABLE `outages`
  MODIFY `outage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tariffs`
--
ALTER TABLE `tariffs`
  MODIFY `tariff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auditlogs`
--
ALTER TABLE `auditlogs`
  ADD CONSTRAINT `auditlogs_ibfk_1` FOREIGN KEY (`performed_by_admin_id`) REFERENCES `admins` (`admin_id`),
  ADD CONSTRAINT `auditlogs_ibfk_2` FOREIGN KEY (`affected_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`connection_id`) REFERENCES `connections` (`connection_id`),
  ADD CONSTRAINT `billing_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `billing_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `billing_ibfk_4` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`meter_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `meters`
--
ALTER TABLE `meters`
  ADD CONSTRAINT `meters_ibfk_1` FOREIGN KEY (`connection_id`) REFERENCES `connections` (`connection_id`),
  ADD CONSTRAINT `meters_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `outages`
--
ALTER TABLE `outages`
  ADD CONSTRAINT `outages_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `tariffs`
--
ALTER TABLE `tariffs`
  ADD CONSTRAINT `tariffs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
