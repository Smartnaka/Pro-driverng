<?php
require_once '../include/db.php';

function runMigration($conn, $sql) {
    try {
        // Split the SQL file into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $conn->query($statement);
                echo "Executed: " . substr($statement, 0, 50) . "...\n";
            }
        }
        return true;
    } catch (Exception $e) {
        echo "Error executing migration: " . $e->getMessage() . "\n";
        return false;
    }
}

// Read and execute the verification checks table creation
$verification_checks_sql = file_get_contents('../driver/migrations/create_verification_checks_table.sql');
if ($verification_checks_sql === false) {
    die("Could not read create_verification_checks_table.sql\n");
}

echo "Creating verification_checks table...\n";
if (runMigration($conn, $verification_checks_sql)) {
    echo "Successfully created verification_checks table and view\n";
} else {
    die("Failed to create verification_checks table\n");
}

// Read and execute the data migration
$migrate_data_sql = file_get_contents('../driver/migrations/migrate_verification_data.sql');
if ($migrate_data_sql === false) {
    die("Could not read migrate_verification_data.sql\n");
}

echo "\nMigrating verification data...\n";
if (runMigration($conn, $migrate_data_sql)) {
    echo "Successfully migrated verification data\n";
} else {
    die("Failed to migrate verification data\n");
}

echo "\nAll migrations completed successfully!\n";
?> 