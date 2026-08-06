USE `material_management_system`;

-- Purchase master table
CREATE TABLE IF NOT EXISTS `purchase_master` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `purchase_no` VARCHAR(20) NOT NULL,
  `purchase_date` DATE NOT NULL,
  `supplier_id` INT(11) NOT NULL,
  `invoice_number` VARCHAR(50) DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `remarks` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_purchase_no` (`purchase_no`),
  KEY `fk_purchase_supplier` (`supplier_id`),
  KEY `fk_purchase_created_by` (`created_by`),
  KEY `idx_purchase_date` (`purchase_date`),
  CONSTRAINT `fk_purchase_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_purchase_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchase items table
CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` INT(11) NOT NULL,
  `material_id` INT(11) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_purchase_items_purchase` (`purchase_id`),
  KEY `fk_purchase_items_material` (`material_id`),
  CONSTRAINT `fk_purchase_items_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchase_master` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_purchase_items_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert purchase permissions
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('purchase.view', 'purchase'),
('purchase.create', 'purchase');

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @store_manager_role_id = (SELECT id FROM roles WHERE role_name = 'Store Manager' LIMIT 1);

-- Map purchase permissions to Super Admin
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('purchase.view', 'purchase.create');

-- Map purchase permissions to Store Manager
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key IN ('purchase.view', 'purchase.create');
