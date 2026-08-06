USE `material_management_system`;

-- Insert role management permissions
INSERT IGNORE INTO `permissions` (`permission_key`, `module_name`) VALUES
('roles.view', 'roles'),
('roles.create', 'roles'),
('roles.edit', 'roles'),
('roles.delete', 'roles');

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);

-- Map all role management permissions to Super Admin only
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('roles.view', 'roles.create', 'roles.edit', 'roles.delete');

-- Ensure users permissions are mapped to Super Admin only (confirm, don't re-insert if exists)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('users.view', 'users.create', 'users.edit', 'users.delete');
