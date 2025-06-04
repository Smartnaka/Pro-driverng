<?php
include '../include/db.php';

// Create required directories
$directories = [
    'uploads/documents/passport/',
    'uploads/documents/licenses/',
    'uploads/documents/vehicle_papers/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Fetch all drivers
$sql = "SELECT * FROM drivers";
$result = $conn->query($sql);

if ($result) {
    while ($driver = $result->fetch_assoc()) {
        // Handle photo_path (passport)
        if (!empty($driver['photo_path']) && file_exists($driver['photo_path'])) {
            $ext = pathinfo($driver['photo_path'], PATHINFO_EXTENSION);
            $newFileName = str_replace(' ', '_', trim($driver['first_name'] . '_' . $driver['last_name'])) . '_passport_' . time() . '.' . $ext;
            $newPath = 'uploads/documents/passport/' . $newFileName;
            
            if (copy($driver['photo_path'], $newPath)) {
                // Update database with new path
                $stmt = $conn->prepare("UPDATE drivers SET photo_path = ? WHERE id = ?");
                $stmt->bind_param("si", $newPath, $driver['id']);
                $stmt->execute();
                echo "Moved passport photo for {$driver['first_name']} {$driver['last_name']}<br>";
            }
        }
    }
    echo "Migration completed successfully!";
} else {
    echo "Error fetching drivers: " . $conn->error;
} 