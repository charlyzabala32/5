<?php
require_once 'config.php';

// Initialize an array to hold our response data.
$response = [];

// Get date and time from the request, with input validation
$date = isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) ? $_GET['date'] : null;
$time = isset($_GET['time']) && preg_match('/^\d{2}:\d{2}$/', $_GET['time']) ? $_GET['time'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Get user ID

if (!$date || !$time) {
    $response['error'] = 'Invalid date or time parameter.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Sanitize the input *after* checking for null/invalid values
$date = $conn->real_escape_string($date);
$time = $conn->real_escape_string($time);

// Check if the database connection is valid
if ($conn->connect_error) {
    $response['error'] = 'Database connection failed: ' . $conn->connect_error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Check if an appointment exists for the given date and time (excluding cancelled appointments)
// Direct comparison since appointment_time is a TIME type.
$sql = "SELECT id, user_id FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time' AND status != 'cancelled'";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        // Slot is booked
        $row = $result->fetch_assoc();
        $isUser = ($user_id && $row['user_id'] == $user_id);
        $response['booked'] = true;
        $response['isUser'] = $isUser;
    } else {
        // Slot is available
        $response['booked'] = false;
    }
} else {
    // Database query error
    $response['error'] = 'Database query failed: ' . $conn->error;
}

// Always set the Content-Type and encode the response
header('Content-Type: application/json');
echo json_encode($response);

?>
