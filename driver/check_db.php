<?php
include '../include/db.php';

$sql = "SHOW COLUMNS FROM drivers";
$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?> 