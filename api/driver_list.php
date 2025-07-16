<?php
session_start();
include '../include/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only allow AJAX requests from logged-in users
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Not authorized';
    exit();
}

// Pagination settings
$per_page = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Search filters
$where_conditions = [];
$params = [];
$types = "";

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_conditions[] = "address LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}
if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
    $where_conditions[] = "drive LIKE ?";
    $params[] = "%" . $_GET['vehicle_type'] . "%";
    $types .= "s";
}
if (isset($_GET['experience']) && !empty($_GET['experience'])) {
    $where_conditions[] = "experience >= ?";
    $params[] = $_GET['experience'];
    $types .= "i";
}

// Build the query
$drivers_sql = "SELECT id, first_name, last_name, drive, speak, skills, profile_picture, address, experience FROM drivers";
if (!empty($where_conditions)) {
    $drivers_sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$drivers_sql .= " ORDER BY experience DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $per_page;
$params[] = $offset;

$stmt = $conn->prepare($drivers_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$drivers_result = $stmt->get_result();

// Output only the grid HTML
include '../partials/driver-grid.php'; 