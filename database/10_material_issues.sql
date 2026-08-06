USE `material_management_system`;

-- Material issues table
CREATE TABLE IF NOT EXISTS `material_issues` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `issue_no` VARCHAR(20) NOT NULL,
  `request_id` INT(11) NOT NULL,
  `employee_id` INT(11) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `issue_quantity` DECIMAL(10,2) NOT NULL,
  `issue_date` DATE NOT NULL,
  `issued_by` INT(11) NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_issue_no` (`issue_no`),
  KEY `fk_issues_request` (`request_id`),
  KEY `fk_issues_employee` (`employee_id`),
  KEY `fk_issues_material` (`material_id`),
  KEY `fk_issues_issued_by` (`issued_by`),
  KEY `idx_issue_date` (`issue_date`),
  CONSTRAINT `fk_issues_request` FOREIGN KEY (`request_id`) REFERENCES `material_requests` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_issues_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_issues_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_issues_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert request and issue permissions
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('request.view', 'request'),
('request.create', 'request'),
('issue.view', 'issue'),
('issue.create', 'issue'),
('issue.approve', 'issue');

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @store_manager_role_id = (SELECT id FROM roles WHERE role_name = 'Store Manager' LIMIT 1);
SET @staff_role_id = (SELECT id FROM roles WHERE role_name = 'Staff' LIMIT 1);

-- Map all permissions to Super Admin
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('request.view', 'request.create', 'issue.view', 'issue.create', 'issue.approve');

-- Map all permissions to Store Manager
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key IN ('request.view', 'request.create', 'issue.view', 'issue.create', 'issue.approve');

-- Map view and create permissions to Staff
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @staff_role_id, id FROM `permissions` 
WHERE permission_key IN ('request.view', 'request.create');
