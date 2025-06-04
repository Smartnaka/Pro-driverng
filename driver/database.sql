-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS prodrivers;
USE prodrivers;

-- Drop tables if they exist (in correct order due to foreign key constraints)
DROP TABLE IF EXISTS driver_notifications;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS activity_log;

-- Create drivers table
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    exp_years INT NOT NULL,
    education VARCHAR(255) NOT NULL,
    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    is_online TINYINT(1) DEFAULT 0,
    license_path VARCHAR(255) DEFAULT NULL,
    vehicle_papers_path VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
);

-- Create driver notifications table
CREATE TABLE driver_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') NOT NULL DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
);

-- Create activity log table
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES drivers(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_driver_email ON drivers(email);
CREATE INDEX idx_driver_status ON drivers(status);
CREATE INDEX idx_notification_driver ON driver_notifications(driver_id);
CREATE INDEX idx_notification_read ON driver_notifications(is_read);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_activity_action ON activity_log(action);

-- Insert some initial data (optional)
-- INSERT INTO drivers (first_name, last_name, email, phone, password, exp_years, education) 
-- VALUES ('John', 'Doe', 'john@example.com', '1234567890', '$2y$10$yourhashedpassword', 5, 'Bachelor''s Degree'); 