-- Add cancellation related columns to bookings table
ALTER TABLE bookings 
ADD COLUMN cancellation_time DATETIME NULL,
ADD COLUMN cancellation_fee DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN refund_amount DECIMAL(10,2) DEFAULT 0.00;

-- Create refunds table to track refund processing
CREATE TABLE IF NOT EXISTS refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);
