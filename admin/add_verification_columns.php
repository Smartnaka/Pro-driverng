<?php
require_once('../include/db.php');

try {
    // Add is_verified column if it doesn't exist
    $sql1 = "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0";
    $conn->query($sql1);
    
    // Add verified_at column if it doesn't exist
    $sql2 = "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL DEFAULT NULL";
    $conn->query($sql2);
    
    // Update existing records
    $sql3 = "UPDATE drivers SET is_verified = 0 WHERE is_verified IS NULL";
    $conn->query($sql3);
    
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
}
?> 