USE `material_management_system`;

-- Create settings table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `system_name` VARCHAR(255) NOT NULL DEFAULT 'Material Management System',
    `company_name` VARCHAR(255) DEFAULT NULL,
    `company_logo` VARCHAR(255) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `timezone` VARCHAR(100) NOT NULL DEFAULT 'Asia/Dhaka',
    `date_format` VARCHAR(50) NOT NULL DEFAULT 'd-m-Y',
    `updated_by` INT(11) DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `updated_by` (`updated_by`),
    CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings row
INSERT INTO `settings` (`id`, `system_name`, `timezone`, `date_format`) 
VALUES (1, 'Material Management System', 'Asia/Dhaka', 'd-m-Y')
ON DUPLICATE KEY UPDATE `id` = 1;

-- Insert settings.edit permission (settings.view should already exist from Phase 1)
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('settings.edit', 'settings');

-- Get Super Admin role ID
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);

-- Map settings permissions to Super Admin only
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('settings.view', 'settings.edit');
