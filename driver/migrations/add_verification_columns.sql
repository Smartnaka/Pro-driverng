-- Add verification status columns to drivers table
ALTER TABLE drivers
ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
ADD COLUMN verified_at TIMESTAMP NULL; 