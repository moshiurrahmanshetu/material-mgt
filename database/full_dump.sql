-- Material Management System - Full Database Dump
-- This file contains all tables and seed data for the application
-- Import this during installation using the installer wizard

-- Roles table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions table
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `permission_key` VARCHAR(100) NOT NULL,
  `module_name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions junction table
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role_id` INT(11) NOT NULL,
  `permission_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
  KEY `fk_role_permissions_role` (`role_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_code` VARCHAR(20) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `role_id` INT(11) NOT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_code` (`user_code`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `unique_username` (`username`),
  KEY `fk_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log table
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_activity_log_user` (`user_id`),
  CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts table for brute-force protection
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempt_count` INT(11) NOT NULL DEFAULT 1,
  `last_attempt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `locked_until` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_username` (`username`),
  KEY `idx_login_attempts_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`role_name`, `description`, `status`) VALUES
('Super Admin', 'Full system access with all permissions', 'Active'),
('Store Manager', 'Manage inventory, purchases, and stock', 'Active'),
('Staff', 'View and manage assigned materials', 'Active'),
('Viewer', 'Read-only access to most modules', 'Active');

-- Get the role IDs for Super Admin (will be 1)
SET @super_admin_role_id = 1;

-- Insert base permissions
INSERT INTO `permissions` (`permission_key`, `module_name`) VALUES
-- Dashboard
('dashboard.view', 'dashboard'),

-- Users
('users.view', 'users'),
('users.create', 'users'),
('users.edit', 'users'),
('users.delete', 'users'),

-- Roles
('roles.view', 'roles'),
('roles.create', 'roles'),
('roles.edit', 'roles'),
('roles.delete', 'roles'),

-- Materials
('materials.view', 'materials'),
('materials.create', 'materials'),
('materials.edit', 'materials'),
('materials.delete', 'materials'),

-- Categories
('categories.view', 'categories'),
('categories.create', 'categories'),
('categories.edit', 'categories'),
('categories.delete', 'categories'),

-- Suppliers
('suppliers.view', 'suppliers'),
('suppliers.create', 'suppliers'),
('suppliers.edit', 'suppliers'),
('suppliers.delete', 'suppliers'),

-- Purchase
('purchase.view', 'purchase'),
('purchase.create', 'purchase'),
('purchase.edit', 'purchase'),
('purchase.delete', 'purchase'),
('purchase.approve', 'purchase'),

-- Issue
('issue.view', 'issue'),
('issue.create', 'issue'),
('issue.edit', 'issue'),
('issue.delete', 'issue'),
('issue.approve', 'issue'),

-- Request
('request.view', 'request'),
('request.create', 'request'),
('request.edit', 'request'),
('request.delete', 'request'),
('request.approve', 'request'),

-- Stock
('stock.view', 'stock'),
('stock.adjust', 'stock'),

-- Reports
('reports.view', 'reports'),
('reports.export', 'reports'),

-- Activity Log
('activity_log.view', 'activity-log'),
('activity_log.delete', 'activity-log'),

-- Settings
('settings.view', 'settings'),
('settings.edit', 'settings');

-- Map ALL permissions to Super Admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions`;

-- Insert default admin user
-- Password hash for 'Admin@123' generated using PHP password_hash()
INSERT INTO `users` (`user_code`, `full_name`, `email`, `username`, `password`, `phone`, `avatar`, `role_id`, `status`) VALUES
('USR-000001', 'System Administrator', 'admin@mms.local', 'admin', '$2y$10$y8wCeF69NHVYeyS7K7mmROc1cI8PgxKXDPpksyOVwqzVEHLhtGmBa', NULL, NULL, @super_admin_role_id, 'Active');

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_code` VARCHAR(20) NOT NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_code` (`category_code`),
  KEY `fk_categories_created_by` (`created_by`),
  CONSTRAINT `fk_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Materials table
CREATE TABLE IF NOT EXISTS `materials` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `material_code` VARCHAR(20) NOT NULL,
  `material_name` VARCHAR(200) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `unit` VARCHAR(20) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `minimum_stock` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `current_stock` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_material_code` (`material_code`),
  KEY `fk_materials_category` (`category_id`),
  KEY `fk_materials_created_by` (`created_by`),
  CONSTRAINT `fk_materials_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_materials_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `supplier_code` VARCHAR(20) NOT NULL,
  `supplier_name` VARCHAR(200) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_supplier_code` (`supplier_code`),
  KEY `fk_suppliers_created_by` (`created_by`),
  CONSTRAINT `fk_suppliers_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements table
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `material_id` INT(11) NOT NULL,
  `movement_type` ENUM('Purchase', 'Issue', 'Return', 'Adjustment') NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `reference_id` INT(11) DEFAULT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stock_movements_material` (`material_id`),
  KEY `fk_stock_movements_created_by` (`created_by`),
  CONSTRAINT `fk_stock_movements_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stock_movements_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Master table
CREATE TABLE IF NOT EXISTS `purchase_master` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `purchase_no` VARCHAR(20) NOT NULL,
  `supplier_id` INT(11) NOT NULL,
  `purchase_date` DATE NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Pending', 'Approved', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_purchase_no` (`purchase_no`),
  KEY `fk_purchase_master_supplier` (`supplier_id`),
  KEY `fk_purchase_master_created_by` (`created_by`),
  CONSTRAINT `fk_purchase_master_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_purchase_master_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase Details table
CREATE TABLE IF NOT EXISTS `purchase_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` INT(11) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_purchase_details_purchase` (`purchase_id`),
  KEY `fk_purchase_details_material` (`material_id`),
  CONSTRAINT `fk_purchase_details_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchase_master` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_purchase_details_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Material Requests table
CREATE TABLE IF NOT EXISTS `material_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_no` VARCHAR(20) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `requested_by` INT(11) NOT NULL,
  `request_date` DATE NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `purpose` TEXT DEFAULT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Completed') NOT NULL DEFAULT 'Pending',
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_request_no` (`request_no`),
  KEY `fk_material_requests_material` (`material_id`),
  KEY `fk_material_requests_requested_by` (`requested_by`),
  KEY `fk_material_requests_approved_by` (`approved_by`),
  CONSTRAINT `fk_material_requests_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_material_requests_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_material_requests_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Material Issues table
CREATE TABLE IF NOT EXISTS `material_issues` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `issue_no` VARCHAR(20) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `employee_id` INT(11) NOT NULL,
  `issue_date` DATE NOT NULL,
  `issue_quantity` DECIMAL(10,2) NOT NULL,
  `purpose` TEXT DEFAULT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Completed') NOT NULL DEFAULT 'Pending',
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_issue_no` (`issue_no`),
  KEY `fk_material_issues_material` (`material_id`),
  KEY `fk_material_issues_employee` (`employee_id`),
  KEY `fk_material_issues_approved_by` (`approved_by`),
  CONSTRAINT `fk_material_issues_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_material_issues_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_material_issues_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Material Returns table
CREATE TABLE IF NOT EXISTS `material_returns` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `return_no` VARCHAR(20) NOT NULL,
  `issue_id` INT(11) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `returned_by` INT(11) NOT NULL,
  `return_date` DATE NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_return_no` (`return_no`),
  KEY `fk_material_returns_issue` (`issue_id`),
  KEY `fk_material_returns_material` (`material_id`),
  KEY `fk_material_returns_returned_by` (`returned_by`),
  CONSTRAINT `fk_material_returns_issue` FOREIGN KEY (`issue_id`) REFERENCES `material_issues` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_material_returns_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_material_returns_returned_by` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) NOT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  `company_name` VARCHAR(200) DEFAULT NULL,
  `company_logo` VARCHAR(255) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `timezone` VARCHAR(50) DEFAULT 'Asia/Dhaka',
  `date_format` VARCHAR(20) DEFAULT 'd-m-Y',
  `updated_by` INT(11) DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_settings_updated_by` (`updated_by`),
  CONSTRAINT `fk_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings row
INSERT INTO `settings` (`id`, `system_name`, `company_name`, `timezone`, `date_format`) VALUES
(1, 'Material Management System', 'My Company', 'Asia/Dhaka', 'd-m-Y');
