USE prodrivers;

-- Add missing columns to bookings table
ALTER TABLE bookings 
ADD COLUMN amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER status,
ADD COLUMN reference VARCHAR(255) NOT NULL DEFAULT '' AFTER amount;

-- Update existing records with default values if needed
UPDATE bookings SET amount = 5000.00 WHERE amount = 0.00;
UPDATE bookings SET reference = CONCAT('PD_', id, '_', UNIX_TIMESTAMP(created_at)) WHERE reference = ''; 