<?php
require_once 'config.php';

// Check if the 'date' parameter is set and is a valid date
if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
    $date = $conn->real_escape_string($_GET['date']);

    // Get ALL appointments for the given date, excluding cancelled ones
    $sql = "SELECT a.appointment_time as time, a.user_id, a.id as appointment_id FROM appointments a WHERE a.appointment_date = '$date' AND a.status != 'cancelled'";
    $result = $conn->query($sql);

    if ($result) { // Check if the query was successful
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row; // Use [] for array push
        }

        if (count($appointments) > 0) {
            header('Content-Type: application/json');
            echo json_encode($appointments);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'No appointments found for this date.']); // Return a message
        }
    } else {
        // Handle database query error
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    }
} else {
    // Handle missing or invalid date parameter
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing date parameter.']);
}

?>
