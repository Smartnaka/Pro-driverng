<?php
session_start();
include '../include/db.php';

header('Content-Type: application/json');

function send_response($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    send_response(false, 'You must be logged in to update your profile.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method.');
}

$user_id = $_SESSION['user_id'];

// --- Fetch current user data ---
$query = "SELECT profile_picture, upload_id FROM customers WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Sanitize and prepare data ---
$address = trim($_POST['address']);
$state = $_POST['state'];
$id_type = $_POST['id_type'];
$occupation = trim($_POST['occupation']);

$profile_picture_path = $user['profile_picture'];
$upload_id_path = $user['upload_id'];

// --- Handle Profile Picture Upload ---
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array(strtolower($ext), $allowed)) {
        send_response(false, 'Only JPG, PNG, JPEG files are allowed for profile picture.');
    }
    if ($file['size'] > 800000) { // 800KB
        send_response(false, 'Profile picture size must be under 800KB.');
    }

    $newname = "../uploads/profile_" . time() . "_" . $user_id . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $newname)) {
        $profile_picture_path = 'uploads/profile_' . time() . "_" . $user_id . "." . $ext;
    } else {
        send_response(false, 'Failed to upload profile picture.');
    }
}

// --- Handle ID Upload ---
if (isset($_FILES['upload_id']) && $_FILES['upload_id']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['upload_id'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array(strtolower($ext), $allowed)) {
        send_response(false, 'Only JPG, PNG, JPEG, or PDF files are allowed for ID upload.');
    }
    if ($file['size'] > 2000000) { // 2MB
        send_response(false, 'ID file must be under 2MB.');
    }
    
    $newname = "../uploads/id_" . time() . "_" . $user_id . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $newname)) {
        $upload_id_path = 'uploads/id_' . time() . "_" . $user_id . "." . $ext;
    } else {
        send_response(false, 'Failed to upload ID document.');
    }
}

// --- Update Database ---
$sql = "UPDATE customers SET address=?, state=?, id_type=?, occupation=?, profile_picture=?, upload_id=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $address, $state, $id_type, $occupation, $profile_picture_path, $upload_id_path, $user_id);

if ($stmt->execute()) {
    $data = [];
    if (isset($profile_picture_path) && $profile_picture_path !== $user['profile_picture']) {
        $data['new_profile_picture'] = $profile_picture_path;
    }
    send_response(true, 'Profile updated successfully!', $data);
} else {
    send_response(false, 'Failed to update profile.');
}

$stmt->close();
$conn->close();
?> 