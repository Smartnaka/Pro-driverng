<?php
require_once 'include/db.php';

$sql = file_get_contents('update_bookings_cancellation.sql');

try {
    // Split the SQL into separate statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if ($conn->query($statement) === FALSE) {
                throw new Exception("Error executing statement: " . $conn->error);
            }
        }
    }
    
    echo "Database update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
