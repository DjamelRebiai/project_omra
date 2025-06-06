-- قاعدة بيانات منصة العمرة في Supabase (PostgreSQL)

-- جدول المديرين
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    permissions VARCHAR(20) DEFAULT 'both', -- We'll use CHECK constraint below
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT permissions_check CHECK (permissions IN ('agencies', 'pilgrims', 'both'))
);

-- جدول صلاحيات المدراء
CREATE TABLE IF NOT EXISTS admin_permissions (
    id SERIAL PRIMARY KEY,
    admin_id INT NOT NULL REFERENCES admins(id) ON DELETE CASCADE,
    permission_key VARCHAR(50) NOT NULL, -- مثال: manage_agencies, manage_pilgrims, manage_admins, manage_offers
    allow_view BOOLEAN DEFAULT FALSE,
    allow_add BOOLEAN DEFAULT FALSE,
    allow_edit BOOLEAN DEFAULT FALSE,
    allow_delete BOOLEAN DEFAULT FALSE
);

-- جدول الوكالات
CREATE TABLE IF NOT EXISTS agencies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending', -- We'll use CHECK constraint below
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cover_image VARCHAR(255) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    CONSTRAINT status_check CHECK (status IN ('active', 'inactive', 'pending'))
);

-- جدول العروض
CREATE TABLE IF NOT EXISTS offers (
    id SERIAL PRIMARY KEY,
    agency_id INT NOT NULL REFERENCES agencies(id) ON DELETE CASCADE,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    type VARCHAR(20) DEFAULT 'standard', -- We'll use CHECK constraint below
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT offer_type_check CHECK (type IN ('standard', 'golden'))
);

-- جدول المعتمرين
CREATE TABLE IF NOT EXISTS pilgrims (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    passport_number VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    birth_date DATE,
    nationality VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الطلبات
CREATE TABLE IF NOT EXISTS requests (
    id SERIAL PRIMARY KEY,
    pilgrim_id INT NOT NULL REFERENCES pilgrims(id) ON DELETE CASCADE,
    offer_id INT NOT NULL REFERENCES offers(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'pending', -- We'll use CHECK constraint below
    admin_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT request_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'sent_to_agency'))
);

-- إدخال مدير جديد واسترجاع الـ id تلقائيًا
WITH new_admin AS (
    INSERT INTO admins (username, password, full_name, permissions)
    VALUES ('admin2', 'hashed_password', 'Admin Two', 'both')
    RETURNING id
)
INSERT INTO admin_permissions (admin_id, permission_key, allow_view, allow_add, allow_edit, allow_delete)
SELECT 
    new_admin.id, 
    permission_key, 
    allow_view, 
    allow_add, 
    allow_edit, 
    allow_delete
FROM new_admin,
(
    VALUES 
        ('manage_agencies', TRUE, TRUE, TRUE, TRUE),
        ('manage_pilgrims', FALSE, FALSE, FALSE, FALSE),
        ('manage_admins', FALSE, FALSE, FALSE, FALSE),
        ('manage_offers', TRUE, TRUE, TRUE, TRUE)
) AS perms(permission_key, allow_view, allow_add, allow_edit, allow_delete);
 
 
 
 --password_hash = 'Dj123456789.'; -- استبدلها بالتجزئة الفعلية لكلمة المرور