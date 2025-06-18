-- Add verification status columns
ALTER TABLE drivers
ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
ADD COLUMN verified_at TIMESTAMP NULL,
ADD COLUMN verification_notes TEXT;

-- Add individual verification fields
ALTER TABLE drivers
ADD COLUMN license_verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN license_verified_at TIMESTAMP NULL,
ADD COLUMN license_notes TEXT,

ADD COLUMN nin_verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN nin_verified_at TIMESTAMP NULL,
ADD COLUMN nin_notes TEXT,

ADD COLUMN education_verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN education_verified_at TIMESTAMP NULL,
ADD COLUMN education_notes TEXT,

ADD COLUMN bank_verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN bank_verified_at TIMESTAMP NULL,
ADD COLUMN bank_notes TEXT,

ADD COLUMN address_verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN address_verified_at TIMESTAMP NULL,
ADD COLUMN address_notes TEXT,

ADD COLUMN documents_verified ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
ADD COLUMN documents_verified_at TIMESTAMP NULL,
ADD COLUMN documents_notes TEXT;

-- Add verification requirements tracking
ALTER TABLE drivers
ADD COLUMN required_documents TEXT COMMENT 'JSON array of required document types',
ADD COLUMN submitted_documents TEXT COMMENT 'JSON array of submitted document information',
ADD COLUMN verification_status JSON COMMENT 'JSON object tracking verification progress';

-- Add verification process tracking
ALTER TABLE drivers
ADD COLUMN background_check_status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN background_check_date TIMESTAMP NULL,
ADD COLUMN background_check_notes TEXT,

ADD COLUMN interview_status ENUM('pending', 'scheduled', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN interview_date TIMESTAMP NULL,
ADD COLUMN interview_notes TEXT,

ADD COLUMN training_status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
ADD COLUMN training_date TIMESTAMP NULL,
ADD COLUMN training_notes TEXT; 