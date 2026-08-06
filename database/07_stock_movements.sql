USE `material_management_system`;

-- Stock movements table (shared ledger for Purchase, Issue, Return, Adjustment)
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `material_id` INT(11) NOT NULL,
  `movement_type` ENUM('Purchase', 'Issue', 'Return', 'Adjustment') NOT NULL,
  `reference_no` VARCHAR(50) DEFAULT NULL,
  `quantity_change` DECIMAL(10,2) NOT NULL,
  `previous_stock` DECIMAL(10,2) NOT NULL,
  `new_stock` DECIMAL(10,2) NOT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_stock_movements_material` (`material_id`),
  KEY `fk_stock_movements_created_by` (`created_by`),
  KEY `idx_movement_type` (`movement_type`),
  KEY `idx_reference_no` (`reference_no`),
  CONSTRAINT `fk_stock_movements_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_stock_movements_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
