-- Migrate existing verification data to verification_checks table
INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'license' as check_type,
    license_verified as status,
    license_notes as notes,
    license_verified_at as verification_date
FROM drivers
WHERE license_verified IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'nin' as check_type,
    nin_verified as status,
    nin_notes as notes,
    nin_verified_at as verification_date
FROM drivers
WHERE nin_verified IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'education' as check_type,
    education_verified as status,
    education_notes as notes,
    education_verified_at as verification_date
FROM drivers
WHERE education_verified IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'bank_details' as check_type,
    bank_verified as status,
    bank_notes as notes,
    bank_verified_at as verification_date
FROM drivers
WHERE bank_verified IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'address' as check_type,
    address_verified as status,
    address_notes as notes,
    address_verified_at as verification_date
FROM drivers
WHERE address_verified IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'documents' as check_type,
    documents_verified as status,
    documents_notes as notes,
    documents_verified_at as verification_date
FROM drivers
WHERE documents_verified IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'background_check' as check_type,
    CASE background_check_status
        WHEN 'completed' THEN 'approved'
        WHEN 'failed' THEN 'rejected'
        ELSE 'pending'
    END as status,
    background_check_notes as notes,
    background_check_date as verification_date
FROM drivers
WHERE background_check_status IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'interview' as check_type,
    CASE interview_status
        WHEN 'completed' THEN 'approved'
        WHEN 'failed' THEN 'rejected'
        ELSE 'pending'
    END as status,
    interview_notes as notes,
    interview_date as verification_date
FROM drivers
WHERE interview_status IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

INSERT INTO verification_checks (driver_id, check_type, status, notes, verification_date)
SELECT 
    id as driver_id,
    'training' as check_type,
    CASE training_status
        WHEN 'completed' THEN 'approved'
        WHEN 'failed' THEN 'rejected'
        ELSE 'pending'
    END as status,
    training_notes as notes,
    training_date as verification_date
FROM drivers
WHERE training_status IS NOT NULL
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    notes = VALUES(notes),
    verification_date = VALUES(verification_date);

-- After migrating data, we can drop the old columns
ALTER TABLE drivers
DROP COLUMN license_verified,
DROP COLUMN license_verified_at,
DROP COLUMN license_notes,
DROP COLUMN nin_verified,
DROP COLUMN nin_verified_at,
DROP COLUMN nin_notes,
DROP COLUMN education_verified,
DROP COLUMN education_verified_at,
DROP COLUMN education_notes,
DROP COLUMN bank_verified,
DROP COLUMN bank_verified_at,
DROP COLUMN bank_notes,
DROP COLUMN address_verified,
DROP COLUMN address_verified_at,
DROP COLUMN address_notes,
DROP COLUMN documents_verified,
DROP COLUMN documents_verified_at,
DROP COLUMN documents_notes,
DROP COLUMN background_check_status,
DROP COLUMN background_check_date,
DROP COLUMN background_check_notes,
DROP COLUMN interview_status,
DROP COLUMN interview_date,
DROP COLUMN interview_notes,
DROP COLUMN training_status,
DROP COLUMN training_date,
DROP COLUMN training_notes;

CREATE TABLE IF NOT EXISTS verification_checks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    driver_id INT NOT NULL,
    check_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    verified_by INT,
    verification_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    INDEX idx_driver_status (driver_id, status),
    INDEX idx_check_type (check_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial verification check types for each driver
INSERT INTO verification_checks (driver_id, check_type)
SELECT d.id, vt.check_type
FROM drivers d
CROSS JOIN (
    SELECT 'license' as check_type UNION
    SELECT 'nin' UNION
    SELECT 'education' UNION
    SELECT 'bank_details' UNION
    SELECT 'address' UNION
    SELECT 'documents' UNION
    SELECT 'background_check' UNION
    SELECT 'interview' UNION
    SELECT 'training'
) vt;

-- Create a view for easier verification status querying
CREATE OR REPLACE VIEW driver_verification_summary AS
SELECT 
    d.id as driver_id,
    d.first_name,
    d.last_name,
    d.email,
    d.phone,
    COUNT(CASE WHEN vc.status = 'approved' THEN 1 END) as approved_checks,
    COUNT(CASE WHEN vc.status = 'pending' THEN 1 END) as pending_checks,
    COUNT(CASE WHEN vc.status = 'rejected' THEN 1 END) as rejected_checks,
    GROUP_CONCAT(DISTINCT CASE WHEN vc.status = 'pending' THEN vc.check_type END) as pending_items,
    GROUP_CONCAT(DISTINCT CASE WHEN vc.status = 'rejected' THEN vc.check_type END) as rejected_items
FROM 
    drivers d
LEFT JOIN 
    verification_checks vc ON d.id = vc.driver_id
GROUP BY 
    d.id; 