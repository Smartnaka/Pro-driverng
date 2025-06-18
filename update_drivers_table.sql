-- Add rating and total_trips columns to drivers table
ALTER TABLE drivers
ADD COLUMN rating DECIMAL(3,2) DEFAULT 0.00,
ADD COLUMN total_trips INT DEFAULT 0;

-- Update existing drivers with default values
UPDATE drivers SET rating = 0.00, total_trips = 0 WHERE rating IS NULL; 