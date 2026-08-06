USE `material_management_system`;

-- Material returns table
CREATE TABLE IF NOT EXISTS `material_returns` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `return_no` VARCHAR(20) NOT NULL,
  `issue_id` INT(11) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `return_date` DATE NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_return_no` (`return_no`),
  KEY `fk_returns_issue` (`issue_id`),
  KEY `fk_returns_material` (`material_id`),
  KEY `fk_returns_created_by` (`created_by`),
  KEY `idx_return_date` (`return_date`),
  CONSTRAINT `fk_returns_issue` FOREIGN KEY (`issue_id`) REFERENCES `material_issues` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_returns_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_returns_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert return and stock permissions
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('return.view', 'return'),
('return.create', 'return'),
('stock.view', 'stock');

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @store_manager_role_id = (SELECT id FROM roles WHERE role_name = 'Store Manager' LIMIT 1);
SET @viewer_role_id = (SELECT id FROM roles WHERE role_name = 'Viewer' LIMIT 1);

-- Map all permissions to Super Admin
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('return.view', 'return.create', 'stock.view');

-- Map all permissions to Store Manager
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key IN ('return.view', 'return.create', 'stock.view');

-- Map stock.view permission to Viewer
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @viewer_role_id, id FROM `permissions` 
WHERE permission_key = 'stock.view';
