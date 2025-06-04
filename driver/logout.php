<?php
session_start();

// Ensure the user is a driver before logging out
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}

// Clear session data and destroy the session
session_unset();
session_destroy();

// Redirect to the driver login page
header("Location: login.php");
exit();
?>