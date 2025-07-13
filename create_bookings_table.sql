USE prodrivers;

DROP TABLE IF EXISTS bookings;

CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    driver_id INT NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    dropoff_location VARCHAR(255) NOT NULL,
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    duration_days INT NOT NULL DEFAULT 1,
    vehicle_type VARCHAR(50) NOT NULL,
    trip_purpose VARCHAR(50) NOT NULL,
    additional_notes TEXT,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 