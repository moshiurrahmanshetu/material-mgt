USE `material_management_system`;

-- Insert activity_log permission
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('activity_log.view', 'activity_log');

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @store_manager_role_id = (SELECT id FROM roles WHERE role_name = 'Store Manager' LIMIT 1);
SET @viewer_role_id = (SELECT id FROM roles WHERE role_name = 'Viewer' LIMIT 1);

-- Map activity_log.view to Super Admin only (audit/security feature)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key = 'activity_log.view';

-- Ensure reports.view is mapped to Super Admin, Store Manager, and Viewer
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key = 'reports.view';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key = 'reports.view';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @viewer_role_id, id FROM `permissions` 
WHERE permission_key = 'reports.view';
