<?php
session_start();
include 'include/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Booking System Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Testing Database Connection</h3>";
if ($conn && !$conn->connect_error) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Test 2: Check if bookings table exists
echo "<h3>2. Testing Bookings Table</h3>";
$table_check = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($table_check->num_rows > 0) {
    echo "✅ Bookings table exists<br>";
} else {
    echo "❌ Bookings table not found<br>";
    exit;
}

// Test 3: Check if there are available drivers
echo "<h3>3. Testing Driver Availability</h3>";
$drivers_check = $conn->query("SELECT id, first_name, last_name FROM drivers LIMIT 1");
if ($drivers_check->num_rows > 0) {
    $driver = $drivers_check->fetch_assoc();
    echo "✅ Found available driver: " . htmlspecialchars($driver['first_name'] . " " . $driver['last_name']) . "<br>";
    $test_driver_id = $driver['id'];
} else {
    echo "❌ No drivers found in the database<br>";
    exit;
}

// Test 4: Check if there are registered customers
echo "<h3>4. Testing Customer Availability</h3>";
$customers_check = $conn->query("SELECT id, first_name, last_name FROM customers LIMIT 1");
if ($customers_check->num_rows > 0) {
    $customer = $customers_check->fetch_assoc();
    echo "✅ Found registered customer: " . htmlspecialchars($customer['first_name'] . " " . $customer['last_name']) . "<br>";
    $test_user_id = $customer['id'];
} else {
    echo "❌ No customers found in the database<br>";
    exit;
}

// Test 5: Try creating a test booking
echo "<h3>5. Testing Booking Creation</h3>";
$test_booking_sql = "INSERT INTO bookings (
    user_id, 
    driver_id, 
    pickup_location, 
    dropoff_location, 
    pickup_date, 
    pickup_time, 
    duration_days,
    vehicle_type, 
    trip_purpose, 
    additional_notes, 
    status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

try {
    $stmt = $conn->prepare($test_booking_sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $pickup_location = "Test Pickup Location";
    $dropoff_location = "Test Dropoff Location";
    $pickup_date = date('Y-m-d', strtotime('+1 day'));
    $pickup_time = "10:00:00";
    $duration_days = 2;
    $vehicle_type = "Automatic";
    $trip_purpose = "Test Booking";
    $additional_notes = "This is a test booking";

    $stmt->bind_param("iissssisss", 
        $test_user_id,
        $test_driver_id,
        $pickup_location,
        $dropoff_location,
        $pickup_date,
        $pickup_time,
        $duration_days,
        $vehicle_type,
        $trip_purpose,
        $additional_notes
    );

    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;
        echo "✅ Test booking created successfully (ID: $booking_id)<br>";
        
        // Verify the booking was created
        $verify_sql = "SELECT * FROM bookings WHERE id = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("i", $booking_id);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        
        if ($booking = $result->fetch_assoc()) {
            echo "<pre>";
            echo "Booking Details:\n";
            echo "---------------\n";
            echo "Booking ID: " . $booking['id'] . "\n";
            echo "User ID: " . $booking['user_id'] . "\n";
            echo "Driver ID: " . $booking['driver_id'] . "\n";
            echo "Pickup: " . $booking['pickup_location'] . "\n";
            echo "Dropoff: " . $booking['dropoff_location'] . "\n";
            echo "Date: " . $booking['pickup_date'] . "\n";
            echo "Time: " . $booking['pickup_time'] . "\n";
            echo "Duration: " . $booking['duration_days'] . " days\n";
            echo "Vehicle Type: " . $booking['vehicle_type'] . "\n";
            echo "Purpose: " . $booking['trip_purpose'] . "\n";
            echo "Status: " . $booking['status'] . "\n";
            echo "</pre>";
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
} catch (Exception $e) {
    echo "❌ Error creating test booking: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test 6: Clean up test booking
echo "<h3>6. Cleaning Up Test Data</h3>";
if (isset($booking_id)) {
    $delete_sql = "DELETE FROM bookings WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $booking_id);
    if ($delete_stmt->execute()) {
        echo "✅ Test booking cleaned up successfully<br>";
    } else {
        echo "❌ Error cleaning up test booking<br>";
    }
}

echo "<br><strong>Testing Complete!</strong>";
?> 