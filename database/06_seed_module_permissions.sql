USE `material_management_system`;

-- Get role IDs
SET @super_admin_role_id = (SELECT id FROM roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @store_manager_role_id = (SELECT id FROM roles WHERE role_name = 'Store Manager' LIMIT 1);
SET @viewer_role_id = (SELECT id FROM roles WHERE role_name = 'Viewer' LIMIT 1);

-- Map all permissions to Super Admin
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions` 
WHERE permission_key IN ('categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                       'materials.view', 'materials.create', 'materials.edit', 'materials.delete');

-- Map view permissions to Store Manager
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key IN ('categories.view', 'materials.view');

-- Map full CRUD permissions to Store Manager
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @store_manager_role_id, id FROM `permissions` 
WHERE permission_key IN ('categories.create', 'categories.edit', 'categories.delete',
                       'materials.create', 'materials.edit', 'materials.delete');

-- Map view permissions to Viewer
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @viewer_role_id, id FROM `permissions` 
WHERE permission_key IN ('categories.view', 'materials.view');
