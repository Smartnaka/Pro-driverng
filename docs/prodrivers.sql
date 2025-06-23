-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 20, 2025 at 03:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prodrivers`
--

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-27 22:36:25'),
(2, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-27 22:43:21'),
(3, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:10:03'),
(4, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:25:30'),
(5, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:26:24'),
(6, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:31:46'),
(7, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:34:24'),
(8, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:38:14'),
(9, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 09:53:43'),
(10, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 10:02:17'),
(11, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 10:45:34'),
(12, 1, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 14:23:12'),
(13, 2, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 17:42:57'),
(14, 2, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-28 17:43:04'),
(17, 10, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-04 18:08:10'),
(18, 11, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-04 20:43:59'),
(20, 13, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-09 20:16:16'),
(21, 15, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-20 13:20:51'),
(22, 15, 'login_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-20 13:47:26');

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `name`, `created_at`) VALUES
(1, 'admin', '$2y$10$johk8F9JuUkOEuLEhV6P/uHLKRp6.pSR/p/cjjpkBdrzEZVkxKzZG', 'admin@prodrivers.com', 'System Administrator', '2025-06-05 01:12:14');

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `driver_id`, `pickup_location`, `dropoff_location`, `pickup_date`, `pickup_time`, `duration_days`, `vehicle_type`, `trip_purpose`, `additional_notes`, `status`, `created_at`, `updated_at`, `amount`, `reference`) VALUES
(3, 2, 11, 'Lekki', 'Ajegunle', '2025-06-10', '10:49:00', 1, 'Both', 'Business', 'please be fast', 'pending', '2025-06-09 19:49:46', '2025-06-09 19:49:46', 0.00, ''),
(4, 4, 11, 'Lekki', 'Ajegunle', '2025-06-19', '17:41:00', 4, 'Automatic', 'Personal', '', 'cancelled', '2025-06-18 16:42:27', '2025-06-20 11:27:41', 0.00, '');

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `created_at`, `status`) VALUES
(2, 'Smart', 'Nakamoura', 'lurozebu@cyclelove.cc', '08107895954', '$2y$10$4GShQFd29NSigw8pAcuYIufXWQMWeTdtyAaW4.KOHh.LmYVWICnxm', '2025-06-09 19:47:35', 'active'),
(3, 'Adeoti', 'Adeola', 'adeotiisrael2024@gmail.com', '8107895954', '$2y$10$6D74lAXwuAO/DzgJsD/HpuacCA1yiY3aAgozIYfIqOybuko3hh49u', '2025-06-12 02:59:44', 'active'),
(4, 'Adeoti', 'Israel', 'adeotiisrael2022@gmail.com', '08107895954', '$2y$10$47ztc7kTd5nGX8FgXIJyqeeHuxGYNTq9WmTk/R1oc8az067vaFdEC', '2025-06-17 12:14:16', 'active'),
(5, 'Matthew', 'Mills', 'smartnaka54@gmail.com', '12762948510', '$2y$10$./ZqBEyfg6hzFNLCkwyne.asDYf7w9UfQ1Li/bJ6.RrxRV3.iFyma', '2025-06-18 18:42:41', 'active');

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `exp_years`, `education`, `photo_path`, `created_at`, `reset_token`, `reset_token_expiry`, `is_online`, `license_path`, `vehicle_papers_path`, `address`, `experience`, `license_number`, `about_me`, `resident`, `family`, `education_level`, `drive`, `speak`, `nin`, `dob`, `bank_name`, `acc_num`, `acc_name`, `skills`, `exp_date`, `license_image`, `profile_picture`, `status`, `is_verified`, `verified_at`, `verification_notes`) VALUES
(1, 'Adeoti', 'Israel', 'adeotiisrael2022@gmail.com', '08107895954', '$2y$10$fcDAuhMIoVw0FK9OiIpeU.2bEmWuasCldvomIG4NtjV1X6B0RH2MS', 5, 'Tertiary', 'uploads/documents/passport/Adeoti_Israel_passport_1748450930.png', '2025-05-27 21:38:10', NULL, NULL, 0, 'uploads/documents/licenses/license_1_1748449771.png', 'uploads/documents/vehicle_papers/vehicle_papers_1_1748449785.jpg', 'Alimosho', 5, '10022229', 'sxjnsjjsjss', 'Ayedun Quarters, Nova Rd. Ado Ekiti', '', '', 'Car, Bus, Coaster, Motorcycle/Tricycle', 'English', '94814284027', '2000-02-17', 'Wema Bank', '0285206092', 'ADEOTI ISREAL ADEOLA', 'Graphics Design', '2027-06-08', 'Adeoti Israel.68372a40b673d8.73959600.png', 'uploads/profile-pictures/Adeoti_Israel_profile_1748450930.png', 'pending', 1, '2025-06-06 12:11:33', ''),
(2, 'Praise', 'Balogun', 'israelsmart24@gmail.com', '12762948510', '$2y$10$k0m3njoEOpIA5SEoPibgr.XA1SO0OssvvwljfziW/qQ3bmgCEluEe', 8, 'Tertiary', '', '2025-05-28 17:31:31', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-pictures/Praise_Balogun_profile_1748453491_background.png', 'pending', 1, '2025-06-06 12:11:55', ''),
(3, 'Kelly', 'James', 'tasesap611@baxima.com', '12762948510', '$2y$10$pUewRM8MBxDGB5tMXRcAvuwVGYJdSv6L9fCHMccsiqCglrntn4xFS', 4, 'Tertiary', '', '2025-06-03 20:36:01', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-pictures/Kelly_James_profile_1748982961_Map-light.jpg', 'pending', 1, '2025-06-06 12:12:30', ''),
(4, 'Kelly', 'Jamesaa', 'yoyalol412@acedby.com', '12762948510', '$2y$10$J5WOVBbNDthh4KHfnvaNveputl5ANt7TdZtOqEYtCtYs0c21pvsBu', 13, 'Uneducated', '', '2025-06-03 20:42:41', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-pictures/Kelly_Jamesaa_profile_1748983361_Map-light.jpg', 'pending', 1, '2025-06-06 12:12:27', ''),
(5, 'Matthew', 'Millstt', 'xaseh41255@baxima.com', '12762948510', '$2y$10$MFi/E8hTj5VV3PYh1b1/pOliJnyhXrP/8rrekXNyOirmqPQDX8pEG', 13, 'Uneducated', '', '2025-06-04 16:35:04', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-pictures/Matthew_Millstt_profile_1749054904_Map-light.jpg', 'rejected', 1, '2025-06-06 12:12:03', ''),
(6, 'KAYODE', 'ADEOTI', 'rajoh49918@acedby.com', '127629485106666', '$2y$10$1GFgMN3vmy0aT8PdLuztAuWhMW9AW4gCOA1TUdO/O.SK43i3CUF52', 10, 'Uneducated', '', '2025-06-04 16:38:49', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-pictures/KAYODE_ADEOTI_profile_1749055129_Map-light.jpg', 'pending', 1, '2025-06-06 12:12:12', ''),
(7, 'Praise', 'Balogun', 'bifoyob248@baxima.com', '08107895954', '$2y$10$XLJ2gnN6Jda16SNxpvHQOeY8Je9geVJyqI50RTyQFx5XgyHR/TaD.', 15, 'Tertiary', '', '2025-06-04 17:08:06', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-pictures/Praise_Balogun_profile_1749056886_Map-light.jpg', 'pending', 1, '2025-06-06 12:07:25', ''),
(10, 'Adeoti', 'Adeolaas', 'fewifik800@baxima.com', '8107895954', '$2y$10$A31DDLtnHvDBfLLPK5HwUOS6SiE6073ExoPvHVeqrL60jzkdYi1/C', 14, 'Secondary', 'uploads/documents/passport/Adeoti_Adeolaas_passport_1749060838.jpg', '2025-06-04 18:07:40', NULL, NULL, 0, 'uploads/documents/licenses/Adeoti_Adeolaas_license_1749060944.jpg', 'uploads/documents/vehicle_papers/Adeoti_Adeolaas_vehicle-papers_1749060968.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-picture/1749060460_Map-light.jpg', 'approved', 1, '2025-06-06 12:07:24', ''),
(11, 'James', 'Okoro', 'kolminosto@gufum.com', '08107895954', '$2y$10$zCIun9CsP3PDFeDRsxpY4.8fMO9LEbADIR7pnTjm3yRcEKgm/nr1m', 7, 'Tertiary', NULL, '2025-06-04 20:42:22', NULL, NULL, 0, NULL, NULL, 'Marina', 10, 'LA100001002', 'Hi there! My name is James Okoro, and I\'m a professional driver with over 6 years of experience navigating roads safely and efficiently. I\'m passionate about delivering excellent service, whether it’s transporting passengers, handling logistics, or ensuring timely deliveries. I pride myself on being punctual, courteous, and always maintaining a clean and comfortable vehicle. Safety is my top priority, and I strive to create a smooth, stress-free experience for everyone I drive. When I’m not behind the wheel, I enjoy exploring new places, listening to music, and spending time with my family.', 'Aakintude A. Adeyemi Dr', '', '', 'Car, Bus', 'English', '54682910713', '2000-02-03', 'WEMA BANK PLC', '0285206092', 'ADEOTI ISREAL ADEOLA', 'Driving', NULL, NULL, 'uploads/profile-picture/1749069742_adeboye.png', 'blocked', 1, '2025-06-06 12:07:22', ''),
(13, 'Smart', 'Israel', 'nijag22957@2mik.com', '08107895954', '$2y$10$ooyK88t4gw253EjYb6/.veB5LkJ8mjA/qyTePwnOLhqYBG2GnX0Oi', 16, 'Tertiary', 'uploads/documents/passport/Smart_Israel_passport_1749500204.png', '2025-06-09 20:16:03', NULL, NULL, 0, 'uploads/documents/licenses/Smart_Israel_license_1749500214.jpg', 'uploads/documents/vehicle_papers/Smart_Israel_vehicle-papers_1749500224.jpg', 'Alaba', 4, 'LA100001002', 'Design and develop \"Luna\" - A comprehensive menstrual health tracking application integrating OpenAI\'s AI capabilities with Supabase authentication and storage.\r\n\r\nTechnical Requirements:\r\n\r\n1. Authentication & Data Management:\r\n- Implement Supabase authentication with email/password and social login options\r\n- Create secure database schemas for user profiles and health data\r\n- Ensure HIPAA-compliant data encryption and storage\r\n- Implement automatic data backup and recovery systems\r\n\r\n2. Core Tracking Features:\r\n- Build an intuitive daily logging interface for:\r\n  * Cycle dates (start/end)\r\n  * Flow intensity (5-point scale)\r\n  * Physical symptoms (categorized checklist)\r\n  * Emotional state (mood tracker)\r\n  * Sleep duration and quality\r\n  * Energy levels (1-10 scale)\r\n  * Custom symptoms (user-defined)\r\n- Include timestamp and data validation for all entries\r\n\r\n3. AI Integration (OpenAI):\r\n- Develop prediction models using GPT-4 for:\r\n  * Next cycle forecast (date and duration)\r\n  * Symptom pattern analysis\r\n  * Personalized health insights\r\n- Implement natural language processing for:\r\n  * Conversational health assistant\r\n  * Monthly health summaries\r\n  * Custom recommendations based on tracked patterns\r\n\r\n4. User Interface Requirements:\r\n- Design responsive layouts for mobile and desktop\r\n- Create accessibility-compliant components (WCAG 2.1)\r\n- Implement dark/light modes\r\n- Support multiple languages\r\n- Include customizable dashboards\r\n- Develop interactive data visualizations\r\n\r\n5. Integration Features:\r\n- Build APIs for health app connectivity\r\n- Create wearable device data sync capabilities\r\n- Develop PDF/CSV export functionality\r\n- Include calendar sync options\r\n\r\n6. Privacy & Security:\r\n- Implement end-to-end encryption\r\n- Add two-factor authentication\r\n- Create granular privacy controls\r\n- Include data deletion options\r\n- Maintain compliance with health data regulations\r\n\r\n7. Additional Features:\r\n- Smart notification system\r\n- Community resources section\r\n- Educational content library\r\n- Emergency contact storage\r\n- Medication tracking\r\n- Exercise log integration\r\n\r\nDeliverables:\r\n1. Fully functional web and mobile applications\r\n2. API documentation\r\n3. User guide and privacy policy\r\n4. Administrative dashboard\r\n5. Analytics reporting system\r\n\r\nTimeline:\r\n- Provide a detailed project schedule with milestones\r\n- Include testing phases and user feedback sessions\r\n- Plan for regular updates and maintenance\r\n\r\nThe final product must be thoroughly tested for accuracy, security, and user experience before deployment.', 'Timoshenko, bld. 10, appt. 34', '', '', 'Car, Bus', 'English', '54682910713', '2000-02-09', 'WEMA BANK PLC', '0285206091', 'ADEOTI ISREAL ADEOLA', 'Driving', NULL, NULL, 'uploads/profile-picture/1749500163_adeboye.png', 'approved', 0, NULL, NULL),
(15, 'Adeoti', 'Oluwatosin', 'williamgraphix@gmail.com', '08107895954', '$2y$10$zljj5.fc1e.eCZH/mPl3A.ZGAlP7f3GK2xi135jSdadF.8igI60QS', 6, 'Tertiary', NULL, '2025-06-20 13:11:55', 'e2faab3ab90630aa403bf1fafcc3a1e60d27bb2a0247dcf8a5434cc2ab410a98d0cc65c4ada4376fadcfac6451afba795b83', '2025-06-20 15:43:01', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/profile-picture/1750425115_man.jpg', 'pending', 0, NULL, NULL);

--
-- Dumping data for table `driver_notifications`
--

INSERT INTO `driver_notifications` (`id`, `driver_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 11, 'New Booking Request', 'You have a new booking request. Please check your dashboard.', 'info', 0, '2025-06-05 00:27:00'),
(2, 11, 'New Booking Request', 'You have a new booking request. Please check your dashboard.', 'info', 0, '2025-06-09 19:49:47'),
(3, 11, 'New Booking Request', 'You have a new booking request. Please check your dashboard.', 'info', 0, '2025-06-18 16:42:28');

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
('commission_rate', '10', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('site_email', 'support@prodriverng.com', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('site_name', 'ProDriver NG', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('smtp_host', '', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('smtp_password', '', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('smtp_port', '587', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('smtp_user', '', '2025-06-06 12:15:44', '2025-06-06 12:15:44'),
('support_phone', '', '2025-06-06 12:15:44', '2025-06-06 12:15:44');

--
-- Dumping data for table `verification_checks`
--

INSERT INTO `verification_checks` (`id`, `driver_id`, `check_type`, `status`, `notes`, `verified_by`, `verification_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(2, 7, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(3, 10, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(4, 2, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(5, 11, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(6, 6, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(7, 3, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(8, 5, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(9, 4, 'license', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(10, 1, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(11, 7, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(12, 10, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(13, 2, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(14, 11, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(15, 6, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(16, 3, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(17, 5, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(18, 4, 'nin', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(19, 1, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(20, 7, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(21, 10, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(22, 2, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(23, 11, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(24, 6, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(25, 3, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(26, 5, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(27, 4, 'education', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(28, 1, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(29, 7, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(30, 10, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(31, 2, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(32, 11, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(33, 6, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(34, 3, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(35, 5, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(36, 4, 'bank_details', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(37, 1, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(38, 7, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(39, 10, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(40, 2, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(41, 11, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(42, 6, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(43, 3, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(44, 5, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(45, 4, 'address', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(46, 1, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(47, 7, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(48, 10, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(49, 2, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(50, 11, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(51, 6, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(52, 3, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(53, 5, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(54, 4, 'documents', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(55, 1, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(56, 7, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(57, 10, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(58, 2, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(59, 11, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(60, 6, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(61, 3, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(62, 5, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(63, 4, 'background_check', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(64, 1, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(65, 7, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(66, 10, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(67, 2, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(68, 11, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(69, 6, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(70, 3, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(71, 5, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(72, 4, 'interview', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(73, 1, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(74, 7, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(75, 10, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(76, 2, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(77, 11, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(78, 6, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(79, 3, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(80, 5, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(81, 4, 'training', 'pending', NULL, NULL, NULL, '2025-06-06 11:34:11', '2025-06-06 11:34:11'),
(128, 10, 'nin', 'approved', '', 1, '2025-06-06 12:44:00', '2025-06-06 11:44:00', '2025-06-06 11:44:00'),
(129, 10, 'license', 'approved', '', 1, '2025-06-06 12:44:59', '2025-06-06 11:44:59', '2025-06-06 11:44:59'),
(130, 11, 'license', 'approved', '', 1, '2025-06-06 12:47:25', '2025-06-06 11:47:25', '2025-06-06 11:47:25'),
(131, 11, 'license', 'approved', '', 1, '2025-06-06 12:49:05', '2025-06-06 11:49:05', '2025-06-06 11:49:05'),
(132, 11, 'license', 'approved', '', 1, '2025-06-06 12:53:51', '2025-06-06 11:53:51', '2025-06-06 11:53:51'),
(133, 11, 'nin', 'approved', '', 1, '2025-06-06 12:54:04', '2025-06-06 11:54:04', '2025-06-06 11:54:04'),
(134, 11, 'education', 'approved', '', 1, '2025-06-06 12:54:10', '2025-06-06 11:54:10', '2025-06-06 11:54:10');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
