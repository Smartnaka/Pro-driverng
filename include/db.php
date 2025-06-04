<?php

require_once 'config.php';

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $host = "localhost";
    $user = "root";
    $password = ""; 
    $database = "prodrivers";//db name

    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");

    //Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Function to safely close database connections
function closeDbConnection($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}

// Register shutdown function to ensure connection is closed
register_shutdown_function('closeDbConnection', $conn);
?>