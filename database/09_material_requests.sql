USE `material_management_system`;

-- Material requests table
CREATE TABLE IF NOT EXISTS `material_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_no` VARCHAR(20) NOT NULL,
  `employee_id` INT(11) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `material_id` INT(11) NOT NULL,
  `requested_quantity` DECIMAL(10,2) NOT NULL,
  `purpose` TEXT DEFAULT NULL,
  `request_date` DATE NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Issued') NOT NULL DEFAULT 'Pending',
  `approved_by` INT(11) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_request_no` (`request_no`),
  KEY `fk_requests_employee` (`employee_id`),
  KEY `fk_requests_material` (`material_id`),
  KEY `fk_requests_approved_by` (`approved_by`),
  KEY `idx_request_status` (`status`),
  KEY `idx_request_date` (`request_date`),
  CONSTRAINT `fk_requests_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_requests_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_requests_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
