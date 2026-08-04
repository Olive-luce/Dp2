DROP DATABASE IF EXISTS community_disaster_platform;
CREATE DATABASE community_disaster_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE community_disaster_platform;

SET NAMES utf8mb4;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_roles_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_permissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_status (status),
    INDEX idx_users_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_role (user_id, role_id),
    INDEX idx_user_roles_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    INDEX idx_role_permissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE disaster_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    incident_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'medium',
    priority VARCHAR(20) NOT NULL DEFAULT 'medium',
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'reported',
    reported_by INT NOT NULL,
    assigned_to INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_incidents_status (status),
    INDEX idx_incidents_severity (severity),
    INDEX idx_incidents_type (incident_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incident_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_id INT NOT NULL,
    update_text TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES disaster_incidents(id) ON DELETE CASCADE,
    INDEX idx_incident_updates_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    availability VARCHAR(30) NOT NULL DEFAULT 'available',
    experience_level VARCHAR(30) DEFAULT 'beginner',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_volunteer_user (user_id),
    INDEX idx_volunteers_status (status),
    INDEX idx_volunteers_availability (availability)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE volunteer_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency VARCHAR(20) NOT NULL DEFAULT 'intermediate',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    INDEX idx_volunteer_skills_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE volunteer_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    incident_id INT NOT NULL,
    assignment_note TEXT DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'assigned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES volunteers(id) ON DELETE CASCADE,
    FOREIGN KEY (incident_id) REFERENCES disaster_incidents(id) ON DELETE CASCADE,
    UNIQUE KEY uq_assignment (volunteer_id, incident_id),
    INDEX idx_volunteer_assignments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(30) DEFAULT NULL,
    location VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_resources_status (status),
    INDEX idx_resources_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE resource_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    incident_id INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 0,
    allocated_to VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'allocated',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (incident_id) REFERENCES disaster_incidents(id) ON DELETE SET NULL,
    INDEX idx_resource_allocations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shelters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    address VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    current_occupancy INT NOT NULL DEFAULT 0,
    contact_person VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_shelters_status (status),
    INDEX idx_shelters_capacity (capacity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shelter_resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shelter_id INT NOT NULL,
    resource_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shelter_id) REFERENCES shelters(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    UNIQUE KEY uq_shelter_resource (shelter_id, resource_id),
    INDEX idx_shelter_resources_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE victims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    needs VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_victims_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE victim_assistance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    victim_id INT NOT NULL,
    incident_id INT DEFAULT NULL,
    assistance_type VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (victim_id) REFERENCES victims(id) ON DELETE CASCADE,
    FOREIGN KEY (incident_id) REFERENCES disaster_incidents(id) ON DELETE SET NULL,
    INDEX idx_victim_assistance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(50) NOT NULL DEFAULT 'info',
    status VARCHAR(20) NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_status (status),
    INDEX idx_notifications_type (notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    published_by INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_announcements_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(150) DEFAULT NULL,
    body TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_status (status),
    INDEX idx_messages_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE statistics_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cache_key VARCHAR(100) NOT NULL UNIQUE,
    cache_value TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_statistics_cache_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_logs_status (status),
    INDEX idx_activity_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_status (status),
    INDEX idx_password_resets_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (name, description, status) VALUES
('admin', 'System administrator', 'active'),
('responder', 'Emergency responder', 'active'),
('volunteer', 'Community volunteer', 'active'),
('citizen', 'Registered citizen', 'active');

INSERT INTO permissions (name, description, status) VALUES
('manage_users', 'Manage system users', 'active'),
('manage_incidents', 'Manage disaster incidents', 'active'),
('manage_volunteers', 'Manage volunteers', 'active'),
('manage_resources', 'Manage resources', 'active'),
('manage_shelters', 'Manage shelters', 'active'),
('manage_victims', 'Manage victim records', 'active'),
('send_notifications', 'Send notifications', 'active'),
('view_reports', 'View reports and dashboards', 'active');

INSERT INTO role_permissions (role_id, permission_id, status) VALUES
(1, 1, 'active'), (1, 2, 'active'), (1, 3, 'active'), (1, 4, 'active'), (1, 5, 'active'), (1, 6, 'active'), (1, 7, 'active'), (1, 8, 'active'),
(2, 2, 'active'), (2, 4, 'active'), (2, 5, 'active'), (2, 7, 'active'), (2, 8, 'active'),
(3, 3, 'active'), (3, 7, 'active'), (3, 8, 'active'),
(4, 8, 'active');

INSERT INTO users (username, full_name, email, password_hash, phone, city, status) VALUES
('admin', 'System Administrator', 'admin@disaster.org', '$2y$10$qZBfO/CqpevbhuHJ0.o5/eyF2aSJ3nEVtol2lTIEaNiKpS72uRmT2', '09170000001', 'Quezon City', 'active'),
('maria', 'Maria Cruz', 'maria@disaster.org', '$2y$10$qZBfO/CqpevbhuHJ0.o5/eyF2aSJ3nEVtol2lTIEaNiKpS72uRmT2', '09170000002', 'Manila', 'active'),
('jose', 'Jose Rivera', 'jose@disaster.org', '$2y$10$qZBfO/CqpevbhuHJ0.o5/eyF2aSJ3nEVtol2lTIEaNiKpS72uRmT2', '09170000003', 'Caloocan', 'active'),
('ana', 'Ana Santos', 'ana@disaster.org', '$2y$10$qZBfO/CqpevbhuHJ0.o5/eyF2aSJ3nEVtol2lTIEaNiKpS72uRmT2', '09170000004', 'Pasig', 'active');

INSERT INTO user_roles (user_id, role_id, status) VALUES
(1, 1, 'active'),
(2, 2, 'active'),
(3, 3, 'active'),
(4, 4, 'active');

INSERT INTO disaster_incidents (title, description, incident_type, severity, latitude, longitude, address, status, reported_by, assigned_to) VALUES
('Flooded Road Near Market', 'Heavy rain caused severe flooding and blocked access.', 'Flood', 'high', 23.8103, 90.4125, 'Dhaka', 'in_progress', 4, 2),
('Power Outage in District 3', 'A localized outage affected several households.', 'Power Outage', 'medium', 22.3569, 91.7832, 'Chattogram', 'acknowledged', 4, 2),
('Medical Supply Shortage', 'Urgent need for first aid supplies in the north zone.', 'Medical', 'high', 24.3745, 88.6042, 'Rajshahi', 'reported', 4, 2);

INSERT INTO incident_updates (incident_id, update_text, status) VALUES
(1, 'Response team dispatched to the market area.', 'active'),
(2, 'Utilities company notified and assessment underway.', 'active');

INSERT INTO volunteers (user_id, availability, experience_level, status) VALUES
(3, 'available', 'intermediate', 'active');

INSERT INTO volunteer_skills (volunteer_id, skill_name, proficiency, status) VALUES
(1, 'First Aid', 'advanced', 'active'),
(1, 'Evacuation Support', 'intermediate', 'active');

INSERT INTO volunteer_assignments (volunteer_id, incident_id, assignment_note, status) VALUES
(1, 1, 'Assist with evacuation routing and welfare checks.', 'assigned');

INSERT INTO resources (name, category, quantity, unit, location, status) VALUES
('Water Packs', 'Relief Supply', 120, 'boxes', 'North Depot', 'available'),
('Medical Kits', 'Medical', 30, 'kits', 'Central Hub', 'available'),
('Portable Generators', 'Power', 8, 'units', 'South Depot', 'available');

INSERT INTO resource_allocations (resource_id, incident_id, quantity, allocated_to, status) VALUES
(1, 1, 20, 'Market Response Team', 'allocated'),
(2, 2, 10, 'District 3 Support', 'allocated');

INSERT INTO shelters (name, address, capacity, current_occupancy, contact_person, status) VALUES
('Civic Center Shelter', '123 Rizal Avenue, Manila', 200, 86, 'Rosa Dela Cruz', 'open'),
('School Gym Shelter', '45 Aurora Blvd, Quezon City', 150, 52, 'Daniel Santos', 'open');

INSERT INTO shelter_resources (shelter_id, resource_id, quantity, status) VALUES
(1, 1, 60, 'available'),
(1, 2, 15, 'available'),
(2, 1, 40, 'available');

INSERT INTO victims (full_name, phone, address, needs, status) VALUES
('Carlos Mendoza', '09180000001', '12 Sample Street, Manila', 'Food and water', 'registered'),
('Elena Garcia', '09180000002', '88 River Road, Quezon City', 'Temporary shelter', 'registered');

INSERT INTO victim_assistance (victim_id, incident_id, assistance_type, details, status) VALUES
(1, 1, 'Relief Pack', 'Delivered water and blankets.', 'completed'),
(2, 1, 'Shelter Placement', 'Transferred to Civic Center Shelter.', 'pending');

INSERT INTO notifications (recipient_id, message, notification_type, status) VALUES
(1, 'A new incident has been reported near the market.', 'incident', 'unread'),
(3, 'You have been assigned to a new volunteer task.', 'assignment', 'unread');

INSERT INTO announcements (title, body, published_by, status) VALUES
('Community Safety Briefing', 'Please stay away from the flooded market area until authorities clear it.', 1, 'published'),
('Shelter Update', 'The Civic Center Shelter is accepting displaced residents tonight.', 1, 'published');

INSERT INTO messages (sender_id, receiver_id, subject, body, status) VALUES
(4, 2, 'Urgent request', 'Emergency assistance is needed near the market.', 'sent'),
(2, 3, 'Volunteer deployment', 'Please report to the market response checkpoint.', 'sent');

INSERT INTO statistics_cache (cache_key, cache_value, status) VALUES
('incident_count', '{"count":3}', 'active'),
('volunteer_count', '{"count":1}', 'active');

INSERT INTO activity_logs (user_id, action, details, status) VALUES
(1, 'login', 'Admin logged in successfully.', 'logged'),
(3, 'assignment_created', 'Volunteer assigned to flood response.', 'logged');

INSERT INTO password_resets (user_id, token, expires_at, status) VALUES
(1, 'reset-token-demo-001', '2026-08-05 00:00:00', 'pending');
