USE `material_management_system`;

-- Suppliers table
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `supplier_code` VARCHAR(20) NOT NULL,
  `supplier_name` VARCHAR(100) NOT NULL,
  `company` VARCHAR(100) DEFAULT NULL,
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

-- Insert supplier permissions
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('suppliers.view', 'suppliers'),
('suppliers.create', 'suppliers'),
('suppliers.edit', 'suppliers'),
('suppliers.delete', 'suppliers');

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @store_manager_role_id = (SELECT id FROM roles WHERE role_name = 'Store Manager' LIMIT 1);
SET @viewer_role_id = (SELECT id FROM roles WHERE role_name = 'Viewer' LIMIT 1);

-- Map all permissions to Super Admin
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete');

-- Map all permissions to Store Manager
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key IN ('suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete');

-- Map view permission to Viewer
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @viewer_role_id, id FROM `permissions` 
WHERE permission_key = 'suppliers.view';
