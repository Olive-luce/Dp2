CREATE TABLE IF NOT EXISTS incident_map_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    incident_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    reporter VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'reported',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_incident_map_reports_type (incident_type),
    INDEX idx_incident_map_reports_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
