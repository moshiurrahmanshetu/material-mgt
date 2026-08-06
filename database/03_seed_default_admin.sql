USE `material_management_system`;

-- Insert default roles
INSERT INTO `roles` (`role_name`, `description`, `status`) VALUES
('Super Admin', 'Full system access with all permissions', 'Active'),
('Store Manager', 'Manage inventory, purchases, and stock', 'Active'),
('Staff', 'View and manage assigned materials', 'Active'),
('Viewer', 'Read-only access to most modules', 'Active');

-- Get the role IDs for Super Admin (will be 1)
SET @super_admin_role_id = 1;

-- Insert base permissions
INSERT INTO `permissions` (`permission_key`, `module_name`) VALUES
-- Dashboard
('dashboard.view', 'dashboard'),

-- Users
('users.view', 'users'),
('users.create', 'users'),
('users.edit', 'users'),
('users.delete', 'users'),

-- Roles
('roles.view', 'roles'),
('roles.create', 'roles'),
('roles.edit', 'roles'),
('roles.delete', 'roles'),

-- Materials
('materials.view', 'materials'),
('materials.create', 'materials'),
('materials.edit', 'materials'),
('materials.delete', 'materials'),

-- Categories
('categories.view', 'categories'),
('categories.create', 'categories'),
('categories.edit', 'categories'),
('categories.delete', 'categories'),

-- Suppliers
('suppliers.view', 'suppliers'),
('suppliers.create', 'suppliers'),
('suppliers.edit', 'suppliers'),
('suppliers.delete', 'suppliers'),

-- Purchase
('purchase.view', 'purchase'),
('purchase.create', 'purchase'),
('purchase.edit', 'purchase'),
('purchase.delete', 'purchase'),
('purchase.approve', 'purchase'),

-- Issue
('issue.view', 'issue'),
('issue.create', 'issue'),
('issue.edit', 'issue'),
('issue.delete', 'issue'),
('issue.approve', 'issue'),

-- Request
('request.view', 'request'),
('request.create', 'request'),
('request.edit', 'request'),
('request.delete', 'request'),
('request.approve', 'request'),

-- Stock
('stock.view', 'stock'),
('stock.adjust', 'stock'),

-- Reports
('reports.view', 'reports'),
('reports.export', 'reports'),

-- Activity Log
('activity_log.view', 'activity-log'),
('activity_log.delete', 'activity-log'),

-- Settings
('settings.view', 'settings'),
('settings.edit', 'settings');

-- Map ALL permissions to Super Admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @super_admin_role_id, id FROM `permissions`;

-- Insert default admin user
-- Password hash for 'Admin@123' generated using PHP password_hash()
INSERT INTO `users` (`user_code`, `full_name`, `email`, `username`, `password`, `phone`, `avatar`, `role_id`, `status`) VALUES
('USR-000001', 'System Administrator', 'admin@mms.local', 'admin', '$2y$10$y8wCeF69NHVYeyS7K7mmROc1cI8PgxKXDPpksyOVwqzVEHLhtGmBa', NULL, NULL, @super_admin_role_id, 'Active');
