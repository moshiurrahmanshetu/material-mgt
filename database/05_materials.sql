USE `material_management_system`;

-- Materials table
CREATE TABLE IF NOT EXISTS `materials` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `material_code` VARCHAR(20) NOT NULL,
  `material_name` VARCHAR(100) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `unit` ENUM('Piece', 'Kg', 'Liter', 'Meter', 'Bag', 'Box', 'Roll', 'Packet') NOT NULL,
  `minimum_stock` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `current_stock` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
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
