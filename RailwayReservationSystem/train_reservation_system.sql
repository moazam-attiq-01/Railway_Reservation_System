-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2024 at 05:08 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `train_reservation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `cnic` varchar(15) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `cnic`, `email`, `password`, `name`) VALUES
(1, '1234567890123', 'admin1@example.com', '1234', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `number_of_seats` int(11) NOT NULL,
  `seat_id` int(11) DEFAULT NULL,
  `passenger_ID` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `number_of_seats`, `seat_id`, `passenger_ID`, `status`) VALUES
(1, 3, 1, 1, 'Confirmed'),
(2, 2, 2, 2, 'Confirmed'),
(3, 2, 3, 1, 'Confirmed'),
(4, 2, 4, 3, 'cancelled'),
(5, 1, 5, 1, 'Confirmed'),
(6, 2, 6, 1, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `fare` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `type`, `fare`) VALUES
(1, 'Business', '150.00'),
(2, 'Economy', '50.00');

-- --------------------------------------------------------

--
-- Table structure for table `passenger`
--

CREATE TABLE `passenger` (
  `passenger_ID` int(11) NOT NULL,
  `wallet` decimal(10,2) DEFAULT 10000.00,
  `cnic` varchar(15) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `passenger`
--

INSERT INTO `passenger` (`passenger_ID`, `wallet`, `cnic`, `email`, `password`, `name`) VALUES
(1, '10775.00', '112233445566', 'rida@gmail.com', '1122', 'rida'),
(2, '12525.00', '11228899007766', 'ayesha@gmail.com', '1122', 'ayesha'),
(3, '10325.00', '66778899', 'Mahir@gmail.com', '1122', 'Mahir');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `method` varchar(50) NOT NULL,
  `p_status` varchar(50) NOT NULL,
  `booking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `amount`, `date`, `method`, `p_status`, `booking_id`) VALUES
(1, '1950.00', '2024-09-04', 'Jazz Cash', 'Refunded', 1),
(2, '1100.00', '2024-08-28', 'Bank', 'Refunded', 2),
(3, '1100.00', '2024-09-04', 'Bank', 'Refunded', 3),
(4, '1100.00', '2024-09-11', 'Jazz Cash', 'Refunded', 4),
(5, '650.00', '2024-08-21', 'Bank', 'Refunded', 5),
(6, '1100.00', '2024-09-04', 'Unknown', 'Paid', 6);

-- --------------------------------------------------------

--
-- Table structure for table `refund`
--

CREATE TABLE `refund` (
  `refund_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `refund`
--

INSERT INTO `refund` (`refund_id`, `payment_id`, `amount`, `status`, `date`) VALUES
(1, 1, '1950.00', 'Refunded', '2024-08-20'),
(2, 2, '1100.00', 'Refunded', '2024-08-20'),
(3, 3, '1100.00', 'Refunded', '2024-08-20'),
(4, 4, '1100.00', 'Refunded', '2024-08-20'),
(5, 5, '325.00', 'Refunded', '2024-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `seat_id` int(11) NOT NULL,
  `seat_num` varchar(10) NOT NULL,
  `status` varchar(50) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `TS_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`seat_id`, `seat_num`, `status`, `class_id`, `departure_date`, `TS_ID`) VALUES
(1, 'B046', 'Booked', 1, '2024-09-04', 3),
(2, 'E056', 'Booked', 2, '2024-08-28', 2),
(3, 'E025', 'Booked', 2, '2024-09-04', 3),
(4, 'E056', 'Booked', 2, '2024-09-11', 4),
(5, 'B008', 'Booked', 1, '2024-08-21', 1),
(6, 'E085', 'Booked', 2, '2024-09-04', 3);

-- --------------------------------------------------------

--
-- Table structure for table `train`
--

CREATE TABLE `train` (
  `train_id` int(11) NOT NULL,
  `total_BSeat` int(11) NOT NULL,
  `total_ESeat` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `train`
--

INSERT INTO `train` (`train_id`, `total_BSeat`, `total_ESeat`, `name`, `status`) VALUES
(1, 50, 200, 'Awam Express', 'Active'),
(2, 60, 180, 'Abaseen Express', 'Active'),
(3, 70, 190, 'Allama Iqbal Express', 'Active'),
(4, 55, 210, 'Buraq Express', 'Active'),
(5, 65, 220, 'Faisal Express', 'Active'),
(6, 75, 230, 'Hazara Express', 'Active'),
(7, 80, 240, 'Jinnah Express', 'Active'),
(8, 85, 250, 'Lala Musa Express', 'Active'),
(9, 90, 260, 'Margalla Express', 'Active'),
(10, 95, 270, 'Mehran Express', 'Active'),
(11, 100, 280, 'Millat Express', 'Active'),
(12, 105, 290, 'Qalander Express', 'Active'),
(13, 110, 300, 'Ravi Express', 'Active'),
(14, 115, 310, 'Rohi Express', 'Active'),
(15, 120, 320, 'Super Express', 'Active'),
(16, 125, 330, 'Zarghoon Express', 'Active'),
(17, 130, 340, 'Shalimar Express', 'Active'),
(18, 135, 350, 'Chennai Express', 'Active'),
(19, 140, 360, 'Shah Hussain Express', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `train_schedule`
--

CREATE TABLE `train_schedule` (
  `TS_ID` int(11) NOT NULL,
  `departure_date` date NOT NULL,
  `source` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `train_id` int(11) DEFAULT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `arrival_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `train_schedule`
--

INSERT INTO `train_schedule` (`TS_ID`, `departure_date`, `source`, `destination`, `fare`, `train_id`, `departure_time`, `arrival_time`, `arrival_date`) VALUES
(1, '2024-08-21', 'Lahore', 'Karachi', '500.00', 1, '08:00:00', '20:00:00', '2024-08-21'),
(2, '2024-08-22', 'Lahore', 'Karachi', '150.00', 2, '14:00:00', '16:00:00', '2024-08-22'),
(2, '2024-08-28', 'Lahore', 'Karachi', '500.00', 2, '08:00:00', '20:00:00', '2024-08-28'),
(3, '2024-09-04', 'Lahore', 'Karachi', '500.00', 3, '08:00:00', '20:00:00', '2024-09-04'),
(4, '2024-09-11', 'Lahore', 'Karachi', '500.00', 4, '08:00:00', '20:00:00', '2024-09-11'),
(5, '2024-08-22', 'Lahore', 'Islamabad', '200.00', 5, '09:00:00', '12:00:00', '2024-08-22'),
(6, '2024-08-29', 'Lahore', 'Islamabad', '200.00', 6, '09:00:00', '12:00:00', '2024-08-29'),
(7, '2024-09-05', 'Lahore', 'Islamabad', '200.00', 7, '09:00:00', '12:00:00', '2024-09-05'),
(8, '2024-08-23', 'Lahore', 'Faisalabad', '150.00', 8, '10:00:00', '12:30:00', '2024-08-23'),
(9, '2024-08-30', 'Lahore', 'Faisalabad', '150.00', 9, '10:00:00', '12:30:00', '2024-08-30'),
(10, '2024-09-06', 'Lahore', 'Faisalabad', '150.00', 10, '10:00:00', '12:30:00', '2024-09-06'),
(11, '2024-08-22', 'Karachi', 'Lahore', '500.00', 11, '08:00:00', '20:00:00', '2024-08-22'),
(12, '2024-08-29', 'Karachi', 'Lahore', '500.00', 12, '08:00:00', '20:00:00', '2024-08-29'),
(13, '2024-09-05', 'Karachi', 'Lahore', '500.00', 13, '08:00:00', '20:00:00', '2024-09-05'),
(14, '2024-09-12', 'Karachi', 'Lahore', '500.00', 14, '08:00:00', '20:00:00', '2024-09-12'),
(15, '2024-08-24', 'Karachi', 'Islamabad', '250.00', 15, '09:00:00', '14:00:00', '2024-08-24'),
(16, '2024-08-31', 'Karachi', 'Islamabad', '250.00', 16, '09:00:00', '14:00:00', '2024-08-31'),
(17, '2024-09-07', 'Karachi', 'Islamabad', '250.00', 17, '09:00:00', '14:00:00', '2024-09-07'),
(18, '2024-08-25', 'Islamabad', 'Multan', '300.00', 18, '10:00:00', '16:00:00', '2024-08-25'),
(19, '2024-09-01', 'Islamabad', 'Multan', '300.00', 19, '10:00:00', '16:00:00', '2024-09-01'),
(20, '2024-09-08', 'Islamabad', 'Multan', '300.00', 6, '10:00:00', '16:00:00', '2024-09-08'),
(21, '2024-09-15', 'Islamabad', 'Multan', '300.00', 1, '10:00:00', '16:00:00', '2024-09-15'),
(22, '2024-08-26', 'Faisalabad', 'Multan', '180.00', 2, '11:00:00', '14:00:00', '2024-08-26'),
(23, '2024-09-02', 'Faisalabad', 'Multan', '180.00', 3, '11:00:00', '14:00:00', '2024-09-02'),
(24, '2024-09-09', 'Faisalabad', 'Multan', '180.00', 4, '11:00:00', '14:00:00', '2024-09-09'),
(25, '2024-09-16', 'Faisalabad', 'Multan', '180.00', 5, '11:00:00', '14:00:00', '2024-09-16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `cnic` (`cnic`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `seat_id` (`seat_id`),
  ADD KEY `passenger_ID` (`passenger_ID`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `passenger`
--
ALTER TABLE `passenger`
  ADD PRIMARY KEY (`passenger_ID`),
  ADD UNIQUE KEY `cnic` (`cnic`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `refund`
--
ALTER TABLE `refund`
  ADD PRIMARY KEY (`refund_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`seat_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `TS_ID` (`TS_ID`,`departure_date`);

--
-- Indexes for table `train`
--
ALTER TABLE `train`
  ADD PRIMARY KEY (`train_id`);

--
-- Indexes for table `train_schedule`
--
ALTER TABLE `train_schedule`
  ADD PRIMARY KEY (`TS_ID`,`departure_date`),
  ADD KEY `train_id` (`train_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `passenger`
--
ALTER TABLE `passenger`
  MODIFY `passenger_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `refund`
--
ALTER TABLE `refund`
  MODIFY `refund_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `seat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `train`
--
ALTER TABLE `train`
  MODIFY `train_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`passenger_ID`) REFERENCES `passenger` (`passenger_ID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`);

--
-- Constraints for table `refund`
--
ALTER TABLE `refund`
  ADD CONSTRAINT `refund_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`);

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`),
  ADD CONSTRAINT `seats_ibfk_2` FOREIGN KEY (`TS_ID`,`departure_date`) REFERENCES `train_schedule` (`TS_ID`, `departure_date`);

--
-- Constraints for table `train_schedule`
--
ALTER TABLE `train_schedule`
  ADD CONSTRAINT `train_schedule_ibfk_1` FOREIGN KEY (`train_id`) REFERENCES `train` (`train_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
