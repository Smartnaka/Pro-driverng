-- Add missing columns to drivers table
ALTER TABLE drivers ADD COLUMN address VARCHAR(255);
ALTER TABLE drivers ADD COLUMN experience INT;
ALTER TABLE drivers ADD COLUMN license_number VARCHAR(255);
ALTER TABLE drivers ADD COLUMN about_me TEXT;
ALTER TABLE drivers ADD COLUMN resident VARCHAR(255);
ALTER TABLE drivers ADD COLUMN family VARCHAR(255);
ALTER TABLE drivers ADD COLUMN education_level VARCHAR(255);
ALTER TABLE drivers ADD COLUMN drive VARCHAR(255);
ALTER TABLE drivers ADD COLUMN speak VARCHAR(255);
ALTER TABLE drivers ADD COLUMN nin VARCHAR(11);
ALTER TABLE drivers ADD COLUMN dob DATE;
ALTER TABLE drivers ADD COLUMN bank_name VARCHAR(255);
ALTER TABLE drivers ADD COLUMN acc_num VARCHAR(10);
ALTER TABLE drivers ADD COLUMN acc_name VARCHAR(255);
ALTER TABLE drivers ADD COLUMN skills VARCHAR(255);
ALTER TABLE drivers ADD COLUMN exp_date DATE;
ALTER TABLE drivers ADD COLUMN license_image VARCHAR(255);
ALTER TABLE drivers ADD COLUMN profile_picture VARCHAR(255);

-- Add notification preferences
ALTER TABLE drivers ADD COLUMN email_notifications TINYINT(1) DEFAULT 1;
ALTER TABLE drivers ADD COLUMN sms_notifications TINYINT(1) DEFAULT 1;

-- Add comments to columns
ALTER TABLE drivers MODIFY COLUMN address VARCHAR(255) COMMENT 'Driver''s current address/location';
ALTER TABLE drivers MODIFY COLUMN experience INT COMMENT 'Years of driving experience';
ALTER TABLE drivers MODIFY COLUMN license_number VARCHAR(255) COMMENT 'Driver''s license number';
ALTER TABLE drivers MODIFY COLUMN about_me TEXT COMMENT 'Driver''s description/bio';
ALTER TABLE drivers MODIFY COLUMN resident VARCHAR(255) COMMENT 'Residential address';
ALTER TABLE drivers MODIFY COLUMN family VARCHAR(255) COMMENT 'Family information';
ALTER TABLE drivers MODIFY COLUMN education_level VARCHAR(255) COMMENT 'Educational qualification';
ALTER TABLE drivers MODIFY COLUMN drive VARCHAR(255) COMMENT 'Types of vehicles can drive';
ALTER TABLE drivers MODIFY COLUMN speak VARCHAR(255) COMMENT 'Languages spoken';
ALTER TABLE drivers MODIFY COLUMN nin VARCHAR(11) COMMENT 'National Identification Number';
ALTER TABLE drivers MODIFY COLUMN dob DATE COMMENT 'Date of Birth';
ALTER TABLE drivers MODIFY COLUMN bank_name VARCHAR(255) COMMENT 'Bank name for payments';
ALTER TABLE drivers MODIFY COLUMN acc_num VARCHAR(10) COMMENT 'Bank account number';
ALTER TABLE drivers MODIFY COLUMN acc_name VARCHAR(255) COMMENT 'Bank account name';
ALTER TABLE drivers MODIFY COLUMN skills VARCHAR(255) COMMENT 'Additional skills';
ALTER TABLE drivers MODIFY COLUMN exp_date DATE COMMENT 'License expiry date';
ALTER TABLE drivers MODIFY COLUMN license_image VARCHAR(255) COMMENT 'Path to license image file';
ALTER TABLE drivers MODIFY COLUMN profile_picture VARCHAR(255) COMMENT 'Path to profile picture'; 