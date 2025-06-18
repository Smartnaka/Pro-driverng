-- Create verification_checks table
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
    FOREIGN KEY (verified_by) REFERENCES admin_users(id) ON DELETE SET NULL,
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