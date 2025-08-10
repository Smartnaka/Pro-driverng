<?php

require_once __DIR__ . '/../config.php';

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Use environment variables with fallback to hardcoded values
    $host = defined('DB_HOST') ? DB_HOST : "localhost";
    $user = defined('DB_USER') ? DB_USER : "root";
    $password = defined('DB_PASS') ? DB_PASS : ""; 
    $database = defined('DB_NAME') ? DB_NAME : "prodrivers";

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
    if ($conn instanceof mysqli && !$conn->connect_errno) {
        $conn->close();
    }
}

// Register shutdown function to ensure connection is closed
register_shutdown_function('closeDbConnection', $conn);
?>