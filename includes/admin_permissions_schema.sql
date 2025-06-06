-- جدول صلاحيات المدراء (لكل مدير صلاحياته)
CREATE TABLE IF NOT EXISTS admin_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    permission_key VARCHAR(50) NOT NULL, -- مثال: manage_agencies, manage_pilgrims, manage_admins, manage_offers
    allow_view TINYINT(1) DEFAULT 0,
    allow_add TINYINT(1) DEFAULT 0,
    allow_edit TINYINT(1) DEFAULT 0,
    allow_delete TINYINT(1) DEFAULT 0,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- مثال إدخال صلاحيات لمدير ثانوي (admin_id=2)
INSERT INTO admin_permissions (admin_id, permission_key, allow_view, allow_add, allow_edit, allow_delete) VALUES
(2, 'manage_agencies', 1, 1, 1, 1),
(2, 'manage_pilgrims', 0, 0, 0, 0),
(2, 'manage_admins', 0, 0, 0, 0),
(2, 'manage_offers', 1, 1, 1, 1);
