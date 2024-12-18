-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2020 at 10:21 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.2.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `airlines_list` (
  `id` BIGINT(30) NOT NULL AUTO_INCREMENT,
  `airlines` TEXT NOT NULL,
  `logo_path` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `airlines_list`
INSERT INTO `airlines_list` (`id`, `airlines`, `logo_path`) VALUES
(1, 'AirAsia', '1600999080_kisspng-flight-indonesia-airasia-airasia-japan-airline-tic-asia-5abad146966736.8321896415221927106161.jpg'),
(2, 'Philippine Airlines', '1600999200_Philippine-Airlines-Logo.jpg'),
(3, 'Cebu Pacific', '1600999200_43cada0008538e3c1a1f4675e5a7aabe.jpeg');

CREATE TABLE `airport_list` (
  `id` BIGINT(30) NOT NULL AUTO_INCREMENT,
  `airport` TEXT NOT NULL,
  `location` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `airport_list`
INSERT INTO `airport_list` (`id`, `airport`, `location`) VALUES
(1, 'NAIA', 'Metro Manila'),
(2, 'Beijing Capital International Airport', 'Chaoyang-Shunyi, Beijing'),
(3, 'Los Angeles International Airport', 'Los Angeles, California'),
(4, 'Dubai International Airport', 'Garhoud, Dubai'),
(5, 'Mactan-Cebu Airport', 'Cebu');

-- Table structure for table `booked_flight`
CREATE TABLE `booked_flight` (
  `id` BIGINT(30) NOT NULL AUTO_INCREMENT,
  `flight_id` BIGINT(30) NOT NULL,
  `name` TEXT NOT NULL,
  `address` TEXT NOT NULL,
  `contact` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`flight_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `booked_flight`
INSERT INTO `booked_flight` (`id`, `flight_id`, `name`, `address`, `contact`) VALUES
(2, 3, 'James Smith', 'Sample Address', '+4545 6456'),
(3, 4, 'John Smith', 'Sample Address', '+18456-5455-55');

-- Table structure for table `flight_list

CREATE TABLE `flight_list` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `airline_id` BIGINT NOT NULL,
  `plane_no` VARCHAR(50) NOT NULL,
  `departure_airport_id` BIGINT NOT NULL,
  `arrival_airport_id` BIGINT NOT NULL,
  `departure_datetime` DATETIME NOT NULL,
  `arrival_datetime` DATETIME NOT NULL,
  `seats` INT NOT NULL DEFAULT 0,
  `price` DECIMAL(10, 2) NOT NULL,
  `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`airline_id`) REFERENCES `airlines_list`(`id`),
  FOREIGN KEY (`departure_airport_id`) REFERENCES `airport_list`(`id`),
  FOREIGN KEY (`arrival_airport_id`) REFERENCES `airport_list`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `flight_list`
INSERT INTO `flight_list` (`id`, `airline_id`, `plane_no`, `departure_airport_id`, `arrival_airport_id`, `departure_datetime`, `arrival_datetime`, `seats`, `price`, `date_created`) VALUES
(1, 1, 'GB623-14', 1, 3, '2020-10-07 04:00:00', '2020-10-21 10:00:00', 150, 7500, '2020-09-25 11:23:52'),
(2, 2, 'TIPS14-15', 1, 2, '2020-10-14 11:00:00', '2020-10-16 09:00:00', 100, 5000, '2020-09-25 11:46:12'),
(3, 3, 'CEB-1101', 5, 1, '2020-09-30 08:00:00', '2020-09-30 08:45:00', 100, 2500, '2020-09-25 11:57:31'),
(4, 3, 'CEB10023', 1, 5, '2020-10-07 01:00:00', '2020-10-07 01:45:00', 100, 2500, '2020-09-25 14:50:47');

-- Table structure for table `system_settings`
CREATE TABLE `system_settings` (
  `id` BIGINT(30) NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `email` VARCHAR(200) NOT NULL,
  `contact` VARCHAR(20) NOT NULL,
  `cover_img` TEXT NOT NULL,
  `about_content` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `system_settings`
INSERT INTO `system_settings` (`id`, `name`, `email`, `contact`, `cover_img`, `about_content`) VALUES
(1, 'Online Flight Booking System', 'info@sample.com', '+6948 8542 623', '1600998360_travel-cover.jpg', 'Lorem Ipsum description.');


-- Table structure for table `users`
CREATE TABLE `users` (
  `id` BIGINT(30) NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT(30) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `address` TEXT NOT NULL,
  `contact` TEXT NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(200) NOT NULL,
  `type` TINYINT(1) NOT NULL DEFAULT 2 COMMENT '1=admin , 2 = staff, 3=patient',
  PRIMARY KEY (`id`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `user_id`, `name`, `address`, `contact`, `username`, `password`, `type`) VALUES
(1, 1, 'Administrator', '', '', 'admin', 'admin123', 1),
(7, 5, 'George Wilson', 'Sample Only', '+18456-5455-55', 'gwilson@sample.com', 'd40242fb23c45206fadee4e2418f274f', 3),
(9, 2, 'DR.James Smith, M.D.', 'Sample Clinic Address', '+1456 554 55623', 'jsmith@sample.com', 'jsmith123', 2),
(10, 3, 'DR.Claire Blake, M.D.', 'Sample Only', '+5465 555 623', 'cblake@sample.com', 'blake123', 2),
(11, 6, 'Sample Only', 'Sample', '+5465 546 4556', 'sample@sample.com', '4e91b1cbe42b5c884de47d4c7fda0555', 3),
(15, 9, 'DR.Sample Doctor, M.D.', 'Sample Address', '+1235 456 623', 'sample2@sample.com', 'sample123', 2);

SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_booked FROM booked_flight;
SELECT COUNT(*) AS total_available FROM flight_list WHERE seats > 0; -- Assuming seats > 0 means available

-- Regular View: Flight Details with Airline and Airport Information
CREATE VIEW flight_details AS
SELECT 
    f.id AS flight_id,
    a.airlines,
    ap1.airport AS departure_airport,
    ap2.airport AS arrival_airport,
    f.departure_datetime,
    f.arrival_datetime,
    f.price,
    f.seats
FROM 
    flight_list f
JOIN 
    airlines_list a ON f.airline_id = a.id
JOIN 
    airport_list ap1 ON f.departure_airport_id = ap1.id
JOIN 
    airport_list ap2 ON f.arrival_airport_id = ap2.id;

-- Regular View: Summary of Booked Flights
CREATE VIEW booked_flight_summary AS
SELECT 
    bf.id AS booking_id,
    bf.name,
    bf.contact,
    f.departure_datetime,
    f.arrival_datetime,
    a.airlines
FROM 
    booked_flight bf
JOIN 
    flight_list f ON bf.flight_id = f.id
JOIN 
    airlines_list a ON f.airline_id = a.id;

-- Materialized View: Total Bookings per Airline
CREATE TABLE airline_booking_summary (
    airlines VARCHAR(255),
    total_bookings INT
);

INSERT INTO airline_booking_summary (airlines, total_bookings)
SELECT 
    a.airlines,
    COUNT(bf.id) AS total_bookings
FROM 
    airlines_list a
LEFT JOIN 
    flight_list f ON a.id = f.airline_id
LEFT JOIN 
    booked_flight bf ON f.id = bf.flight_id
GROUP BY 
    a.airlines;


-- Index on Flight List for Departure Airport
CREATE INDEX idx_flight_airport ON airport_list (location);

-- Index on Booked Flight for Flight ID
CREATE INDEX idx_booked_flight ON booked_flight (flight_id);

ALTER TABLE booked_flight ADD COLUMN status ENUM('pending', 'accepted', 'decline') DEFAULT 'pending';

-- Scalar Function
DELIMITER $$

CREATE FUNCTION get_flight_price(flight_id BIGINT) 
RETURNS DOUBLE
BEGIN
    DECLARE flight_price DOUBLE;
    SELECT price INTO flight_price FROM flight_list WHERE id = flight_id;
    RETURN flight_price;
END$$

DELIMITER ;

-- Table-Valued Function (Note: MySQL does not support table-valued functions)
-- Instead, this function returns a single integer value (the count of bookings)
DELIMITER $$
CREATE FUNCTION get_booking_count_by_flight(flight_id BIGINT) 
RETURNS INT
BEGIN
    DECLARE booking_count INT;

    SELECT COUNT(bf.id) INTO booking_count
    FROM booked_flight bf
    WHERE bf.flight_id = flight_id;

    RETURN booking_count;
END$$

DELIMITER ;