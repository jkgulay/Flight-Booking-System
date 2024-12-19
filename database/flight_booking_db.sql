-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2024 at 11:33 PM
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
-- Database: `flight_booking_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`` PROCEDURE `log_booking_activity` (IN `user_id` BIGINT, IN `action_type` VARCHAR(10), IN `table_name` VARCHAR(50), IN `affected_columns` TEXT, IN `details` TEXT)   BEGIN
    INSERT INTO logs (user_id, action_type, timestamp, table_name, affected_columns, details)
    VALUES (user_id, action_type, NOW(), table_name, affected_columns, details);
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `get_booking_count_by_flight` (`flight_id` BIGINT) RETURNS INT(11)  BEGIN
    DECLARE booking_count INT;

    SELECT COUNT(bf.id) INTO booking_count
    FROM booked_flight bf
    WHERE bf.flight_id = flight_id;

    RETURN booking_count;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_flight_price` (`flight_id` BIGINT) RETURNS DOUBLE  BEGIN
    DECLARE flight_price DOUBLE;
    SELECT price INTO flight_price FROM flight_list WHERE id = flight_id;
    RETURN flight_price;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `airlines_list`
--

CREATE TABLE `airlines_list` (
  `id` bigint(30) NOT NULL,
  `airlines` text NOT NULL,
  `logo_path` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airlines_list`
--

INSERT INTO `airlines_list` (`id`, `airlines`, `logo_path`) VALUES
(1, 'AirAsia', '1600999080_kisspng-flight-indonesia-airasia-airasia-japan-airline-tic-asia-5abad146966736.8321896415221927106161.jpg'),
(2, 'Philippine Airlines', '1600999200_Philippine-Airlines-Logo.jpg'),
(3, 'Cebu Pacific', '1600999200_43cada0008538e3c1a1f4675e5a7aabe.jpeg'),
(5, 'Cebu Mactan', '1734635640_5-removebg-preview.png'),
(14, 'Ilo-ilo', '1734637680_1.png');

-- --------------------------------------------------------

--
-- Table structure for table `airline_booking_summary`
--

CREATE TABLE `airline_booking_summary` (
  `airlines` varchar(255) DEFAULT NULL,
  `total_bookings` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airline_booking_summary`
--

INSERT INTO `airline_booking_summary` (`airlines`, `total_bookings`) VALUES
('AirAsia', 0),
('Cebu Pacific', 2),
('Philippine Airlines', 0);

-- --------------------------------------------------------

--
-- Table structure for table `airport_list`
--

CREATE TABLE `airport_list` (
  `id` bigint(30) NOT NULL,
  `airport` text NOT NULL,
  `location` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airport_list`
--

INSERT INTO `airport_list` (`id`, `airport`, `location`) VALUES
(1, 'NAIA', 'Metro Manila'),
(2, 'Beijing Capital International Airport', 'Chaoyang-Shunyi, Beijing'),
(3, 'Los Angeles International Airport', 'Los Angeles, California'),
(4, 'Dubai International Airport', 'Garhoud, Dubai'),
(5, 'Mactan-Cebu Airport', 'Cebu'),
(6, 'Bancasi Airport', 'Butuan City'),
(17, 'we', 'wew');

-- --------------------------------------------------------

--
-- Table structure for table `booked_flight`
--

CREATE TABLE `booked_flight` (
  `id` bigint(30) NOT NULL,
  `flight_id` bigint(30) NOT NULL,
  `name` text NOT NULL,
  `address` text NOT NULL,
  `contact` text NOT NULL,
  `status` enum('pending','accepted','decline') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booked_flight`
--

INSERT INTO `booked_flight` (`id`, `flight_id`, `name`, `address`, `contact`, `status`) VALUES
(2, 3, 'James Smith', 'Sample Address', '+4545 6456', 'pending'),
(3, 4, 'John Smith', 'Sample Address', '+18456-5455-55', 'pending');

-- --------------------------------------------------------

--
-- Stand-in structure for view `booked_flight_summary`
-- (See below for the actual view)
--
CREATE TABLE `booked_flight_summary` (
`booking_id` bigint(30)
,`name` text
,`contact` text
,`departure_datetime` datetime
,`arrival_datetime` datetime
,`airlines` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `flight_details`
-- (See below for the actual view)
--
CREATE TABLE `flight_details` (
`flight_id` bigint(20)
,`airlines` text
,`departure_airport` text
,`arrival_airport` text
,`departure_datetime` datetime
,`arrival_datetime` datetime
,`price` decimal(10,2)
,`seats` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `flight_list`
--

CREATE TABLE `flight_list` (
  `id` bigint(20) NOT NULL,
  `airline_id` bigint(20) NOT NULL,
  `plane_no` varchar(50) NOT NULL,
  `departure_airport_id` bigint(20) NOT NULL,
  `arrival_airport_id` bigint(20) NOT NULL,
  `departure_datetime` datetime NOT NULL,
  `arrival_datetime` datetime NOT NULL,
  `seats` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flight_list`
--

INSERT INTO `flight_list` (`id`, `airline_id`, `plane_no`, `departure_airport_id`, `arrival_airport_id`, `departure_datetime`, `arrival_datetime`, `seats`, `price`, `date_created`) VALUES
(1, 1, 'GB623-14', 1, 3, '2020-10-07 04:00:00', '2020-10-21 10:00:00', 150, 7500.00, '2020-09-25 11:23:52'),
(2, 2, 'TIPS14-15', 1, 2, '2020-10-14 11:00:00', '2020-10-16 09:00:00', 100, 5000.00, '2020-09-25 11:46:12'),
(3, 3, 'CEB-1101', 5, 1, '2020-09-30 08:00:00', '2020-09-30 08:45:00', 100, 2500.00, '2020-09-25 11:57:31'),
(4, 3, 'CEB10023', 1, 5, '2020-10-07 01:00:00', '2020-10-07 01:45:00', 100, 2500.00, '2020-09-25 14:50:47'),
(5, 3, 'wwew', 4, 4, '2024-12-06 08:00:00', '2024-12-03 09:00:00', 2, 232.00, '2024-12-18 23:28:04'),
(6, 3, 'wwew', 4, 4, '2024-12-06 08:00:00', '2024-12-03 09:00:00', 2, 232.00, '2024-12-19 01:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `action_type` enum('SELECT','INSERT','UPDATE','DELETE') NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `table_name` varchar(50) NOT NULL,
  `affected_columns` text DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action_type`, `timestamp`, `table_name`, `affected_columns`, `details`) VALUES
(1, 25, 'INSERT', '2024-12-20 05:24:09', 'users', 'ALL', 'New user added'),
(3, 25, 'UPDATE', '2024-12-20 05:27:37', 'users', 'Updated columns', 'User details updated'),
(8, 16, 'UPDATE', '2024-12-20 06:32:36', 'users', 'Updated columns', 'User details updated');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(30) NOT NULL,
  `name` text NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `cover_img` text NOT NULL,
  `about_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `name`, `email`, `contact`, `cover_img`, `about_content`) VALUES
(1, 'Online Flight Booking System', 'info@sample.com', '+6948 8542 623', '1600998360_travel-cover.jpg', 'Lorem Ipsum description.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(30) NOT NULL,
  `user_id` bigint(30) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `contact` text NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1=admin , 2 = staff, 3=customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `name`, `address`, `contact`, `username`, `password`, `type`) VALUES
(9, 2, 'DR.James Smith, M.D.', 'Sample Clinic Address', '+1456 554 55623', 'jsmith@sample.com', 'jsmith123', 2),
(10, 3, 'DR.Claire Blake, M.D.', 'Sample Only', '+5465 555 623', 'cblake@sample.com', 'blake123', 2),
(15, 9, 'DR.Sample Doctor, M.D.', 'Buenavista', '+1235 456 623', 'sample2@sample.com', 'sample123', 2),
(16, 0, 'Jun Kyle Gulay', 'Butuan City', '09100290521', 'junkyle.gulay', '$2y$10$0v1WXe.ILfW7n8IxKwg97.gVai/1htOiWCdZe3029lmp9CAk14zSO', 3),
(17, 0, 'Gummy Worms', 'p-5,poblacion 7, buenavista adn.', '09876543211', 'katzukii21', '$2y$10$soSSUffvO3pYKD.q0Rjp9eTJ6UGMSqiYAH2lfcst2loUEbWbdNl7a', 3),
(18, 0, 'Admin', 'Butuan City', '09100290521', 'admin', '$2y$10$EeMbofo.QX1UZSaRQIlvd.rESdVKoSNOIZ9GDVL1DA52aUBoc2q2i', 1),
(25, 0, 'Aldwin', 'Test Address', '1234567890', 'testuser', 'password123', 3);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    CALL log_booking_activity(NEW.id, 'INSERT', 'users', 'ALL', 'New user added');
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    CALL log_booking_activity(NEW.id, 'UPDATE', 'users', 'Updated columns', 'User details updated');
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_user_delete` BEFORE DELETE ON `users` FOR EACH ROW BEGIN
    CALL log_booking_activity(OLD.id, 'DELETE', 'users', 'ALL', 'User  deleted');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure for view `booked_flight_summary`
--
DROP TABLE IF EXISTS `booked_flight_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `booked_flight_summary`  AS SELECT `bf`.`id` AS `booking_id`, `bf`.`name` AS `name`, `bf`.`contact` AS `contact`, `f`.`departure_datetime` AS `departure_datetime`, `f`.`arrival_datetime` AS `arrival_datetime`, `a`.`airlines` AS `airlines` FROM ((`booked_flight` `bf` join `flight_list` `f` on(`bf`.`flight_id` = `f`.`id`)) join `airlines_list` `a` on(`f`.`airline_id` = `a`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `flight_details`
--
DROP TABLE IF EXISTS `flight_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `flight_details`  AS SELECT `f`.`id` AS `flight_id`, `a`.`airlines` AS `airlines`, `ap1`.`airport` AS `departure_airport`, `ap2`.`airport` AS `arrival_airport`, `f`.`departure_datetime` AS `departure_datetime`, `f`.`arrival_datetime` AS `arrival_datetime`, `f`.`price` AS `price`, `f`.`seats` AS `seats` FROM (((`flight_list` `f` join `airlines_list` `a` on(`f`.`airline_id` = `a`.`id`)) join `airport_list` `ap1` on(`f`.`departure_airport_id` = `ap1`.`id`)) join `airport_list` `ap2` on(`f`.`arrival_airport_id` = `ap2`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `airlines_list`
--
ALTER TABLE `airlines_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `airport_list`
--
ALTER TABLE `airport_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_flight_airport` (`location`(768));

--
-- Indexes for table `booked_flight`
--
ALTER TABLE `booked_flight`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flight_id` (`flight_id`),
  ADD KEY `idx_booked_flight` (`flight_id`);

--
-- Indexes for table `flight_list`
--
ALTER TABLE `flight_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `airline_id` (`airline_id`),
  ADD KEY `departure_airport_id` (`departure_airport_id`),
  ADD KEY `arrival_airport_id` (`arrival_airport_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `airlines_list`
--
ALTER TABLE `airlines_list`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `airport_list`
--
ALTER TABLE `airport_list`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `booked_flight`
--
ALTER TABLE `booked_flight`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `flight_list`
--
ALTER TABLE `flight_list`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `flight_list`
--
ALTER TABLE `flight_list`
  ADD CONSTRAINT `flight_list_ibfk_1` FOREIGN KEY (`airline_id`) REFERENCES `airlines_list` (`id`),
  ADD CONSTRAINT `flight_list_ibfk_2` FOREIGN KEY (`departure_airport_id`) REFERENCES `airport_list` (`id`),
  ADD CONSTRAINT `flight_list_ibfk_3` FOREIGN KEY (`arrival_airport_id`) REFERENCES `airport_list` (`id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
