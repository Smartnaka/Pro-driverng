<?php
require_once dirname(__FILE__) . '/../../include/db.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_settings_table.sql');
    
    if ($conn->multi_query($sql)) {
        do {
            // Consume results to allow next query execution
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        echo "Settings table created successfully!\n";
        
        // Insert default settings if they don't exist
        $default_settings = [
            'site_name' => 'ProDriver NG',
            'site_email' => 'support@prodriverng.com',
            'support_phone' => '',
            'commission_rate' => '10',
            'smtp_host' => '',
            'smtp_user' => '',
            'smtp_port' => '587',
            'smtp_password' => ''
        ];
        
        foreach ($default_settings as $key => $value) {
            $sql = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $key, $value);
            $stmt->execute();
        }
        
        echo "Default settings inserted successfully!\n";
        
    } else {
        throw new Exception("Error executing SQL: " . $conn->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close(); 