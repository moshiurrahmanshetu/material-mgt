-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2026 at 09:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `material_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `module`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'Login', 'Auth', 'User logged in successfully', '::1', '2026-08-06 18:15:18'),
(2, 1, 'Logout', 'Auth', 'User logged out', '::1', '2026-08-06 18:38:39'),
(3, 1, 'Login', 'Auth', 'User logged in successfully', '::1', '2026-08-06 18:56:38'),
(4, 1, 'Create', 'Categories', 'Created category: Electronics', '::1', '2026-08-06 18:56:57'),
(5, 1, 'Create', 'Materials', 'Created material: sdfdzf s', '::1', '2026-08-06 18:57:18'),
(6, 1, 'Update', 'Materials', 'Updated material ID: 1', '::1', '2026-08-06 18:57:38'),
(7, 1, 'Update', 'Categories', 'Updated category ID: 1', '::1', '2026-08-06 18:57:49'),
(8, 1, 'Delete', 'Materials', 'Deleted material ID: 1', '::1', '2026-08-06 18:58:02'),
(9, 1, 'Delete', 'Categories', 'Deleted category ID: 1', '::1', '2026-08-06 18:58:06'),
(10, 1, 'Login', 'Auth', 'User logged in successfully', '::1', '2026-08-07 05:05:01'),
(11, 1, 'Update', 'Profile', 'Avatar updated', '::1', '2026-08-07 05:13:25'),
(12, 1, 'Update', 'Profile', 'Avatar updated', '::1', '2026-08-07 05:13:25'),
(13, 1, 'Login', 'Auth', 'User logged in successfully', '::1', '2026-08-07 05:54:45'),
(14, 1, 'Create', 'Categories', 'Created category: Electronics 2', '::1', '2026-08-07 06:20:25'),
(15, 1, 'Create', 'Materials', 'Created material: efsafs fds', '::1', '2026-08-07 06:20:42'),
(16, 1, 'Create', 'Suppliers', 'Created supplier: rsrvsf', '::1', '2026-08-07 06:20:56'),
(17, 1, 'Create', 'Purchase', 'Created purchase: PUR-000001', '::1', '2026-08-07 06:22:02'),
(18, 1, 'Create', 'Request', 'Created request: REQ-000001', '::1', '2026-08-07 06:29:28'),
(19, 1, 'Approve', 'Request', 'Approved request: REQ-000001', '::1', '2026-08-07 06:29:47'),
(20, 1, 'Logout', 'Auth', 'User logged out', '::1', '2026-08-07 06:30:16'),
(21, 1, 'Login', 'Auth', 'User logged in successfully', '::1', '2026-08-07 06:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_code`, `category_name`, `description`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'CAT-000001', 'Electronics 2', 'r dsacfsdafas', 'Active', 1, '2026-08-07 06:20:25', '2026-08-07 06:20:25');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempt_count`, `last_attempt`, `locked_until`) VALUES
(2, 'admin', '::1', 1, '2026-08-06 18:55:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `material_code` varchar(20) NOT NULL,
  `material_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `unit` enum('Piece','Kg','Liter','Meter','Bag','Box','Roll','Packet') NOT NULL,
  `minimum_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `material_code`, `material_name`, `category_id`, `unit`, `minimum_stock`, `current_stock`, `description`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'MAT-000001', 'efsafs fds', 2, 'Meter', 45.00, 44.00, 'recrsdf', 'Active', 1, '2026-08-07 06:20:42', '2026-08-07 06:22:02');

-- --------------------------------------------------------

--
-- Table structure for table `material_issues`
--

CREATE TABLE `material_issues` (
  `id` int(11) NOT NULL,
  `issue_no` varchar(20) NOT NULL,
  `request_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `issue_quantity` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `issued_by` int(11) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_requests`
--

CREATE TABLE `material_requests` (
  `id` int(11) NOT NULL,
  `request_no` varchar(20) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `material_id` int(11) NOT NULL,
  `requested_quantity` decimal(10,2) NOT NULL,
  `purpose` text DEFAULT NULL,
  `request_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected','Issued') NOT NULL DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_requests`
--

INSERT INTO `material_requests` (`id`, `request_no`, `employee_id`, `department`, `material_id`, `requested_quantity`, `purpose`, `request_date`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 'REQ-000001', 1, 'zfdfsf', 2, 10.00, '', '2026-08-07', 'Approved', 1, '2026-08-07 12:29:47', NULL, '2026-08-07 06:29:28', '2026-08-07 06:29:47');

-- --------------------------------------------------------

--
-- Table structure for table `material_returns`
--

CREATE TABLE `material_returns` (
  `id` int(11) NOT NULL,
  `return_no` varchar(20) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `return_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `module_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_key`, `module_name`, `created_at`) VALUES
(1, 'dashboard.view', 'dashboard', '2026-08-06 18:12:42'),
(2, 'users.view', 'users', '2026-08-06 18:12:42'),
(3, 'users.create', 'users', '2026-08-06 18:12:42'),
(4, 'users.edit', 'users', '2026-08-06 18:12:42'),
(5, 'users.delete', 'users', '2026-08-06 18:12:42'),
(6, 'roles.view', 'roles', '2026-08-06 18:12:42'),
(7, 'roles.create', 'roles', '2026-08-06 18:12:42'),
(8, 'roles.edit', 'roles', '2026-08-06 18:12:42'),
(9, 'roles.delete', 'roles', '2026-08-06 18:12:42'),
(10, 'materials.view', 'materials', '2026-08-06 18:12:42'),
(11, 'materials.create', 'materials', '2026-08-06 18:12:42'),
(12, 'materials.edit', 'materials', '2026-08-06 18:12:42'),
(13, 'materials.delete', 'materials', '2026-08-06 18:12:42'),
(14, 'categories.view', 'categories', '2026-08-06 18:12:42'),
(15, 'categories.create', 'categories', '2026-08-06 18:12:42'),
(16, 'categories.edit', 'categories', '2026-08-06 18:12:42'),
(17, 'categories.delete', 'categories', '2026-08-06 18:12:42'),
(18, 'suppliers.view', 'suppliers', '2026-08-06 18:12:42'),
(19, 'suppliers.create', 'suppliers', '2026-08-06 18:12:42'),
(20, 'suppliers.edit', 'suppliers', '2026-08-06 18:12:42'),
(21, 'suppliers.delete', 'suppliers', '2026-08-06 18:12:42'),
(22, 'purchase.view', 'purchase', '2026-08-06 18:12:42'),
(23, 'purchase.create', 'purchase', '2026-08-06 18:12:42'),
(24, 'purchase.edit', 'purchase', '2026-08-06 18:12:42'),
(25, 'purchase.delete', 'purchase', '2026-08-06 18:12:42'),
(26, 'purchase.approve', 'purchase', '2026-08-06 18:12:42'),
(27, 'issue.view', 'issue', '2026-08-06 18:12:42'),
(28, 'issue.create', 'issue', '2026-08-06 18:12:42'),
(29, 'issue.edit', 'issue', '2026-08-06 18:12:42'),
(30, 'issue.delete', 'issue', '2026-08-06 18:12:42'),
(31, 'issue.approve', 'issue', '2026-08-06 18:12:42'),
(32, 'request.view', 'request', '2026-08-06 18:12:42'),
(33, 'request.create', 'request', '2026-08-06 18:12:42'),
(34, 'request.edit', 'request', '2026-08-06 18:12:42'),
(35, 'request.delete', 'request', '2026-08-06 18:12:42'),
(36, 'request.approve', 'request', '2026-08-06 18:12:42'),
(37, 'stock.view', 'stock', '2026-08-06 18:12:42'),
(38, 'stock.adjust', 'stock', '2026-08-06 18:12:42'),
(39, 'reports.view', 'reports', '2026-08-06 18:12:42'),
(40, 'reports.export', 'reports', '2026-08-06 18:12:42'),
(41, 'activity_log.view', 'activity-log', '2026-08-06 18:12:42'),
(42, 'activity_log.delete', 'activity-log', '2026-08-06 18:12:42'),
(43, 'settings.view', 'settings', '2026-08-06 18:12:42'),
(44, 'settings.edit', 'settings', '2026-08-06 18:12:42'),
(64, 'return.view', 'return', '2026-08-06 19:17:04'),
(65, 'return.create', 'return', '2026-08-06 19:17:04');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `material_id`, `quantity`, `unit_price`, `total`) VALUES
(1, 1, 2, 4.00, 400.00, 1600.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_master`
--

CREATE TABLE `purchase_master` (
  `id` int(11) NOT NULL,
  `purchase_no` varchar(20) NOT NULL,
  `purchase_date` date NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_master`
--

INSERT INTO `purchase_master` (`id`, `purchase_no`, `purchase_date`, `supplier_id`, `invoice_number`, `total_amount`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PUR-000001', '2026-08-06', 1, 'erwcw', 1600.00, 'rcwcds', 1, '2026-08-07 06:22:01', '2026-08-07 06:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Full system access with all permissions', 'Active', '2026-08-06 18:12:41', '2026-08-06 18:12:41'),
(2, 'Store Manager', 'Manage inventory, purchases, and stock', 'Active', '2026-08-06 18:12:41', '2026-08-06 18:12:41'),
(3, 'Staff', 'View and manage assigned materials', 'Active', '2026-08-06 18:12:41', '2026-08-06 18:12:41'),
(4, 'Viewer', 'Read-only access to most modules', 'Active', '2026-08-06 18:12:41', '2026-08-06 18:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(7, 1, 1),
(44, 1, 2),
(41, 1, 3),
(43, 1, 4),
(42, 1, 5),
(32, 1, 6),
(29, 1, 7),
(31, 1, 8),
(30, 1, 9),
(16, 1, 10),
(13, 1, 11),
(15, 1, 12),
(14, 1, 13),
(6, 1, 14),
(3, 1, 15),
(5, 1, 16),
(4, 1, 17),
(40, 1, 18),
(37, 1, 19),
(39, 1, 20),
(38, 1, 21),
(21, 1, 22),
(18, 1, 23),
(20, 1, 24),
(19, 1, 25),
(17, 1, 26),
(12, 1, 27),
(9, 1, 28),
(11, 1, 29),
(10, 1, 30),
(8, 1, 31),
(28, 1, 32),
(25, 1, 33),
(27, 1, 34),
(26, 1, 35),
(24, 1, 36),
(36, 1, 37),
(35, 1, 38),
(23, 1, 39),
(22, 1, 40),
(2, 1, 41),
(1, 1, 42),
(34, 1, 43),
(33, 1, 44),
(104, 1, 64),
(103, 1, 65),
(67, 2, 10),
(72, 2, 11),
(74, 2, 12),
(73, 2, 13),
(66, 2, 14),
(69, 2, 15),
(71, 2, 16),
(70, 2, 17),
(83, 2, 18),
(80, 2, 19),
(82, 2, 20),
(81, 2, 21),
(90, 2, 22),
(89, 2, 23),
(95, 2, 27),
(94, 2, 28),
(93, 2, 31),
(97, 2, 32),
(96, 2, 33),
(108, 2, 37),
(112, 2, 39),
(107, 2, 64),
(106, 2, 65),
(101, 3, 32),
(100, 3, 33),
(77, 4, 10),
(76, 4, 14),
(87, 4, 18),
(109, 4, 37),
(113, 4, 39);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `system_name` varchar(255) NOT NULL DEFAULT 'Material Management System',
  `company_name` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `timezone` varchar(100) NOT NULL DEFAULT 'Asia/Dhaka',
  `date_format` varchar(50) NOT NULL DEFAULT 'd-m-Y',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `system_name`, `company_name`, `company_logo`, `address`, `phone`, `email`, `timezone`, `date_format`, `updated_by`, `updated_at`) VALUES
(1, 'Material Management System', NULL, NULL, NULL, NULL, NULL, 'Asia/Dhaka', 'd-m-Y', NULL, '2026-08-07 05:02:57');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `movement_type` enum('Purchase','Issue','Return','Adjustment') NOT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `quantity_change` decimal(10,2) NOT NULL,
  `previous_stock` decimal(10,2) NOT NULL,
  `new_stock` decimal(10,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `material_id`, `movement_type`, `reference_no`, `quantity_change`, `previous_stock`, `new_stock`, `remarks`, `created_by`, `created_at`) VALUES
(1, 2, 'Purchase', 'PUR-000001', 4.00, 40.00, 44.00, 'Purchase: PUR-000001', 1, '2026-08-07 06:22:02');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_code` varchar(20) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `supplier_code`, `supplier_name`, `company`, `contact_person`, `phone`, `email`, `address`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'SUP-000001', 'rsrvsf', 'fscscfasf', 'sdfcsf', '23442423', 'atomicshetu@gmail.com', 'fdsgds\r\ndfg fdsg', 'Active', 1, '2026-08-07 06:20:56', '2026-08-07 06:20:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_code` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_code`, `full_name`, `email`, `username`, `password`, `phone`, `avatar`, `role_id`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'USR-000001', 'System Administrator', 'admin@mms.local', 'admin', '$2y$10$3GVtIE7ONdHdryLMny0W/exFeII3ZFynVfxqnnAnmgMbs3ocBRKu6', NULL, 'avatar_6a75697514ca2.jpg', 1, 'Active', '2026-08-07 12:36:20', '2026-08-06 18:12:42', '2026-08-07 06:36:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_log_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_code` (`category_code`),
  ADD UNIQUE KEY `unique_category_name` (`category_name`),
  ADD KEY `fk_categories_created_by` (`created_by`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_username` (`username`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_material_code` (`material_code`),
  ADD KEY `fk_materials_category` (`category_id`),
  ADD KEY `fk_materials_created_by` (`created_by`);

--
-- Indexes for table `material_issues`
--
ALTER TABLE `material_issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_issue_no` (`issue_no`),
  ADD KEY `fk_issues_request` (`request_id`),
  ADD KEY `fk_issues_employee` (`employee_id`),
  ADD KEY `fk_issues_material` (`material_id`),
  ADD KEY `fk_issues_issued_by` (`issued_by`),
  ADD KEY `idx_issue_date` (`issue_date`);

--
-- Indexes for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request_no` (`request_no`),
  ADD KEY `fk_requests_employee` (`employee_id`),
  ADD KEY `fk_requests_material` (`material_id`),
  ADD KEY `fk_requests_approved_by` (`approved_by`),
  ADD KEY `idx_request_status` (`status`),
  ADD KEY `idx_request_date` (`request_date`);

--
-- Indexes for table `material_returns`
--
ALTER TABLE `material_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_return_no` (`return_no`),
  ADD KEY `fk_returns_issue` (`issue_id`),
  ADD KEY `fk_returns_material` (`material_id`),
  ADD KEY `fk_returns_created_by` (`created_by`),
  ADD KEY `idx_return_date` (`return_date`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permission_key` (`permission_key`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_purchase_items_purchase` (`purchase_id`),
  ADD KEY `fk_purchase_items_material` (`material_id`);

--
-- Indexes for table `purchase_master`
--
ALTER TABLE `purchase_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_purchase_no` (`purchase_no`),
  ADD KEY `fk_purchase_supplier` (`supplier_id`),
  ADD KEY `fk_purchase_created_by` (`created_by`),
  ADD KEY `idx_purchase_date` (`purchase_date`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_role` (`role_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_movements_material` (`material_id`),
  ADD KEY `fk_stock_movements_created_by` (`created_by`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `idx_reference_no` (`reference_no`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_supplier_code` (`supplier_code`),
  ADD KEY `fk_suppliers_created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_code` (`user_code`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `material_issues`
--
ALTER TABLE `material_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_requests`
--
ALTER TABLE `material_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `material_returns`
--
ALTER TABLE `material_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_master`
--
ALTER TABLE `purchase_master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `fk_materials_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_materials_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `material_issues`
--
ALTER TABLE `material_issues`
  ADD CONSTRAINT `fk_issues_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_issues_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_issues_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `fk_issues_request` FOREIGN KEY (`request_id`) REFERENCES `material_requests` (`id`);

--
-- Constraints for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD CONSTRAINT `fk_requests_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_requests_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_requests_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `material_returns`
--
ALTER TABLE `material_returns`
  ADD CONSTRAINT `fk_returns_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_returns_issue` FOREIGN KEY (`issue_id`) REFERENCES `material_issues` (`id`),
  ADD CONSTRAINT `fk_returns_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `fk_purchase_items_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `fk_purchase_items_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchase_master` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_master`
--
ALTER TABLE `purchase_master`
  ADD CONSTRAINT `fk_purchase_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_purchase_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_stock_movements_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stock_movements_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `fk_suppliers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
