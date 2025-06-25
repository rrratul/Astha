-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 10:33 PM
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
-- Database: `shohochor_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisor_appointments`
--

CREATE TABLE `advisor_appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `advisor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `duration` int(11) DEFAULT 60,
  `meeting_type` enum('in_person','video_call','phone_call') DEFAULT 'video_call',
  `user_message` text DEFAULT NULL,
  `advisor_notes` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled','rescheduled') DEFAULT 'pending',
  `meeting_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `facility_name` varchar(100) DEFAULT NULL,
  `appointment_type` enum('doctor','test','therapy','other') DEFAULT 'doctor',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `duration` int(11) DEFAULT 30,
  `location` text DEFAULT NULL,
  `is_video_call` tinyint(1) DEFAULT 0,
  `video_call_link` varchar(255) DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('hobbies','health','local','religion','technology','other') DEFAULT 'other',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communities`
--

INSERT INTO `communities` (`id`, `name`, `description`, `image`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Local Garden Club', 'Connect with other garden enthusiasts in your community to share tips and experiences.', NULL, 'hobbies', '2025-06-14 07:32:24', '2025-06-14 07:32:24'),
(2, 'Senior Technology Group', 'Learn about technology together in a friendly, supportive environment.', NULL, 'technology', '2025-06-14 07:32:24', '2025-06-14 07:32:24'),
(3, 'Health & Wellness Circle', 'Share health tips and support each other on your wellness journey.', NULL, 'health', '2025-06-14 07:32:24', '2025-06-14 07:32:24');

-- --------------------------------------------------------

--
-- Table structure for table `community_events`
--

CREATE TABLE `community_events` (
  `id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `duration` int(11) DEFAULT 60,
  `location` text DEFAULT NULL,
  `is_virtual` tinyint(1) DEFAULT 0,
  `virtual_link` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_members`
--

CREATE TABLE `community_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `role` enum('member','moderator','admin') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consultation_requests`
--

CREATE TABLE `consultation_requests` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `organization` varchar(100) DEFAULT NULL,
  `primary_need` varchar(100) NOT NULL,
  `specific_needs` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(50) DEFAULT NULL,
  `status` enum('pending','contacted','scheduled','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `to_email` varchar(100) NOT NULL,
  `to_name` varchar(100) DEFAULT NULL,
  `from_email` varchar(100) NOT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `headers` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `priority` tinyint(1) DEFAULT 3,
  `max_attempts` tinyint(1) DEFAULT 3,
  `attempts` tinyint(1) DEFAULT 0,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `last_attempt_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_alerts`
--

CREATE TABLE `emergency_alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `alert_type` enum('fall','medical','sos','other') NOT NULL,
  `location` text DEFAULT NULL,
  `status` enum('active','resolved','false_alarm') DEFAULT 'active',
  `resolved_by` int(11) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_emergency` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_advisors`
--

CREATE TABLE `financial_advisors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialty` varchar(100) NOT NULL,
  `expertise` text DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `available` tinyint(1) DEFAULT 1,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_advisors`
--

INSERT INTO `financial_advisors` (`id`, `name`, `specialty`, `expertise`, `email`, `phone`, `rating`, `total_reviews`, `available`, `hourly_rate`, `languages`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'Sarah Johnson', 'Retirement Planning', 'Specializes in helping seniors maximize retirement income and minimize taxes', 'sarah.johnson@advisor.com', '+8801712345678', 4.80, 127, 1, 2500.00, 'English,Bengali', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(2, 'Michael Patel', 'Estate Planning', 'Creates comprehensive estate plans including wills, trusts, and power of attorney documents', 'michael.patel@advisor.com', '+8801812345678', 4.90, 156, 1, 3000.00, 'English,Hindi,Bengali', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(3, 'Fatima Rahman', 'Tax Filing Assistance', 'Helps seniors identify all available deductions and credits to minimize tax liability', 'fatima.rahman@advisor.com', '+8801912345678', 4.70, 89, 1, 2000.00, 'Bengali,English', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(4, 'Aminul Islam', 'Government Benefits', 'Expert in Bangladesh government benefits and social security programs', 'aminul.islam@advisor.com', '+8801612345678', 4.60, 73, 0, 1800.00, 'Bengali', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(5, 'Sarah Johnson', 'Retirement Planning', 'Specializes in helping seniors maximize retirement income and minimize taxes', 'sarah.johnson@advisor.com', '+8801712345678', 4.80, 127, 1, 2500.00, 'English,Bengali', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43'),
(6, 'Michael Patel', 'Estate Planning', 'Creates comprehensive estate plans including wills, trusts, and power of attorney documents', 'michael.patel@advisor.com', '+8801812345678', 4.90, 156, 1, 3000.00, 'English,Hindi,Bengali', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43'),
(7, 'Fatima Rahman', 'Tax Filing Assistance', 'Helps seniors identify all available deductions and credits to minimize tax liability', 'fatima.rahman@advisor.com', '+8801912345678', 4.70, 89, 1, 2000.00, 'Bengali,English', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43'),
(8, 'Aminul Islam', 'Government Benefits', 'Expert in Bangladesh government benefits and social security programs', 'aminul.islam@advisor.com', '+8801612345678', 4.60, 73, 0, 1800.00, 'Bengali', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43');

-- --------------------------------------------------------

--
-- Table structure for table `financial_calculations`
--

CREATE TABLE `financial_calculations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `calculation_type` varchar(50) NOT NULL,
  `calculation_name` varchar(100) DEFAULT NULL,
  `calculation_result` text NOT NULL,
  `result_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result_data`)),
  `calculation_inputs` text NOT NULL,
  `input_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_data`)),
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_calculations`
--

INSERT INTO `financial_calculations` (`id`, `user_id`, `calculation_type`, `calculation_name`, `calculation_result`, `result_data`, `calculation_inputs`, `input_data`, `created_at`, `updated_at`) VALUES
(1, 13, 'fixed_deposit', NULL, '{\"principal\":\"1,500.00\",\"interest_rate\":\"12.00\",\"term\":5,\"maturity_amount\":\"2,643.51\",\"interest_earned\":\"1,143.51\"}', NULL, '{\"principal\":1500,\"interest_rate\":12,\"term\":5}', NULL, '2025-06-23 23:20:46', '2025-06-23 17:20:46');

-- --------------------------------------------------------

--
-- Table structure for table `health_metrics`
--

CREATE TABLE `health_metrics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `metric_type` enum('blood_pressure','heart_rate','blood_sugar','oxygen_level','weight','temperature','sleep','steps','other') NOT NULL,
  `systolic` int(11) DEFAULT NULL,
  `diastolic` int(11) DEFAULT NULL,
  `pulse` int(11) DEFAULT NULL,
  `blood_sugar` decimal(5,1) DEFAULT NULL,
  `oxygen_level` decimal(4,1) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `sleep_hours` decimal(4,1) DEFAULT NULL,
  `steps` int(11) DEFAULT NULL,
  `other_value` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_tips`
--

CREATE TABLE `health_tips` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('general','nutrition','exercise','mental','sleep','other') DEFAULT 'general',
  `source` varchar(100) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_tips`
--

INSERT INTO `health_tips` (`id`, `title`, `content`, `category`, `source`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Stay Hydrated Daily', 'Drink at least 8 glasses of water each day to maintain proper hydration, especially important for elderly individuals.', 'general', 'World Health Organization', 1, '2025-06-14 07:32:24', '2025-06-14 07:32:24'),
(2, 'Daily Walking Benefits', 'Just 30 minutes of walking each day can improve cardiovascular health, strengthen bones, and enhance mood.', 'exercise', 'American Heart Association', 1, '2025-06-14 07:32:24', '2025-06-14 07:32:24'),
(3, 'Mind Exercises', 'Regular puzzles, crosswords, or reading can help maintain cognitive function and reduce risk of dementia.', 'mental', 'Alzheimer\'s Association', 0, '2025-06-14 07:32:24', '2025-06-14 07:32:24');

-- --------------------------------------------------------

--
-- Table structure for table `life_stories`
--

CREATE TABLE `life_stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `prescribing_doctor` varchar(100) DEFAULT NULL,
  `pharmacy` varchar(100) DEFAULT NULL,
  `refill_reminder_days` int(11) DEFAULT 7,
  `refill_count` int(11) DEFAULT 0,
  `morning` tinyint(1) DEFAULT 0,
  `afternoon` tinyint(1) DEFAULT 0,
  `evening` tinyint(1) DEFAULT 0,
  `night` tinyint(1) DEFAULT 0,
  `with_food` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication_logs`
--

CREATE TABLE `medication_logs` (
  `id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `scheduled_time` datetime NOT NULL,
  `taken_time` datetime DEFAULT NULL,
  `status` enum('pending','taken','skipped','missed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mood_tracking`
--

CREATE TABLE `mood_tracking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mood` enum('very_happy','happy','neutral','sad','very_sad') NOT NULL,
  `notes` text DEFAULT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_articles`
--

CREATE TABLE `news_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('health','local','technology','lifestyle','world','other') DEFAULT 'health',
  `image` varchar(255) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `published_at` date NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'BDT',
  `due_date` date NOT NULL,
  `payment_type` enum('utility','insurance','tax','medical','other') DEFAULT 'other',
  `autopay_enabled` tinyint(1) DEFAULT 0,
  `reminder_days` int(11) DEFAULT 3,
  `status` enum('upcoming','paid','overdue','cancelled') DEFAULT 'upcoming',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_reminders`
--

INSERT INTO `payment_reminders` (`id`, `user_id`, `payment_name`, `amount`, `currency`, `due_date`, `payment_type`, `autopay_enabled`, `reminder_days`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 6, 'Electricity Bill', 3450.00, 'BDT', '2025-06-26', 'utility', 0, 3, 'upcoming', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(2, 6, 'Health Insurance Premium', 6500.00, 'BDT', '2025-06-30', 'insurance', 1, 3, 'upcoming', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(3, 6, 'Property Tax', 28900.00, 'BDT', '2025-07-07', 'tax', 0, 3, 'upcoming', NULL, '2025-06-23 16:21:56', '2025-06-23 16:21:56'),
(4, 6, 'Electricity Bill', 3450.00, 'BDT', '2025-06-26', 'utility', 0, 3, 'upcoming', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43'),
(5, 6, 'Health Insurance Premium', 6500.00, 'BDT', '2025-06-30', 'insurance', 1, 3, 'upcoming', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43'),
(6, 6, 'Property Tax', 28900.00, 'BDT', '2025-07-07', 'tax', 0, 3, 'upcoming', NULL, '2025-06-23 16:43:43', '2025-06-23 16:43:43');

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_calls`
--

CREATE TABLE `scheduled_calls` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `connection_id` int(11) NOT NULL,
  `scheduled_time` datetime NOT NULL,
  `duration` int(11) DEFAULT 30,
  `call_type` enum('voice','video') DEFAULT 'voice',
  `reminder_sent` tinyint(1) DEFAULT 0,
  `status` enum('scheduled','completed','missed','cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_connections`
--

CREATE TABLE `social_connections` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `connection_name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `connection_type` enum('family','friend','caregiver','healthcare_provider','other') DEFAULT 'family',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `profile_picture` longblob DEFAULT NULL,
  `profile_picture_type` varchar(50) DEFAULT NULL,
  `profile_picture_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `role` enum('admin','elderly','caregiver','family_member','healthcare_provider') DEFAULT 'elderly',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `remember_token_expiry` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `address`, `city`, `state`, `postal_code`, `country`, `profile_picture`, `profile_picture_type`, `profile_picture_name`, `bio`, `role`, `status`, `email_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `remember_token`, `remember_token_expiry`, `login_attempts`, `last_login`, `last_activity`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@shohochor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin', 'active', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, '2025-06-14 13:32:24', '2025-06-14 07:32:24', '2025-06-17 18:47:59'),
(6, 'Rezaur Rahman', 'Ratul', 'rezaurrahmanratul@gmail.com', '$2y$10$ryVx895e9Yo.4c23DuyugObsR7UDXtnf4k3EnjDlfi9hN5pj2/DE2', '01685337528', '2002-02-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'elderly', 'active', 1, '58d321542d16762024e4f63f4c9176bd', 'bad5fb064c10072f4391bc288b9bd81e9c6674da56d988382469a537ede81c71', '2025-06-17 20:00:03', NULL, NULL, 0, '2025-06-25 01:59:14', '2025-06-25 01:59:24', '2025-06-17 16:23:28', '2025-06-24 19:59:24'),
(13, 'Rezaur Rahman Ratul', 'Ratul', 'rratul212025@bscse.uiu.ac.bd', '$2y$10$4UTOl7SiB9CRHMGyx1572.jADh.sGo6xkOaNteW9O5doAY3PnP0fu', '01685337528', '2025-06-19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'elderly', 'active', 1, '733d0f078841520c838e083ba17907ef', NULL, NULL, NULL, NULL, 2, '2025-06-23 19:12:04', '2025-06-25 01:58:57', '2025-06-18 06:03:42', '2025-06-24 19:58:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_log`
--

INSERT INTO `user_activity_log` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-17 18:50:22'),
(2, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:19:36'),
(3, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:19:59'),
(4, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:20:55'),
(5, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:21:58'),
(6, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-17 19:31:06'),
(7, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:31:56'),
(8, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:33:35'),
(9, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:34:17'),
(10, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-17 19:37:40'),
(11, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-17 19:37:53'),
(12, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:39:04'),
(13, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:40:13'),
(14, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:43:15'),
(16, 6, 'failed_login', 'Failed login attempt 1/5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:53:45'),
(17, 6, 'login', 'Successful login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:53:55'),
(18, 6, 'failed_login', 'Failed login attempt 1/5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:54:33'),
(19, 6, 'failed_login', 'Failed login attempt 2/5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:54:35'),
(20, 6, 'failed_login', 'Failed login attempt 3/5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:54:37'),
(21, 6, 'failed_login', 'Failed login attempt 4/5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:54:39'),
(22, 6, 'failed_login', 'Failed login attempt 5/5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', '2025-06-17 19:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text_size` varchar(10) DEFAULT 'medium',
  `high_contrast` tinyint(1) DEFAULT 0,
  `voice_assistance` tinyint(1) DEFAULT 1,
  `email_notifications` tinyint(1) DEFAULT 1,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `medication_reminders` tinyint(1) DEFAULT 1,
  `appointment_reminders` tinyint(1) DEFAULT 1,
  `language` varchar(10) DEFAULT 'en',
  `timezone` varchar(50) DEFAULT 'UTC',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisor_appointments`
--
ALTER TABLE `advisor_appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `community_events`
--
ALTER TABLE `community_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `community_id` (`community_id`),
  ADD KEY `event_date` (`event_date`);

--
-- Indexes for table `community_members`
--
ALTER TABLE `community_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`community_id`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `consultation_requests`
--
ALTER TABLE `consultation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `status` (`status`),
  ADD KEY `preferred_date` (`preferred_date`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `priority` (`priority`);

--
-- Indexes for table `emergency_alerts`
--
ALTER TABLE `emergency_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `alert_type` (`alert_type`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `financial_advisors`
--
ALTER TABLE `financial_advisors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_calculations`
--
ALTER TABLE `financial_calculations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `calculation_type` (`calculation_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_calculation_type` (`calculation_type`);

--
-- Indexes for table `health_metrics`
--
ALTER TABLE `health_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `metric_type` (`metric_type`),
  ADD KEY `recorded_at` (`recorded_at`);

--
-- Indexes for table `health_tips`
--
ALTER TABLE `health_tips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `is_featured` (`is_featured`);

--
-- Indexes for table `life_stories`
--
ALTER TABLE `life_stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medication_logs`
--
ALTER TABLE `medication_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `medication_id` (`medication_id`),
  ADD KEY `scheduled_time` (`scheduled_time`);

--
-- Indexes for table `mood_tracking`
--
ALTER TABLE `mood_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recorded_at` (`recorded_at`);

--
-- Indexes for table `news_articles`
--
ALTER TABLE `news_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `published_at` (`published_at`),
  ADD KEY `is_featured` (`is_featured`);

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `scheduled_calls`
--
ALTER TABLE `scheduled_calls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `connection_id` (`connection_id`),
  ADD KEY `scheduled_time` (`scheduled_time`);

--
-- Indexes for table `social_connections`
--
ALTER TABLE `social_connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisor_appointments`
--
ALTER TABLE `advisor_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `community_events`
--
ALTER TABLE `community_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_members`
--
ALTER TABLE `community_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consultation_requests`
--
ALTER TABLE `consultation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergency_alerts`
--
ALTER TABLE `emergency_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_advisors`
--
ALTER TABLE `financial_advisors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `financial_calculations`
--
ALTER TABLE `financial_calculations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_metrics`
--
ALTER TABLE `health_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_tips`
--
ALTER TABLE `health_tips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `life_stories`
--
ALTER TABLE `life_stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medication_logs`
--
ALTER TABLE `medication_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mood_tracking`
--
ALTER TABLE `mood_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_articles`
--
ALTER TABLE `news_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `scheduled_calls`
--
ALTER TABLE `scheduled_calls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_connections`
--
ALTER TABLE `social_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advisor_appointments`
--
ALTER TABLE `advisor_appointments`
  ADD CONSTRAINT `advisor_appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advisor_appointments_ibfk_2` FOREIGN KEY (`advisor_id`) REFERENCES `financial_advisors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_events`
--
ALTER TABLE `community_events`
  ADD CONSTRAINT `community_events_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `community_members`
--
ALTER TABLE `community_members`
  ADD CONSTRAINT `community_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_members_ibfk_2` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `consultation_requests`
--
ALTER TABLE `consultation_requests`
  ADD CONSTRAINT `consultation_requests_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `emergency_alerts`
--
ALTER TABLE `emergency_alerts`
  ADD CONSTRAINT `emergency_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emergency_alerts_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_calculations`
--
ALTER TABLE `financial_calculations`
  ADD CONSTRAINT `financial_calculations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_metrics`
--
ALTER TABLE `health_metrics`
  ADD CONSTRAINT `health_metrics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `life_stories`
--
ALTER TABLE `life_stories`
  ADD CONSTRAINT `life_stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medication_logs`
--
ALTER TABLE `medication_logs`
  ADD CONSTRAINT `medication_logs_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medication_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mood_tracking`
--
ALTER TABLE `mood_tracking`
  ADD CONSTRAINT `mood_tracking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `payment_reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scheduled_calls`
--
ALTER TABLE `scheduled_calls`
  ADD CONSTRAINT `scheduled_calls_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scheduled_calls_ibfk_2` FOREIGN KEY (`connection_id`) REFERENCES `social_connections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `social_connections`
--
ALTER TABLE `social_connections`
  ADD CONSTRAINT `social_connections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
