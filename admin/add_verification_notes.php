<?php
require_once('../include/db.php');

try {
    // Add verification_notes column if it doesn't exist
    $sql = "ALTER TABLE drivers 
            ADD COLUMN IF NOT EXISTS verification_notes TEXT NULL DEFAULT NULL";
    
    if ($conn->query($sql)) {
        echo "Migration completed successfully!\n";
    } else {
        echo "Error running migration: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
}
?> 