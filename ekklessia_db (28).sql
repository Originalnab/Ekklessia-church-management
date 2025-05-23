-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2025 at 06:41 AM
-- Server version: 10.4.16-MariaDB
-- PHP Version: 7.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ekklessia_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assemblies`
--

CREATE TABLE `assemblies` (
  `assembly_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `city_town` varchar(100) NOT NULL,
  `digital_address` varchar(20) NOT NULL,
  `nearest_landmark` varchar(255) DEFAULT NULL,
  `date_started` date DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `assemblies`
--

INSERT INTO `assemblies` (`assembly_id`, `zone_id`, `region`, `city_town`, `digital_address`, `nearest_landmark`, `date_started`, `name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(2, 4, 'Ashanti', 'Kumasi', 'GH-123-4324-32', 'Near Ai Hospital', '2024-02-15', 'Marvellous Church ', 'Second assembly in Zone 1', 1, '2025-03-17 07:02:16', '2025-03-17 08:14:38', 'Admin', NULL),
(3, 4, 'Ashanti', 'Kumasi', 'GH-123-4324-32', 'Near A1Hospital', '2022-08-05', 'Glorious Church ', 'First assembly in Zone 2', 1, '2025-03-17 07:02:16', '2025-03-17 08:13:49', 'Admin', NULL),
(4, 3, 'Central', 'Winneba ', 'GH-322-413-23', 'Near Main Market ', '2017-06-01', 'Word Expresss', 'First assembly in Zone 3', 1, '2025-03-17 07:02:16', '2025-03-17 08:12:46', 'Admin', NULL),
(5, 5, 'Greater Accra', 'Acccra', 'GW-0738-663', 'Opposite American Bar ', '2016-01-01', 'Word Embassy ', 'First assembly in Zone 4', 1, '2025-03-17 07:02:16', '2025-03-17 08:11:43', 'Admin', NULL),
(6, 5, 'Greater Accra', 'Asuotware Shia Hills ', 'GH-123-4324-32', 'Sun City Estate AND Novtra Esta', '2023-05-01', 'Shai Hills ', NULL, 1, '2025-03-17 08:52:13', NULL, 'Admin', NULL),
(7, 4, 'Ashanti', 'Offinso ', 'GW-0738-663', 'Near Main Market ', '2025-02-01', 'Offinso Assmbly ', NULL, 1, '2025-03-17 08:53:17', NULL, 'Admin', NULL),
(8, 5, 'Central', 'Overseas ', 'PLT 260 ABUAKWA KOFO', 'Near Ai Hospital', '2025-01-01', 'Virtual Assembly ', NULL, 1, '2025-03-30 17:31:11', NULL, 'Admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assembly_assignments`
--

CREATE TABLE `assembly_assignments` (
  `assignment_id` int(11) NOT NULL,
  `assembly_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `role_type` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `church_functions`
--

CREATE TABLE `church_functions` (
  `function_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `function_name` varchar(50) NOT NULL,
  `function_type` enum('local','national') NOT NULL,
  `description` text DEFAULT NULL,
  `is_exclusive` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `scope_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `church_functions`
--

INSERT INTO `church_functions` (`function_id`, `role_id`, `function_name`, `function_type`, `description`, `is_exclusive`, `created_at`, `updated_at`, `created_by`, `updated_by`, `scope_id`) VALUES
(1, 3, 'TPD Director', 'national', 'Leads TPD at national level', 0, '2025-03-17 07:02:16', '2025-04-05 19:33:30', 'Admin', NULL, 4),
(2, 3, 'Assistant TPD Director', 'national', 'Assists TPD Director', 0, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 4),
(3, 2, 'PED Director', 'national', 'Leads PED at national level', 0, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 4),
(4, 2, 'PED Worker', 'national', 'Supports PED activities', 0, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 4),
(5, 3, 'TPD Worker', 'national', 'Supports TPD activities', 0, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 4),
(6, 1, 'President', 'national', 'EXCO head', 0, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 4),
(7, 1, 'Chief Finance Officer', 'national', 'Oversees finance', 0, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 4),
(8, 3, 'Presiding Elder', 'local', 'TPD leader at local assembly', 1, '2025-03-17 07:02:16', '2025-04-05 19:33:30', 'Admin', NULL, 2),
(9, 3, 'Assistant Presiding Elder', 'local', 'Assists Presiding Elder', 0, '2025-03-17 07:02:16', '2025-04-05 19:33:30', 'Admin', NULL, 2),
(10, 3, 'Elder', 'local', 'TPD elder at local assembly', 1, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 2),
(11, 3, 'Shepherd', 'local', 'Guides saints', 1, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 1),
(12, 3, 'Saint', 'local', 'Base member', 1, '2025-03-17 07:02:16', '2025-04-05 19:38:40', 'Admin', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `level` enum('household','assembly','zone','national') NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `assembly_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `frequency` enum('daily','weekly','monthly','quarterly','yearly') DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `recurrence_day` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') DEFAULT NULL
) ;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_type_id`, `title`, `description`, `start_date`, `end_date`, `start_time`, `duration`, `location`, `level`, `household_id`, `assembly_id`, `zone_id`, `is_recurring`, `frequency`, `created_by`, `created_at`, `updated_at`, `recurrence_day`) VALUES
(1, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 3, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(2, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 2, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(3, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 7, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(4, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 6, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(5, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 8, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(6, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 5, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(7, 2, 'Sunday Service', '', NULL, NULL, '08:30:00', 240, '', 'assembly', NULL, 4, NULL, 1, 'weekly', 62, '2025-04-01 12:12:17', NULL, 'Sunday'),
(8, 6, 'Shai hills Household Outreach ', 'Shai Hills outreach household level ', NULL, NULL, '08:30:00', 90, 'Shai hills ', 'household', 11, NULL, NULL, 1, 'weekly', 62, '2025-04-01 12:53:07', NULL, ''),
(9, 7, 'Glorious Church Outreach', 'GC Outreach', '2025-04-05 09:00:00', '2025-04-05 09:30:00', '09:00:00', 30, 'Glorious church', 'assembly', NULL, 3, NULL, 1, 'monthly', 62, '2025-04-01 13:37:49', NULL, 'Saturday'),
(10, 6, 'Outreach Dede\'s Household', 'Dede\'s household outreach', '2025-04-06 15:30:00', '2025-04-06 17:48:00', '15:30:00', 138, '', 'household', 5, NULL, NULL, 0, NULL, 62, '2025-04-01 13:49:44', NULL, NULL),
(11, 4, 'GC ALL NIGHT', 'GC ALL NIGHT GC', '2025-04-11 17:45:00', '2025-04-12 05:46:00', '17:45:00', 721, '', 'assembly', NULL, 3, NULL, 0, NULL, 62, '2025-04-02 16:48:47', NULL, NULL),
(12, 4, 'Local Assembly outreach', 'Word Embassy assembly outreach', '2025-04-03 07:00:00', '2025-04-03 08:00:00', '07:00:00', 60, 'Word Embassy', 'assembly', NULL, 5, NULL, 0, NULL, 62, '2025-04-02 17:06:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events_old`
--

CREATE TABLE `events_old` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_level` enum('National','Zonal','Assembly','Household') NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `assembly_id` int(11) DEFAULT NULL,
  `household_id` int(11) DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `recurrence_type` enum('Daily','Weekly','Monthly') DEFAULT NULL,
  `recurrence_interval` int(11) DEFAULT 1,
  `recurrence_day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
  `recurrence_end_date` date DEFAULT NULL,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_attendance`
--

CREATE TABLE `event_attendance` (
  `attendance_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `instance_id` int(11) DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `status` enum('present','absent','excused') NOT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_instances`
--

CREATE TABLE `event_instances` (
  `instance_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `instance_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_instances_old`
--

CREATE TABLE `event_instances_old` (
  `instance_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `instance_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_rescheduled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE `event_types` (
  `event_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `default_frequency` enum('weekly','monthly','quarterly','yearly','none') DEFAULT 'none',
  `level` enum('household','assembly','zone','national') NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event_types`
--

INSERT INTO `event_types` (`event_type_id`, `name`, `description`, `default_frequency`, `level`, `is_recurring`, `created_at`, `updated_at`) VALUES
(1, 'Household Meeting ', 'assembly meetings in household ', 'weekly', 'household', 1, '2025-03-30 06:33:13', '2025-03-30 06:33:13'),
(2, 'Sunday Lords Day Meeting ', 'Meets every Sunday for temple meetings ', 'weekly', 'assembly', 1, '2025-03-30 10:51:58', '2025-03-30 10:51:58'),
(3, 'Midweek Prayer Meeting ', 'mid week prayer meeting for prayer temple gathering ', 'weekly', 'assembly', 1, '2025-03-30 10:53:20', '2025-03-30 10:53:20'),
(4, 'Assembly All Night', 'all night meetings ', '', 'assembly', 0, '2025-03-30 10:54:35', '2025-03-30 10:54:35'),
(5, 'Zonal Answers To Prayers Conference ', 'prayer gathering ', '', 'zone', 0, '2025-03-30 10:56:05', '2025-03-30 10:56:05'),
(6, 'Household outreach  ', 'evangelism at the household level', 'weekly', 'household', 0, '2025-03-30 10:57:32', '2025-03-30 10:57:32'),
(7, 'Assembly Outreach ', 'assembly level outreach ', 'monthly', 'assembly', 1, '2025-03-30 10:58:36', '2025-03-30 10:58:36'),
(8, 'Christ Conference ', 'Christ conference meeting ', 'yearly', 'national', 0, '2025-03-31 10:10:11', '2025-03-31 10:10:11'),
(9, 'Test Event Type', NULL, 'none', 'national', 0, '2025-04-01 09:22:27', '2025-04-01 09:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `household_id` int(11) NOT NULL,
  `assembly_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `digital_address` varchar(20) NOT NULL,
  `nearest_landmark` varchar(255) DEFAULT NULL,
  `date_started` date DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`household_id`, `assembly_id`, `address`, `digital_address`, `nearest_landmark`, `date_started`, `name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(3, 6, 'Agomeda Town ', 'GH-123-4324-32', 'Near Main Market ', '2024-10-01', 'Agomeda  Household', 'First household in Assembly 2', 1, '2025-03-17 07:02:16', '2025-03-17 08:54:59', 'Admin', 'Admin'),
(4, 2, 'Jessica\'s house ', 'GH-322-413-23', 'Near Ai Hospital', '2025-02-17', 'Jessy\'s household ', 'Second household in Assembly 2', 1, '2025-03-17 07:02:16', '2025-03-17 08:49:39', 'Admin', 'Admin'),
(5, 3, 'Koftwoon ', 'GW-0738-663', 'Near the station', '2025-03-16', 'Dede\'s Husehold', 'First household in Assembly 3', 1, '2025-03-17 07:02:16', '2025-03-17 08:57:19', 'Admin', 'Admin'),
(6, 4, 'Zima\'s house ', 'GH-123-4324-32', 'Near Main Market ', '2025-03-17', 'ZIma\'s household ', 'First household in Assembly 4', 1, '2025-03-17 07:02:16', '2025-03-17 08:57:01', 'Admin', 'Admin'),
(7, 5, 'ofankor Barrier ', 'GH-123-4324-32', 'ofankor station ', '2023-05-04', 'Ofankor', 'First household in Assembly 5', 1, '2025-03-17 07:02:16', '2025-03-20 19:00:51', 'Admin', 'Admin'),
(8, 7, 'Offinso ', 'GH-322-413-23', 'Near Main Market ', '2025-02-01', 'Offinso Household', NULL, 1, '2025-03-17 08:56:34', '2025-03-17 08:56:34', 'Admin', 'Admin'),
(9, 6, 'Gbestele ', 'GW-0738-663', 'Near Main Market ', '2023-08-08', 'Gbestele ', NULL, 1, '2025-03-18 21:25:34', '2025-03-18 21:25:34', 'Admin', 'Admin'),
(10, 3, 'PLT 260 ABUAKWA KOFORIDUA', 'GH-123-4324-32', 'Near Main Market ', '2024-07-26', 'Bishop Thaddeus houshold ', NULL, 1, '2025-03-26 10:39:09', '2025-03-26 10:39:09', 'Admin', 'Admin'),
(11, 6, 'PLT 260 ABUAKWA KOFORIDUA', 'GW-0738-663', 'Near Main Market ', '2023-05-01', 'Shai Hills household ', NULL, 1, '2025-03-26 10:40:37', '2025-03-26 10:40:37', 'Admin', 'Admin'),
(12, 5, 'PLT 260 ABUAKWA KOFORIDUA', 'GH-322-413-23', 'Near Main Market ', '2024-06-14', 'Kwabenja ', NULL, 1, '2025-03-28 18:44:43', '2025-03-28 18:44:43', 'Admin', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `household_assistant_assignments`
--

CREATE TABLE `household_assistant_assignments` (
  `id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `assistant_member_id` int(11) NOT NULL,
  `assigned_by` varchar(255) NOT NULL,
  `assigned_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `household_assistant_assignments`
--

INSERT INTO `household_assistant_assignments` (`id`, `household_id`, `assistant_member_id`, `assigned_by`, `assigned_at`, `updated_at`) VALUES
(3, 4, 21, 'System', '2025-03-25 19:10:21', '2025-03-25 19:10:21'),
(4, 9, 30, 'System', '2025-03-25 19:26:14', '2025-03-25 19:26:14'),
(5, 8, 26, 'System', '2025-03-25 19:39:31', '2025-03-25 19:39:31'),
(10, 5, 9, 'System', '2025-03-25 19:59:04', '2025-03-25 19:59:04'),
(11, 5, 31, 'System', '2025-03-25 19:59:04', '2025-03-25 19:59:04'),
(12, 12, 71, 'System', '2025-03-28 19:48:36', '2025-03-28 19:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `household_shepherdhead_assignments`
--

CREATE TABLE `household_shepherdhead_assignments` (
  `assignment_id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `shepherd_member_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `household_shepherdhead_assignments`
--

INSERT INTO `household_shepherdhead_assignments` (`assignment_id`, `household_id`, `shepherd_member_id`, `assigned_at`, `assigned_by`, `updated_at`) VALUES
(1, 9, 16, '2025-03-25 18:26:14', 'System', '2025-03-25 18:26:14'),
(2, 7, 13, '2025-03-24 15:24:53', 'Admin', '2025-03-24 15:24:53'),
(3, 5, 33, '2025-03-25 18:59:04', 'System', '2025-03-25 18:59:04'),
(4, 4, 20, '2025-03-25 18:10:21', 'System', '2025-03-25 18:10:21'),
(5, 8, 3, '2025-03-25 18:39:31', 'System', '2025-03-25 18:39:31'),
(6, 12, 8, '2025-03-28 18:48:36', 'System', '2025-03-28 18:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') NOT NULL,
  `contact` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `digital_address` varchar(20) NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `employer` varchar(100) DEFAULT NULL,
  `work_phone` varchar(20) DEFAULT NULL,
  `highest_education_level` enum('None','Primary','Secondary','Diploma','Bachelor','Master','Doctorate') DEFAULT NULL,
  `institution` varchar(100) DEFAULT NULL,
  `year_graduated` int(11) DEFAULT NULL,
  `status` enum('Committed saint','Active saint','Worker','New saint') NOT NULL,
  `joined_date` date NOT NULL,
  `assemblies_id` int(11) NOT NULL,
  `local_function_id` int(11) NOT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `group_name` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `marital_status`, `contact`, `email`, `address`, `profile_photo`, `digital_address`, `occupation`, `employer`, `work_phone`, `highest_education_level`, `institution`, `year_graduated`, `status`, `joined_date`, `assemblies_id`, `local_function_id`, `referral_id`, `group_name`, `username`, `password`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Jay', 'Kakra', '2023-07-01', 'Male', 'Single', '0249484744', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '123525983_354702239138901_7876415139572633753_n.jpg', 'GW-0738-663', 'Software Engineering', 'Self Employed', '0249484744', 'Diploma', 'IPMC', 2018, 'Active saint', '2024-12-26', 6, 8, 20, 'Adult Ministry', 'jaykak463', '$2y$10$OAZ.j0W2G3L77oXgvicOMeQNw/ZnoSwhZBt9lcmF5RgmPuRjilc8m', '2025-03-17 10:23:40', '2025-03-26 13:07:17', 'Admin', 'Admin'),
(3, 'Gideon', 'Opoku', '2025-03-01', 'Male', 'Married', '0507172337', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', 'gideon.jpg', 'GH-123-4324-32', 'Consultant', 'Self Employed', '0507172337', NULL, 'UDSD', 2017, 'Committed saint', '2025-03-11', 7, 8, 1, 'Adult Ministry', 'gidopo679', '$2y$10$ySaV0e8W3KWFGWOxYtf6een6PVHJtY73TvH7meB8pwNZuIzB54cW6', '2025-03-17 10:26:42', '2025-03-25 17:36:15', 'Admin', 'Admin'),
(4, 'Agyeman ', 'E Atta Senior ', '2025-03-07', 'Male', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '67d8009725281_senior.jpg', 'GW-0738-663', 'Engineer ', 'Wahu Mobility ', '0540912097', '', 'KNUST ', 2023, 'Active saint', '2024-11-08', 5, 8, NULL, NULL, 'agye a631', '$2y$10$03cPJuS0L0y4wzjkuA.bqejxMlNyCJLAZzyrXRMrnay.xfHbaWD0.', '2025-03-17 10:59:35', '2025-03-18 06:35:11', 'Admin', NULL),
(8, 'Princess  Arita', 'Annim', '2024-10-31', 'Male', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', 'arita.jpg', 'GW-0738-663', 'Journalist', 'Arita Globe', '0540912097', NULL, 'Leagon', 2020, 'Active saint', '2025-03-07', 5, 10, 4, 'Adult Ministry', 'arita', '$2y$10$sS805qOvBOPiXHS6Cp/gAOV5G9/psbNM9e05Yi05dny/.Nv..9ox6', '2025-03-17 13:34:53', '2025-03-28 18:28:10', 'Admin', 'Admin'),
(9, 'Shadrach', 'Danquah', '2025-03-06', 'Male', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', 'shaddy.jpg', 'GH-123-4324-32', 'Manager', 'Kingsperp', '0249484744', NULL, 'UDSD', 2019, 'Worker', '1995-09-02', 3, 10, 3, 'Adult Ministry', 's6911', 'Sha7202', '2025-03-17 13:44:01', '2025-03-19 21:11:57', 'Admin', 'Admin'),
(10, 'Gifty ', 'Asekapta', '2025-02-28', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', 'gifty.jpg', 'GW-0738-663', 'Nurse', 'Offinso Hospital ', '0540912097', 'Diploma', 'UDSD', 2017, 'New saint', '2025-03-17', 7, 10, NULL, NULL, 'g5942', 'Gif2408', '2025-03-17 14:32:57', NULL, 'Admin', NULL),
(11, 'Benjamin ', 'Edwards ', '2023-01-01', 'Male', 'Single', '0249484744', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '60481682_2706921486200768_1337766640644784128_n.jpg', 'GW-0738-663', 'Business Man', 'Self Employed ', '0540912097', '', 'University of Winneba', 2017, 'Active saint', '2025-03-12', 4, 8, NULL, NULL, 'zima', '$2y$10$/Z3vY8/ec29Ujtr9rl4QR.RaIshC1vJAH8rLuFKF04B0QqAmXyXLK', '2025-03-18 10:39:37', '2025-03-28 10:50:27', 'Admin', NULL),
(12, 'Richard ', 'Opoku', '2021-02-18', 'Male', 'Married', '0249484744', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '472709324_604447905466269_7470505180045480800_n.jpg', 'GW-0738-663', 'Teacher ', 'Government ', '0540912097', '', 'University of wenniba ', 2025, 'Active saint', '2025-03-01', 4, 9, NULL, NULL, 'r6280', 'Ric3030', '2025-03-18 10:48:36', '2025-03-18 14:03:54', 'Admin', NULL),
(13, 'Marie ', 'Rechel Debgor', '2021-07-03', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '416039882_773344904820968_7598996711205567339_n.jpg', 'GH-123-4324-32', 'Graphic Designer ', 'EPM', '0540912097', '', 'ATU', 2024, 'Committed saint', '2022-10-20', 5, 11, 9, 'Adult Ministry', 'm1240', 'Mar8163', '2025-03-18 11:02:23', '2025-03-19 20:41:38', 'Admin', 'Admin'),
(15, 'Henry ', 'Apenteng ', '2017-08-01', 'Male', 'Single', '0507172337', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '355907975_3268059790005549_722939374745671412_n.jpg', 'GW-0738-663', 'Nurse', 'Government ', '0540912097', '', 'UDSD Tamale ', 2023, 'Active saint', '2024-07-02', 6, 9, 1, 'Adult Ministry', 'h9237', 'Hen9309', '2025-03-18 11:20:54', '2025-03-19 14:18:31', 'Admin', 'Admin'),
(16, 'Genevieve ', 'Amponsah ', '2021-02-02', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '415992187_773346728154119_3217201142420798472_n.jpg', 'GW-0738-663', 'Accountant ', 'GIPS ', '0540912097', '', 'UDSD NAVRONGO ', 2020, 'Committed saint', '2024-12-05', 6, 11, 3, 'Adult Ministry', 'g4793', 'Gen8756', '2025-03-18 21:28:16', '2025-03-19 11:39:52', 'Admin', 'Admin'),
(17, 'Emmanuel', 'S Kwampong', '2025-01-11', 'Male', 'Single', '0249484744', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '415996449_773348074820651_9060012531449618014_n.jpg', 'GW-0738-663', 'Nurse', 'Government', '0540912097', NULL, 'UCC', 2019, 'Committed saint', '2024-12-12', 3, 8, 3, 'Adult Ministry', 'e9570', 'Emm1253', '2025-03-19 09:10:25', '2025-03-24 18:35:03', 'Admin', 'Admin'),
(20, 'Joshua', 'Ezekiel  Nyame', '2021-02-05', 'Male', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '416028628_773344588154333_2537894643934933626_n (1).jpg', 'GW-0738-663', 'Priest', 'God', '0540912097', NULL, 'UDSD', 2013, 'Committed saint', '2015-02-19', 2, 10, NULL, 'Adult Ministry', 'j6696', 'Jos7568', '2025-03-19 22:08:08', '2025-03-19 22:08:51', 'Admin', 'Admin'),
(21, 'Deborah', 'Nyarko', '2022-07-01', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '67db4287ead6b.jpg', 'GH-123-4324-32', 'Nurse', 'Government', '0540912097', 'Diploma', 'NMTC', 2024, 'Committed saint', '2025-03-19', 2, 11, 1, NULL, 'd1389', 'Deb9541', '2025-03-19 22:16:10', '2025-03-19 22:17:43', 'Admin', 'Admin'),
(26, 'James ', 'Akiti', '2025-02-26', 'Male', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '415977705_772650841557041_2341200343007589749_n.jpg', 'GW-0738-663', 'IT', 'GG', '0540912097', '', 'IPMC ', 2022, 'Committed saint', '2025-03-08', 7, 12, 15, '', 'j6405', 'Jam5542', '2025-03-20 00:09:13', NULL, 'Admin', NULL),
(30, 'Gifty', 'Nyavor', '2024-11-28', 'Female', 'Married', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '67e7d3fdce60e.jpg', 'GH-123-4324-32', 'entrepreneur', 'Agri haven', '0540912097', NULL, 'KNUST', 2019, 'Committed saint', '2025-01-04', 6, 11, 15, 'Adult Ministry', 'gifty2025', '$2y$10$y3FRjeCYjbkXkQE0082LO.LG9PDk9zBQZtZxStyWLgu99uAdsIJZa', '2025-03-24 14:54:26', '2025-03-29 11:05:33', 'Admin', 'Admin'),
(31, 'Samuel Odame', 'Addo', '2024-12-06', 'Male', 'Married', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '67e1b05056583.jpg', 'GH-123-4324-32', 'Teacher', 'GES', '0540912097', NULL, 'KNUST', 2017, 'Committed saint', '2025-03-01', 3, 10, 20, 'Adult Ministry', 'o5646', 'Oda4729', '2025-03-24 18:33:11', '2025-03-24 19:20:31', 'Admin', 'Admin'),
(32, 'Abigail ', 'Mahama', '2024-12-04', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '94196392_643513083164609_1327264225835352064_n.jpg', 'GH-322-413-23', 'Fashion Designer ', '0507172337', '0540912097', '', 'UDSD', 2021, 'Committed saint', '2024-10-31', 6, 11, 1, 'Children\'s Ministry', 'a1584', 'Abi5890', '2025-03-24 18:44:29', NULL, 'Admin', NULL),
(33, 'Dede Gifty ', 'Agyie ', '2024-11-28', 'Female', 'Single', '0249484744', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '272435848_7105596976179098_2368052529194325692_n.jpg', 'GH-322-413-23', 'Teacher ', 'KNUST ', '0540912097', '', 'UCC', 2018, 'Committed saint', '2024-12-13', 3, 10, 3, 'Adult Ministry', 'd1293', 'Ded2550', '2025-03-25 09:19:53', NULL, 'Admin', NULL),
(34, 'Constance ', 'Adu Mensah ', '2024-11-07', 'Female', 'Married', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', '295965440_608121494364633_748532744197972485_n.jpg', 'GW-0738-663', 'Nurse', 'Government ', '0540912097', 'Diploma', 'NMTC ', 2020, 'Worker', '2025-01-04', 3, 11, 33, 'Adult Ministry', 'c2507', 'Con6072', '2025-03-25 09:33:47', NULL, 'Admin', NULL),
(35, 'Vannesa', 'Kokor', '2025-01-02', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', 'achievement-african-american-business-business-people.jpg', 'GH-123-4324-32', 'student', NULL, NULL, 'Primary', 'Grace International', 2018, 'Active saint', '2024-12-06', 6, 12, 1, 'Adult Ministry', 'v7346', 'Van3033', '2025-03-25 17:29:31', '2025-03-25 17:35:06', 'Admin', 'Admin'),
(36, 'Vannela ', 'Korkor', '2024-10-31', 'Female', 'Single', '0540912097', 'elkkakra@gmail.com', 'PLT 260 ABUAKWA KOFORIDUA', 'images (4).jfif', 'GH-123-4324-32', 'student ', '', '', 'Primary', 'GIPS', 2019, 'Committed saint', '2024-11-30', 6, 12, 1, 'Adult Ministry', 'v7359', 'Van1661', '2025-03-25 17:32:58', NULL, 'Admin', NULL),
(61, 'Kwaku', 'Shai', '1990-05-12', 'Male', 'Married', '0244123456', 'kwaku.shai@gmail.com', 'P.O. Box 123, Accra', '67e3cde42c420.jpg', 'GA-123-4567', 'Teacher', 'Ghana Education Service', '0209876543', NULL, 'University of Ghana', 2014, 'Active saint', '2025-03-26', 3, 12, NULL, NULL, 'Kwi468', 'Kwi397', '2025-03-26 09:49:46', '2025-03-26 09:50:28', 'System', 'Admin'),
(62, 'Godfred', 'Kwaakye', '1985-08-20', 'Male', 'Single', '0556789012', 'godfred.kwaakye@yahoo.com', '45 Spintex Road, Accra', '67e91a3d7e627.jpg', 'GA-789-0123', 'Accountant', 'FirstBank Ghana', '0543210987', NULL, 'University of Cape Coast', 2010, 'Committed saint', '2025-03-26', 3, 8, 10, 'Adult Ministry', 'iGOD', '$2y$10$/Q.FdaoGHKzffsICpXjLk.nbIu9.fg.gNugS9k9Dh7PvIEkiGIEFC', '2025-03-26 09:49:46', '2025-03-30 10:17:33', 'System', 'Admin'),
(63, 'Isaac', 'Deodu', '1992-03-15', 'Male', 'Married', '0265432109', 'isaac.deodu@outlook.com', '12 Kanda Highway, Accra', '67e3ce920ffc8.jpeg', 'GA-456-7890', 'Nurse', 'Korle Bu Teaching Hospital', '0578901234', 'Diploma', 'Ghana Nursing College', 2016, 'Worker', '2025-03-26', 6, 12, NULL, NULL, 'Isu439', 'Isu470', '2025-03-26 09:49:46', '2025-03-26 10:45:55', 'System', 'Admin'),
(64, 'Abena', 'Ofori', '1995-11-25', 'Female', 'Single', '0501234567', 'abena.ofori@gmail.com', 'Block 5, Kumasi', '67e3cf3165408.jpg', 'AK-234-5678', 'Pharmacist', 'PharmaTrust Ltd', '0534567890', NULL, 'KNUST', 2018, 'Worker', '2025-03-26', 5, 12, NULL, NULL, 'Abi421', 'Abi428', '2025-03-26 09:49:46', '2025-03-26 10:46:13', 'System', 'Admin'),
(65, 'Kofi', 'Asante', '1988-07-30', 'Male', 'Divorced', '0277890123', 'kofi.asante@hotmail.com', '10 Ashanti Road, Kumasi', '67e3cedbc035b.jpg', 'AK-567-8901', 'Engineer', 'Asante Construction', '0556789012', NULL, 'KNUST', 2012, 'Active saint', '2025-03-26', 3, 12, NULL, NULL, 'Koe060', 'Koe450', '2025-03-26 09:49:46', '2025-03-26 09:54:35', 'System', 'Admin'),
(66, 'Akosua', 'Mensah', '1993-02-14', 'Female', 'Married', '0249876543', 'akosua.mensah@gmail.com', '15 Adum Street, Kumasi', '67e3cf75b5775.jpg', 'AK-890-1234', 'Trader', 'Self-Employed', '0201234567', 'Secondary', 'Adum Senior High', 2010, 'Committed saint', '2025-03-26', 5, 12, NULL, NULL, 'Akh697', 'Akh045', '2025-03-26 09:49:46', '2025-03-26 10:46:31', 'System', 'Admin'),
(67, 'Yaw', 'Boakye', '1990-09-10', 'Male', 'Single', '0552345678', 'yaw.boakye@yahoo.com', '20 Tamale Road, Tamale', '67e3cf598aad8.jpg', 'NR-123-4567', 'Farmer', 'Boakye Farms', '0547890123', 'Secondary', 'Tamale Senior High', 2008, 'Worker', '2025-03-26', 4, 12, 12, 'Adult Ministry', 'Yae505', 'Yae820', '2025-03-26 09:49:46', '2025-03-26 12:42:04', 'System', 'Admin'),
(68, 'Adwoa', 'Nyarko', '1987-12-05', 'Female', 'Widowed', '0266789012', 'adwoa.nyarko@outlook.com', '8 Cape Coast Avenue, Cape Coast', '67e3cf90e051d.jpg', 'CR-456-7890', 'Librarian', 'University of Cape Coast Library', '0571234567', NULL, 'University of Cape Coast', 2015, 'Active saint', '2025-03-26', 2, 12, NULL, NULL, 'Ado206', 'Ado500', '2025-03-26 09:49:46', '2025-03-26 10:47:05', 'System', 'Admin'),
(69, 'Kojo', 'Annan', '1994-06-18', 'Male', 'Married', '0507890123', 'kojo.annan@gmail.com', '25 Takoradi Street, Takoradi', '67e3cfa5dd48f.jpg', 'WR-789-0123', 'Mechanic', 'Annan Auto Works', '0532345678', NULL, 'Takoradi Technical Institute', 2016, 'Worker', '2025-03-26', 3, 12, NULL, NULL, 'Kon258', 'Kon297', '2025-03-26 09:49:46', '2025-03-26 09:57:57', 'System', 'Admin'),
(70, 'Esi', 'Darko', '1991-04-22', 'Female', 'Single', '0271234567', 'esi.darko@yahoo.com', '30 East Legon, Accra', '67e3d0a0a46f6.jpg', 'GA-012-3456', 'Software Developer', 'Tech Innovations Ghana', '0555678901', NULL, 'Ashesi University', 2013, 'Committed saint', '2025-03-26', 5, 12, NULL, NULL, 'Eso011', 'Eso997', '2025-03-26 09:49:46', '2025-03-26 10:47:17', 'System', 'Admin'),
(71, 'Kwame', 'Osei', '1989-01-10', 'Male', 'Married', '0244000001', 'kwame.osei@gmail.com', 'P.O. Box 456, Accra', '67e67b1e73841.jpg', 'GA-111-2222', 'Doctor', 'Korle Bu Teaching Hospital', '0209000001', NULL, 'University of Ghana', 2013, 'Active saint', '2025-03-26', 5, 12, NULL, 'Adult Ministry', 'Kwi397', 'Kwi724', '2025-03-26 13:22:57', '2025-03-28 19:18:35', 'System', 'Admin'),
(72, 'Ama', 'Boateng', '1993-05-22', 'Female', 'Single', '0556000002', 'ama.boateng@yahoo.com', 'East Legon, Accra', '67e67b0f40416.jpg', 'GA-333-4444', 'Lawyer', 'Legal Aid Ghana', '0543000002', NULL, 'KNUST', 2017, 'Committed saint', '2025-03-26', 3, 12, 16, 'Children\'s Ministry', 'Amg203', 'Amg784', '2025-03-26 13:22:57', '2025-03-28 10:33:51', 'System', 'Admin'),
(73, 'Joseph', 'Mensah', '1986-11-15', 'Male', 'Married', '0265000003', 'joseph.mensah@outlook.com', 'Adabraka, Accra', '67e67af93caed.jpg', 'GA-555-6666', 'Banker', 'Ecobank Ghana', '0578000003', NULL, 'University of Cape Coast', 2012, 'Worker', '2025-03-26', 4, 12, 8, 'Children\'s Ministry', 'Joh588', 'Joh291', '2025-03-26 13:22:57', '2025-03-28 10:33:29', 'System', 'Admin'),
(74, 'Mavis', 'Agyeman', '1994-08-30', 'Female', 'Single', '0502000004', 'mavis.agyeman@gmail.com', 'Asokwa, Kumasi', '67e67a0cc851c.jpg', 'AK-777-8888', 'Nurse', 'Komfo Anokye Teaching Hospital', '0536000004', 'Diploma', 'Ghana Nursing College', 2019, 'Active saint', '2025-03-26', 5, 12, NULL, 'Children\'s Ministry', 'Man207', 'Man309', '2025-03-26 13:22:57', '2025-03-28 19:19:45', 'System', 'Admin'),
(75, 'Michael', 'Owusu', '1990-06-18', 'Male', 'Divorced', '0279000005', 'michael.owusu@hotmail.com', 'Sofoline, Kumasi', '67e679f38a4d3.jpg', 'AK-999-0000', 'Engineer', 'Owusu Engineering Ltd', '0557000005', NULL, 'KNUST', 2011, 'Active saint', '2025-03-26', 6, 12, 32, 'Children\'s Ministry', 'Miu969', 'Miu086', '2025-03-26 13:22:57', '2025-03-28 10:29:07', 'System', 'Admin'),
(76, 'Sandra', 'Opoku', '1992-12-14', 'Female', 'Married', '0246000006', 'sandra.opoku@gmail.com', 'Tema Community 5', '67e679d6d2135.jpg', 'TG-123-4567', 'Teacher', 'Ghana Education Service', '0203000006', NULL, 'University of Ghana', 2016, 'Committed saint', '2025-03-26', 5, 12, NULL, 'Children\'s Ministry', 'Sau508', 'Sau813', '2025-03-26 13:22:57', '2025-03-28 19:18:59', 'System', 'Admin'),
(77, 'Yaw', 'Dapaah', '1991-09-25', 'Male', 'Single', '0559000007', 'yaw.dapaah@yahoo.com', 'Cape Coast', '67e679c722177.jpg', 'CR-234-5678', 'Lecturer', 'University of Cape Coast', '0544000007', NULL, 'University of Cape Coast', 2021, 'Worker', '2025-03-26', 7, 12, 34, 'Children\'s Ministry', 'Yah113', 'Yah478', '2025-03-26 13:22:57', '2025-03-28 10:28:23', 'System', 'Admin'),
(78, 'Akua', 'Frimpong', '1988-07-05', 'Female', 'Widowed', '0263000008', 'akua.frimpong@outlook.com', 'Koforidua', '67e679b736027.jpg', 'ER-345-6789', 'Trader', 'Self-Employed', '0579000008', 'Secondary', 'Koforidua Senior High', 2005, 'Active saint', '2025-03-26', 2, 12, 21, 'Children\'s Ministry', 'Akg537', 'Akg670', '2025-03-26 13:22:57', '2025-03-28 10:28:07', 'System', 'Admin'),
(79, 'Nana', 'Addo', '1995-04-22', 'Male', 'Married', '0505000009', 'nana.addo@gmail.com', 'Takoradi', '67e639f0bd08d.jpg', 'WR-456-7890', 'Mechanic', 'Addo Auto Works', '0531000009', NULL, 'Takoradi Technical Institute', 2018, 'Committed saint', '2025-03-26', 3, 12, 9, 'Children\'s Ministry', 'Nao374', 'Nao878', '2025-03-26 13:22:57', '2025-03-28 05:56:00', 'System', 'Admin'),
(80, 'Esi', 'Baah', '1987-03-12', 'Female', 'Single', '0271000010', 'esi.baah@yahoo.com', 'Bolgatanga', '67e639df596c7.jpg', 'UE-567-8901', 'Software Developer', 'TechHub Ghana', '0559000010', NULL, 'Ashesi University', 2010, 'Committed saint', '2025-03-26', 5, 12, NULL, 'Adult Ministry', 'Esh021', 'Esh727', '2025-03-26 13:22:57', '2025-03-28 19:19:16', 'System', 'Admin'),
(81, 'Samuel', 'Twum', '1996-10-01', 'Male', 'Single', '0247000011', 'samuel.twum@gmail.com', 'Tamale', '67e837c1611bd.jpg', 'NR-678-9012', 'Pilot', 'Ghana Airways', '0204000011', NULL, 'Ghana Aviation School', 2020, 'Worker', '2025-03-26', 3, 12, 62, 'Children\'s Ministry', 'Sam130', 'Sam116', '2025-03-26 13:22:57', '2025-03-29 18:11:13', 'System', 'Admin'),
(82, 'Patience', 'Korankye', '1990-12-29', 'Female', 'Married', '0558000012', 'patience.korankye@outlook.com', 'Ho', '67e6965a4936a.jpg', 'VR-789-0123', 'Pharmacist', 'Health First Pharmacy', '0545000012', NULL, 'KNUST', 2014, 'Committed saint', '2025-03-26', 7, 12, 3, 'Children\'s Ministry', 'Pae208', 'Pae001', '2025-03-26 13:22:57', '2025-03-28 12:30:18', 'System', 'Admin'),
(83, 'George', 'Asiedu', '1985-06-11', 'Male', 'Divorced', '0268000013', 'george.asiedu@gmail.com', 'Sunyani', '67e7291f0f905.jpg', 'BA-890-1234', 'Accountant', 'GCB Bank', '0576000013', NULL, 'University of Ghana', 2009, 'Active saint', '2025-03-26', 5, 12, NULL, NULL, 'Geu771', 'Geu891', '2025-03-26 13:22:57', '2025-03-28 22:56:31', 'System', 'Admin'),
(84, 'Linda', 'Tetteh', '1993-07-20', 'Female', 'Single', '0509000014', 'linda.tetteh@yahoo.com', 'Winneba', '67e7290f18298.jpg', 'CR-901-2345', 'Librarian', 'Winneba University Library', '0537000014', NULL, 'University of Education, Winneba', 2017, 'Worker', '2025-03-26', 5, 12, NULL, NULL, 'Lih773', 'Lih580', '2025-03-26 13:22:57', '2025-03-28 22:56:15', 'System', 'Admin'),
(85, 'Richard', 'Amankwah', '1992-11-05', 'Male', 'Married', '0272000015', 'richard.amankwah@hotmail.com', 'Wa', '67e728f267540.jpg', 'UW-012-3456', 'Farmer', 'Amankwah Farms', '0558000015', 'Secondary', 'Wa Senior High', 2008, 'Worker', '2025-03-26', 6, 12, 30, 'Children\'s Ministry', 'Rih735', 'Rih981', '2025-03-26 13:22:57', '2025-03-28 22:55:46', 'System', 'Admin'),
(105, 'Paul', 'Akorful', '1999-05-10', 'Male', 'Single', '0555874321', '', 'dayasew, winneba', '52420869_2433479290215632_4185848119927242752_n.jpg', 'CE098762', 'Wielder and fabrication', 'Self employed', '', 'Secondary', 'Winneba NVTI', 2024, 'Active saint', '2022-01-01', 4, 11, 12, 'Adult Ministry', 'p2306', 'Pau6261', '2025-03-30 17:15:17', NULL, 'Admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `member_household`
--

CREATE TABLE `member_household` (
  `assignment_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `assemblies_id` int(11) DEFAULT NULL,
  `household_id` int(11) NOT NULL,
  `shepherd_id` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `member_household`
--

INSERT INTO `member_household` (`assignment_id`, `member_id`, `assemblies_id`, `household_id`, `shepherd_id`, `assigned_at`, `assigned_by`, `updated_at`) VALUES
(1, 16, 6, 9, 15, '2025-03-24 20:13:59', 'system', '2025-03-24 20:13:59'),
(3, 13, 5, 7, 4, '2025-03-19 19:36:20', 'system', '2025-03-19 19:36:20'),
(4, 9, 3, 5, 17, '2025-03-19 19:37:14', 'system', '2025-03-19 19:37:14'),
(5, 21, 2, 4, 20, '2025-03-19 22:18:51', 'system', '2025-03-19 22:18:51'),
(6, 20, 2, 4, 20, '2025-03-19 22:19:14', 'system', '2025-03-19 22:19:14'),
(7, 12, 4, 6, 11, '2025-03-19 22:47:38', 'system', '2025-03-19 22:47:38'),
(9, 30, 6, 9, 15, '2025-03-24 14:54:54', 'system', '2025-03-24 14:54:54'),
(10, 26, 7, 8, 10, '2025-03-24 14:55:48', 'system', '2025-03-24 14:55:48'),
(11, 31, 3, 5, 17, '2025-03-24 18:34:53', 'system', '2025-03-24 18:34:53'),
(12, 32, 6, 3, 1, '2025-03-24 18:46:00', 'system', '2025-03-24 18:46:00'),
(29, 34, 3, 5, 33, '2025-03-25 10:12:21', 'system', '2025-03-25 10:12:21'),
(30, 33, 3, 5, 17, '2025-03-25 10:12:39', 'system', '2025-03-25 10:12:39'),
(31, 35, 6, 9, 30, '2025-03-25 17:33:42', 'system', '2025-03-25 17:33:42'),
(32, 36, 6, 9, 30, '2025-03-25 17:34:46', 'system', '2025-03-25 17:34:46'),
(33, 3, 7, 8, 3, '2025-03-25 17:37:27', 'system', '2025-03-25 17:37:27'),
(34, 61, 3, 10, 17, '2025-03-26 10:52:08', 'system', '2025-03-26 10:52:08'),
(35, 65, 3, 10, 17, '2025-03-26 10:51:13', 'system', '2025-03-26 10:51:13'),
(36, 67, 4, 6, 11, '2025-03-26 12:43:01', 'system', '2025-03-26 12:43:01'),
(37, 1, 6, 11, 1, '2025-03-26 13:00:17', 'system', '2025-03-26 13:00:17'),
(38, 15, 6, 9, 1, '2025-03-26 13:00:57', 'system', '2025-03-26 13:00:57'),
(39, 68, 2, 4, 21, '2025-03-26 13:14:59', 'system', '2025-03-26 13:14:59'),
(40, 69, 3, 10, 9, '2025-03-26 13:16:14', 'system', '2025-03-26 13:16:14'),
(41, 70, 5, 7, 8, '2025-03-26 13:16:54', 'system', '2025-03-26 13:16:54'),
(42, 62, 3, 5, 17, '2025-03-28 13:46:25', 'system', '2025-03-28 13:46:25'),
(43, 71, 5, 12, 8, '2025-03-28 18:45:58', 'system', '2025-03-28 18:45:58'),
(44, 76, 5, 12, 8, '2025-03-28 18:47:07', 'system', '2025-03-28 18:47:07'),
(45, 8, 5, 12, 4, '2025-03-28 18:47:24', 'system', '2025-03-28 18:47:24'),
(46, 75, 6, 9, 30, '2025-03-28 22:57:07', 'system', '2025-03-28 22:57:07'),
(47, 85, 6, 9, 16, '2025-03-30 16:32:55', 'system', '2025-03-30 16:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `member_role`
--

CREATE TABLE `member_role` (
  `member_role_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `function_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'view_own_household', 'View members in own household', '2025-03-17 07:02:16', NULL),
(2, 'view_assembly_members', 'View all members in the assembly', '2025-03-17 07:02:16', NULL),
(3, 'view_households', 'View all households in the assembly', '2025-03-17 07:02:16', NULL),
(4, 'guide_saints', 'Guide saints (for Shepherds)', '2025-03-17 07:02:16', NULL),
(5, 'view_all', 'View all data (for EXCO)', '2025-03-17 07:02:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'EXCO', 'Executive Council', '2025-03-17 07:02:16', NULL, 'Admin', NULL),
(2, 'PED', 'Programs and Evangelism', '2025-03-17 07:02:16', NULL, 'Admin', NULL),
(3, 'TPD', 'Territorial and Pastoral', '2025-03-17 07:02:16', NULL, 'Admin', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_church_function_permissions`
--

CREATE TABLE `role_church_function_permissions` (
  `permission_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `function_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `role_church_function_permissions`
--

INSERT INTO `role_church_function_permissions` (`permission_id`, `role_id`, `function_id`, `is_active`) VALUES
(1, 3, 12, 1),
(2, 3, 8, 1),
(2, 3, 9, 1),
(2, 3, 10, 1),
(2, 3, 11, 1),
(3, 3, 8, 1),
(3, 3, 9, 1),
(4, 3, 11, 1),
(5, 1, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `scopes`
--

CREATE TABLE `scopes` (
  `scope_id` int(11) NOT NULL,
  `scope_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `scopes`
--

INSERT INTO `scopes` (`scope_id`, `scope_name`) VALUES
(2, 'assembly'),
(1, 'household'),
(4, 'national'),
(3, 'zone');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`session_id`, `member_id`, `token`, `created_at`, `expires_at`) VALUES
(2, 62, '75a92266d6d5758f147185e34aaa55404d83d79a54ecb4405668ef703d398210', '2025-03-26 14:47:54', '2025-03-26 15:47:54'),
(3, 62, '4a98a63512dd559a25b3516346eae47b748e9756955d75f0bdfe9fc214cb61f3', '2025-03-27 07:08:17', '2025-03-27 08:08:17'),
(4, 62, '7d917fa3714069d3901e0a51b1b2beda969075e01a60a263a5e1613baee4dcf7', '2025-03-27 07:41:21', '2025-03-27 08:41:21'),
(5, 62, 'c04a21fd265681d2391d411a3dbcaec592679221bf23678b9620ca5514397a7a', '2025-03-27 09:27:39', '2025-03-27 10:27:39'),
(6, 62, 'd3bc482f77b213d7011424f6e3f052387612e318b62736c0290cfe1811c170e4', '2025-03-27 10:32:50', '2025-03-27 11:32:50'),
(8, 62, '6da34b3dbaac0317c0b3cb4fa7edff2b71f82cc84443031cdd1df071689e1b13', '2025-03-27 12:59:26', '2025-03-27 13:59:26'),
(9, 62, '31a9976f64da8aa3dbdd567539906ba3a01fcf6af24aa9a29af9af149e132f6e', '2025-03-27 13:46:11', '2025-03-27 14:46:11'),
(13, 62, 'c817c2e9378cb26c9e6883709759b022ed96d5e2ad5d79f0d71349ac1340ed13', '2025-03-27 14:16:38', '2025-03-27 15:16:38'),
(14, 62, 'd22b84d639f80ec455e0efb54db9213cf2d9395b4d2ef17b459eaf6ce900351e', '2025-03-27 19:10:27', '2025-03-27 20:10:27'),
(15, 62, '533b00aad36ff738278968d435edb869031a2632f22fa7f9217a83e16e5d06ec', '2025-03-27 22:27:10', '2025-03-27 23:27:10'),
(17, 62, '80ee154f2b651840980afe6cbd5f785903aca6dd7d73b054c2445e5e1e768a99', '2025-03-27 23:31:59', '2025-03-28 00:31:59'),
(18, 62, 'dd8eb0768cc2cfa2e418f2f2b73d6e43625d51127a19fd591751c257175f1d12', '2025-03-28 00:02:39', '2025-03-28 01:02:39'),
(19, 62, '27d9703bce54292e49ee78256f863185eef653995071e8a7b03b21ad50d0588b', '2025-03-28 00:05:34', '2025-03-28 01:05:34'),
(20, 62, 'fdd509986767986a9c2e6315c36671539f43be573cffed9a7a4b4cdd743a789e', '2025-03-28 06:07:47', '2025-03-28 07:07:47'),
(25, 62, '3e31cf20071e3a80001800f465af143b80db5ea6554b8fd1f5e66cfa653593f8', '2025-03-28 10:49:12', '2025-03-28 11:49:12'),
(48, 8, 'db9c3f48ebec193049462efdc065dc2f8abe42c109e41966be61baeff9a7bc73', '2025-03-28 13:58:18', '2025-04-04 13:58:18'),
(81, 30, '2da81681a30d052ed95fc200dd07e849bcfb1e9bab32d51f3c94f3568e9a5bc5', '2025-03-30 06:09:33', '2025-04-06 06:09:33'),
(82, 62, '6dfa855bc3669120e1366bf5d11e225c6e3e1057e322d338886663aec281bddc', '2025-03-30 11:29:11', '2025-04-06 11:29:11'),
(92, 62, 'db1154fe67405fc6278f8411a405f136a8d121f2ce747c72be47afec76f98481', '2025-04-02 17:10:38', '2025-04-09 17:10:38'),
(93, 62, '982fc0e37f12ab5d6307b60e8f0129443f490500cd19de319ace9ad1e97cfa37', '2025-04-04 14:44:58', '2025-04-11 14:44:58'),
(95, 62, '6782a4f065edd2fd371bff08e581694bba6f57b0c17d6db57bd3cdacc581a1f1', '2025-04-05 21:20:34', '2025-04-12 21:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `temp_credentials`
--

CREATE TABLE `temp_credentials` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `temp_username` varchar(50) NOT NULL,
  `temp_password` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `temp_credentials`
--

INSERT INTO `temp_credentials` (`id`, `member_id`, `temp_username`, `temp_password`, `created_at`) VALUES
(2, 9, 's6911', 'Sha7202', '2025-03-17 13:44:01'),
(3, 10, 'g5942', 'Gif2408', '2025-03-17 14:32:57'),
(5, 12, 'r6280', 'Ric3030', '2025-03-18 10:48:36'),
(6, 13, 'm1240', 'Mar8163', '2025-03-18 11:02:23'),
(8, 15, 'h9237', 'Hen9309', '2025-03-18 11:20:54'),
(9, 16, 'g4793', 'Gen8756', '2025-03-18 21:28:16'),
(10, 17, 'e9570', 'Emm1253', '2025-03-19 09:10:25'),
(11, 20, 'j6696', 'Jos7568', '2025-03-19 22:08:08'),
(12, 21, 'd1389', 'Deb9541', '2025-03-19 22:16:10'),
(13, 26, 'j6405', 'Jam5542', '2025-03-20 00:09:13'),
(16, 31, 'o5646', 'Oda4729', '2025-03-24 18:33:11'),
(17, 32, 'a1584', 'Abi5890', '2025-03-24 18:44:29'),
(18, 33, 'd1293', 'Ded2550', '2025-03-25 09:19:53'),
(19, 34, 'c2507', 'Con6072', '2025-03-25 09:33:47'),
(20, 35, 'v7346', 'Van3033', '2025-03-25 17:29:31'),
(21, 36, 'v7359', 'Van1661', '2025-03-25 17:32:58'),
(44, 61, 'Kwi468', 'Kwi397', '2025-03-26 09:49:46'),
(46, 63, 'Isu439', 'Isu470', '2025-03-26 09:49:46'),
(47, 64, 'Abi421', 'Abi428', '2025-03-26 09:49:46'),
(48, 65, 'Koe060', 'Koe450', '2025-03-26 09:49:46'),
(49, 66, 'Akh697', 'Akh045', '2025-03-26 09:49:46'),
(50, 67, 'Yae505', 'Yae820', '2025-03-26 09:49:46'),
(51, 68, 'Ado206', 'Ado500', '2025-03-26 09:49:46'),
(52, 69, 'Kon258', 'Kon297', '2025-03-26 09:49:46'),
(53, 70, 'Eso011', 'Eso997', '2025-03-26 09:49:46'),
(54, 71, 'Kwi397', 'Kwi724', '2025-03-26 13:22:57'),
(55, 72, 'Amg203', 'Amg784', '2025-03-26 13:22:57'),
(56, 73, 'Joh588', 'Joh291', '2025-03-26 13:22:57'),
(57, 74, 'Man207', 'Man309', '2025-03-26 13:22:57'),
(58, 75, 'Miu969', 'Miu086', '2025-03-26 13:22:57'),
(59, 76, 'Sau508', 'Sau813', '2025-03-26 13:22:57'),
(60, 77, 'Yah113', 'Yah478', '2025-03-26 13:22:57'),
(61, 78, 'Akg537', 'Akg670', '2025-03-26 13:22:57'),
(62, 79, 'Nao374', 'Nao878', '2025-03-26 13:22:57'),
(63, 80, 'Esh021', 'Esh727', '2025-03-26 13:22:57'),
(64, 81, 'Sam130', 'Sam116', '2025-03-26 13:22:57'),
(65, 82, 'Pae208', 'Pae001', '2025-03-26 13:22:57'),
(66, 83, 'Geu771', 'Geu891', '2025-03-26 13:22:57'),
(67, 84, 'Lih773', 'Lih580', '2025-03-26 13:22:57'),
(68, 85, 'Rih735', 'Rih981', '2025-03-26 13:22:57'),
(71, 105, 'p2306', 'Pau6261', '2025-03-30 17:15:17');

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`zone_id`, `name`, `description`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(3, 'Zone C', 'Central Region, Western Region ', '2025-03-17 07:02:16', '2025-03-17 08:10:26', 'Admin', ''),
(4, 'Zone B', 'Covers Ashanti Region, Northern Region, Upper east and Upper West ', '2025-03-17 07:02:16', '2025-03-17 08:09:51', 'Admin', ''),
(5, 'Zone A', 'Covers Accra, Volta, and Eastern Region ', '2025-03-17 07:02:16', '2025-03-17 08:09:02', 'Admin', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assemblies`
--
ALTER TABLE `assemblies`
  ADD PRIMARY KEY (`assembly_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `assemblies_ibfk_1` (`zone_id`);

--
-- Indexes for table `assembly_assignments`
--
ALTER TABLE `assembly_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `assembly_id` (`assembly_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `role_type` (`role_type`);

--
-- Indexes for table `church_functions`
--
ALTER TABLE `church_functions`
  ADD PRIMARY KEY (`function_id`),
  ADD KEY `church_functions_ibfk_1` (`role_id`),
  ADD KEY `fk_church_function_scope` (`scope_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `fk_event_event_type` (`event_type_id`),
  ADD KEY `fk_event_household` (`household_id`),
  ADD KEY `fk_event_assembly` (`assembly_id`),
  ADD KEY `fk_event_zone` (`zone_id`),
  ADD KEY `fk_event_created_by` (`created_by`);

--
-- Indexes for table `events_old`
--
ALTER TABLE `events_old`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `zone_id` (`zone_id`),
  ADD KEY `assembly_id` (`assembly_id`),
  ADD KEY `household_id` (`household_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `fk_attendance_event` (`event_id`),
  ADD KEY `fk_attendance_instance` (`instance_id`),
  ADD KEY `fk_attendance_member` (`member_id`),
  ADD KEY `fk_attendance_recorded_by` (`recorded_by`);

--
-- Indexes for table `event_instances`
--
ALTER TABLE `event_instances`
  ADD PRIMARY KEY (`instance_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_instances_old`
--
ALTER TABLE `event_instances_old`
  ADD PRIMARY KEY (`instance_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`event_type_id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`household_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `households_ibfk_1` (`assembly_id`);

--
-- Indexes for table `household_assistant_assignments`
--
ALTER TABLE `household_assistant_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `household_id` (`household_id`),
  ADD KEY `assistant_member_id` (`assistant_member_id`);

--
-- Indexes for table `household_shepherdhead_assignments`
--
ALTER TABLE `household_shepherdhead_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_household_shepherd` (`household_id`,`shepherd_member_id`),
  ADD KEY `fk_household_shepherdhead_member` (`shepherd_member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `members_ibfk_1` (`assemblies_id`),
  ADD KEY `members_ibfk_2` (`local_function_id`),
  ADD KEY `idx_referral_id` (`referral_id`);

--
-- Indexes for table `member_household`
--
ALTER TABLE `member_household`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `uk_member` (`member_id`),
  ADD KEY `fk_assemblies` (`assemblies_id`),
  ADD KEY `fk_shepherd` (`shepherd_id`),
  ADD KEY `idx_household` (`household_id`);

--
-- Indexes for table `member_role`
--
ALTER TABLE `member_role`
  ADD PRIMARY KEY (`member_role_id`),
  ADD UNIQUE KEY `unique_member_role_function` (`member_id`,`role_id`,`function_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_church_function_permissions`
--
ALTER TABLE `role_church_function_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`,`function_id`);

--
-- Indexes for table `scopes`
--
ALTER TABLE `scopes`
  ADD PRIMARY KEY (`scope_id`),
  ADD UNIQUE KEY `scope_name` (`scope_name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `temp_credentials`
--
ALTER TABLE `temp_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assemblies`
--
ALTER TABLE `assemblies`
  MODIFY `assembly_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `assembly_assignments`
--
ALTER TABLE `assembly_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `church_functions`
--
ALTER TABLE `church_functions`
  MODIFY `function_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_old`
--
ALTER TABLE `events_old`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_attendance`
--
ALTER TABLE `event_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_instances`
--
ALTER TABLE `event_instances`
  MODIFY `instance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_instances_old`
--
ALTER TABLE `event_instances_old`
  MODIFY `instance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_types`
--
ALTER TABLE `event_types`
  MODIFY `event_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `household_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `household_assistant_assignments`
--
ALTER TABLE `household_assistant_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `household_shepherdhead_assignments`
--
ALTER TABLE `household_shepherdhead_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `member_household`
--
ALTER TABLE `member_household`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `member_role`
--
ALTER TABLE `member_role`
  MODIFY `member_role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `scopes`
--
ALTER TABLE `scopes`
  MODIFY `scope_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `temp_credentials`
--
ALTER TABLE `temp_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assemblies`
--
ALTER TABLE `assemblies`
  ADD CONSTRAINT `assemblies_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE CASCADE;

--
-- Constraints for table `assembly_assignments`
--
ALTER TABLE `assembly_assignments`
  ADD CONSTRAINT `assembly_assignments_ibfk_1` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`assembly_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_assignments_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_assignments_ibfk_3` FOREIGN KEY (`role_type`) REFERENCES `church_functions` (`function_id`);

--
-- Constraints for table `church_functions`
--
ALTER TABLE `church_functions`
  ADD CONSTRAINT `church_functions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_church_function_scope` FOREIGN KEY (`scope_id`) REFERENCES `scopes` (`scope_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`event_type_id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`),
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`assembly_id`),
  ADD CONSTRAINT `events_ibfk_4` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`),
  ADD CONSTRAINT `events_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `members` (`member_id`),
  ADD CONSTRAINT `fk_event_assembly` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`assembly_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_event_created_by` FOREIGN KEY (`created_by`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_event_event_type` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`event_type_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_event_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_event_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE SET NULL;

--
-- Constraints for table `events_old`
--
ALTER TABLE `events_old`
  ADD CONSTRAINT `events_old_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`),
  ADD CONSTRAINT `events_old_ibfk_2` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`assembly_id`),
  ADD CONSTRAINT `events_old_ibfk_3` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`),
  ADD CONSTRAINT `events_old_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `members` (`member_id`);

--
-- Constraints for table `event_attendance`
--
ALTER TABLE `event_attendance`
  ADD CONSTRAINT `event_attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `event_attendance_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`),
  ADD CONSTRAINT `event_attendance_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `members` (`member_id`),
  ADD CONSTRAINT `fk_attendance_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_instance` FOREIGN KEY (`instance_id`) REFERENCES `event_instances` (`instance_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_instances`
--
ALTER TABLE `event_instances`
  ADD CONSTRAINT `event_instances_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_instances_old`
--
ALTER TABLE `event_instances_old`
  ADD CONSTRAINT `event_instances_old_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events_old` (`event_id`);

--
-- Constraints for table `households`
--
ALTER TABLE `households`
  ADD CONSTRAINT `households_ibfk_1` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`assembly_id`) ON DELETE CASCADE;

--
-- Constraints for table `household_assistant_assignments`
--
ALTER TABLE `household_assistant_assignments`
  ADD CONSTRAINT `household_assistant_assignments_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`),
  ADD CONSTRAINT `household_assistant_assignments_ibfk_2` FOREIGN KEY (`assistant_member_id`) REFERENCES `members` (`member_id`);

--
-- Constraints for table `household_shepherdhead_assignments`
--
ALTER TABLE `household_shepherdhead_assignments`
  ADD CONSTRAINT `fk_household_shepherdhead_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_household_shepherdhead_member` FOREIGN KEY (`shepherd_member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_referral` FOREIGN KEY (`referral_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`assemblies_id`) REFERENCES `assemblies` (`assembly_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `members_ibfk_2` FOREIGN KEY (`local_function_id`) REFERENCES `church_functions` (`function_id`) ON DELETE CASCADE;

--
-- Constraints for table `member_household`
--
ALTER TABLE `member_household`
  ADD CONSTRAINT `fk_assemblies` FOREIGN KEY (`assemblies_id`) REFERENCES `assemblies` (`assembly_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_shepherd` FOREIGN KEY (`shepherd_id`) REFERENCES `members` (`member_id`) ON DELETE SET NULL;

--
-- Constraints for table `temp_credentials`
--
ALTER TABLE `temp_credentials`
  ADD CONSTRAINT `temp_credentials_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
