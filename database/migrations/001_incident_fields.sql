-- Adds the incident fields the application already collects but could not store.
-- Run once against an existing database:
--   mysql -u <user> -p community_disaster_platform < database/migrations/001_incident_fields.sql

ALTER TABLE disaster_incidents
    ADD COLUMN priority VARCHAR(20) NOT NULL DEFAULT 'medium' AFTER severity,
    ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER longitude;

ALTER TABLE disaster_incidents
    MODIFY latitude DECIMAL(10, 8) DEFAULT NULL,
    MODIFY longitude DECIMAL(11, 8) DEFAULT NULL;
