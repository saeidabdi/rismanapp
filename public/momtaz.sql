-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2020 at 10:38 AM
-- Server version: 10.1.28-MariaDB
-- PHP Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `momtaz`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_persian_ci NOT NULL,
  `username` varchar(20) COLLATE utf8_persian_ci NOT NULL,
  `pass` varchar(10) COLLATE utf8_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `username`, `pass`) VALUES
(1, 'سعید عبدی', 'saeid', '123');

-- --------------------------------------------------------

--
-- Table structure for table `edu_plan`
--

CREATE TABLE `edu_plan` (
  `id` int(11) NOT NULL,
  `date_time` varchar(30) COLLATE utf8_persian_ci NOT NULL,
  `l_id` int(11) NOT NULL,
  `stu_id` int(11) NOT NULL,
  `study_time` int(11) DEFAULT '0',
  `test_time` int(11) DEFAULT '0',
  `test_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `edu_plan`
--

INSERT INTO `edu_plan` (`id`, `date_time`, `l_id`, `stu_id`, `study_time`, `test_time`, `test_count`) VALUES
(52, '1592921482', 1, 30, NULL, NULL, NULL),
(53, '1592921482', 2, 30, NULL, NULL, NULL),
(54, '1592921482', 3, 30, NULL, NULL, NULL),
(61, '1592986021', 1, 30, NULL, NULL, NULL),
(62, '1592986021', 2, 30, NULL, NULL, NULL),
(63, '1592986021', 3, 30, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson`
--

CREATE TABLE `lesson` (
  `id` int(11) NOT NULL,
  `title` varchar(20) COLLATE utf8_persian_ci NOT NULL,
  `base_id` int(11) NOT NULL,
  `r_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `lesson`
--

INSERT INTO `lesson` (`id`, `title`, `base_id`, `r_id`) VALUES
(1, 'ریاضی', 0, NULL),
(2, 'علوم', 0, NULL),
(3, 'فارسی', 0, NULL),
(4, 'ریاضی', 1, 0),
(5, 'ریاضی', 1, 1),
(6, 'فیزیک', 1, 0),
(7, 'شیمی', 1, 0),
(8, 'دینی', 1, 0),
(9, 'عربی', 1, 0),
(10, 'فارسی', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `mosh`
--

CREATE TABLE `mosh` (
  `id` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `mobile` varchar(12) COLLATE utf8_persian_ci NOT NULL,
  `nation_code` varchar(10) COLLATE utf8_persian_ci DEFAULT NULL,
  `code` varchar(6) COLLATE utf8_persian_ci NOT NULL,
  `img` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `logo` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `message` text COLLATE utf8_persian_ci,
  `status` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `vlaue` varchar(200) COLLATE utf8_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `type`, `vlaue`) VALUES
(1, 0, '1591516471.jpg'),
(2, 0, '1591516481.jpg'),
(4, 0, '1591614589.png'),
(5, 2, 'پیام بالای صفحه ی اصلی اپلیکیشن سجاد خره گاو منه سوارش میشم راه میبره نمیدونی تا کجا میره');

-- --------------------------------------------------------

--
-- Table structure for table `planing`
--

CREATE TABLE `planing` (
  `id` int(11) NOT NULL,
  `title` varchar(30) COLLATE utf8_persian_ci NOT NULL,
  `parent` int(11) DEFAULT '0',
  `is_ready` tinyint(4) NOT NULL,
  `price` int(11) DEFAULT '0',
  `img` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  `is_end` int(11) DEFAULT '0',
  `is_exam` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `planing`
--

INSERT INTO `planing` (`id`, `title`, `parent`, `is_ready`, `price`, `img`, `is_end`, `is_exam`) VALUES
(3, 'هفتگی', 0, 1, 16000, '1592052876.png', 1, 0),
(4, 'آزمون', 0, 1, 25000, '1592053722.png', 0, NULL),
(5, 'گاج', 4, 1, 0, '1592054111.png', 0, NULL),
(7, 'قلمچی', 4, 1, 0, '1592054317.png', 0, NULL),
(8, 'آزمون اول', 7, 1, 30000, '1592054372.png', 1, 1),
(9, 'هفتگی', 0, 0, 0, '1592062894.jpg', 1, 0),
(11, 'آزمون', 0, 0, 0, '1592061468.jpg', 0, NULL),
(12, 'گزینه 2', 11, 0, 0, '1592061468.jpg', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plan_exam`
--

CREATE TABLE `plan_exam` (
  `id` int(11) NOT NULL,
  `planing_id` int(11) NOT NULL,
  `file` varchar(20) COLLATE utf8_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms`
--

CREATE TABLE `sms` (
  `id` int(11) NOT NULL,
  `stu_id` int(11) NOT NULL,
  `mobile` varchar(12) COLLATE utf8_persian_ci NOT NULL,
  `code` varchar(6) COLLATE utf8_persian_ci NOT NULL,
  `err` int(11) NOT NULL DEFAULT '0',
  `time_added` varchar(11) COLLATE utf8_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `sms`
--

INSERT INTO `sms` (`id`, `stu_id`, `mobile`, `code`, `err`, `time_added`) VALUES
(1, 3, '09140045125', '713925', 0, '1591444880'),
(2, 4, '8855', '855091', 0, '1591445949'),
(3, 5, '09339598315', '176220', 2, '1591446329'),
(4, 6, '09123692551', '236228', 0, '1591447295'),
(5, 7, '09100045125', '295698', 10, '1591448675'),
(6, 8, '09359598315', '341300', 0, '1591459217'),
(7, 9, '09339598316', '179470', 0, '1591459268'),
(8, 10, '09369598315', '724858', 0, '1591513303'),
(9, 11, '09339598317', '782880', 0, '1591515390'),
(10, 12, '09113692551', '973971', 0, '1591519273'),
(11, 13, '09333692551', '866251', 0, '1591519371'),
(13, 15, '09363692551', '239756', 0, '1591519523'),
(14, 16, '09359598325', '692451', 0, '1591533476'),
(15, 17, '09123692555', '709083', 0, '1592128237'),
(16, 18, '09110045125', '812116', 0, '1592132304'),
(17, 19, '09900045125', '719231', 0, '1592132539'),
(18, 20, '09650045125', '840113', 0, '1592132765'),
(19, 21, '123', '633123', 0, '1592132905'),
(20, 22, '456', '539194', 0, '1592133001'),
(21, 23, '777', '273115', 0, '1592133182'),
(22, 24, '666', '611264', 0, '1592139898'),
(23, 25, '555', '653525', 0, '1592140030'),
(24, 26, '999', '589830', 0, '1592140663'),
(25, 27, '888', '608825', 0, '1592149619'),
(26, 28, '09123698574', '824063', 5, '1592830733'),
(27, 29, '09130045125', '285090', 0, '1592910838'),
(28, 30, '09100000000', '750224', 0, '1592911775');

-- --------------------------------------------------------

--
-- Table structure for table `stu`
--

CREATE TABLE `stu` (
  `id` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_persian_ci DEFAULT NULL,
  `mobile` varchar(11) COLLATE utf8_persian_ci NOT NULL,
  `nation_code` varchar(11) COLLATE utf8_persian_ci DEFAULT NULL,
  `mosh_id` int(11) DEFAULT NULL,
  `base_id` int(11) DEFAULT NULL,
  `r_id` int(11) DEFAULT '0',
  `img` varchar(50) COLLATE utf8_persian_ci DEFAULT NULL,
  `rest` int(11) DEFAULT NULL,
  `pass` varchar(15) COLLATE utf8_persian_ci DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `time_added` varchar(12) COLLATE utf8_persian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `stu`
--

INSERT INTO `stu` (`id`, `name`, `mobile`, `nation_code`, `mosh_id`, `base_id`, `r_id`, `img`, `rest`, `pass`, `status`, `time_added`) VALUES
(2, NULL, '09120045125', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, '1591443189'),
(3, NULL, '09140045125', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, '1591444880'),
(4, NULL, '8855', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, '1591445948'),
(5, 'asdfasfasdf', '09339598315', NULL, NULL, 2, 2, NULL, NULL, '123456', 2, '1591446329'),
(6, NULL, '09123692551', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591447295'),
(7, NULL, '09100045125', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591448675'),
(8, NULL, '09359598315', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591459217'),
(9, NULL, '09339598316', NULL, NULL, 1, 2, NULL, NULL, NULL, 2, '1591459268'),
(10, 'xvhbxgbx', '09369598315', NULL, NULL, 1, 1, NULL, NULL, 'sgdfs', 2, '1591513303'),
(11, NULL, '09339598317', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591515390'),
(12, NULL, '09113692551', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591519273'),
(13, NULL, '09333692551', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591519371'),
(15, NULL, '09363692551', NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, '1591519523'),
(16, NULL, '09359598325', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, '1591533475'),
(17, '‏علی ‏غلامنژاد', '09123692555', NULL, NULL, 1, 0, NULL, NULL, '123', 2, '1592128237'),
(18, 'سجد خره', '09110045125', NULL, NULL, 1, 1, NULL, NULL, '456', 2, '1592132304'),
(19, 'سعید گاوه', '09900045125', NULL, NULL, 1, 1, NULL, NULL, '798', 2, '1592132538'),
(22, 'qwe', '456', NULL, NULL, 1, 1, NULL, NULL, '111', 2, '1592133000'),
(23, 'dfsg', '777', NULL, NULL, 1, 1, NULL, NULL, 'ddd', 2, '1592133182'),
(24, 'ddd', '666', NULL, NULL, 2, 2, NULL, NULL, '777', 2, '1592139898'),
(26, 'qqq', '999', NULL, NULL, 2, 2, NULL, NULL, 'qqq', 2, '1592140663'),
(27, 'SSS', '888', NULL, NULL, 1, 0, NULL, NULL, 'aaa', 2, '1592149619'),
(28, 'سعید ‏عبدی', '09123698574', NULL, NULL, 0, 3, NULL, NULL, '۱۱۱', 2, '1592830733'),
(29, 'sss', '09130045125', NULL, NULL, 0, 3, NULL, NULL, '222', 2, '1592910838'),
(30, 'aaa', '09100000000', NULL, NULL, 0, NULL, NULL, NULL, 'sadsa', 2, '1592911773');

-- --------------------------------------------------------

--
-- Table structure for table `weekly`
--

CREATE TABLE `weekly` (
  `id` int(11) NOT NULL,
  `stu_id` int(11) NOT NULL,
  `day` varchar(15) COLLATE utf8_persian_ci NOT NULL,
  `part` int(11) NOT NULL,
  `l_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci;

--
-- Dumping data for table `weekly`
--

INSERT INTO `weekly` (`id`, `stu_id`, `day`, `part`, `l_id`) VALUES
(1, 27, '0', 1, 8),
(2, 27, '0', 2, 4),
(3, 27, '0', 3, 9),
(4, 27, '0', 4, 10),
(5, 27, '1', 1, 9),
(6, 27, '1', 2, 7),
(7, 27, '1', 3, 8),
(8, 27, '1', 4, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `edu_plan`
--
ALTER TABLE `edu_plan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson`
--
ALTER TABLE `lesson`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mosh`
--
ALTER TABLE `mosh`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `planing`
--
ALTER TABLE `planing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plan_exam`
--
ALTER TABLE `plan_exam`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms`
--
ALTER TABLE `sms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stu`
--
ALTER TABLE `stu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weekly`
--
ALTER TABLE `weekly`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `edu_plan`
--
ALTER TABLE `edu_plan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `lesson`
--
ALTER TABLE `lesson`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mosh`
--
ALTER TABLE `mosh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `planing`
--
ALTER TABLE `planing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plan_exam`
--
ALTER TABLE `plan_exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms`
--
ALTER TABLE `sms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `stu`
--
ALTER TABLE `stu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `weekly`
--
ALTER TABLE `weekly`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
