<?php
session_start();
require_once '../includes/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: new_appointment.php");
    exit();
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$type = $_POST['type'];
$date = $_POST['date'];
$time = $_POST['time'];

// Validate input
if (empty($type) || !in_array($type, ['pickup', 'repair'])) {
    $_SESSION['error'] = "Invalid appointment type.";
    header("Location: new_appointment.php");
    exit();
}

if (empty($date) || empty($time)) {
    $_SESSION['error'] = "Please select a date and time.";
    header("Location: new_appointment.php");
    exit();
}

// Validate time slot (must be between 8:00 and 15:00, on the hour)
$valid_time_slots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
if (!in_array($time, $valid_time_slots)) {
    $_SESSION['error'] = "Invalid time slot. Please select a time between 8:00 and 15:00.";
    header("Location: new_appointment.php");
    exit();
}

// Combine date and time into a DATETIME format
$datetime = "$date $time:00";
$selected_datetime = new DateTime($datetime);
$current_datetime = new DateTime();

// Ensure the selected date is at least 24 hours in the future
$time_diff = $current_datetime->diff($selected_datetime);
$hours_until_appointment = ($time_diff->invert) ? -$time_diff->h - ($time_diff->d * 24) : $time_diff->h + ($time_diff->d * 24);
if ($selected_datetime <= $current_datetime || $hours_until_appointment < 24) {
    $_SESSION['error'] = "Please select a date and time at least 24 hours in the future.";
    header("Location: new_appointment.php");
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO appointments (customer_id, type, appointment_datetime, status) VALUES (:customer_id, :type, :datetime, 'active')");
    $stmt->execute([
        ':customer_id' => $customer_id,
        ':type' => $type,
        ':datetime' => $datetime
    ]);
    $_SESSION['success'] = "Appointment booked successfully.";
    header("Location: appointments.php");
    exit();
} catch (PDOException $e) {
    error_log("Error booking appointment: " . $e->getMessage());
    $_SESSION['error'] = "Error booking appointment. Please try again.";
    header("Location: new_appointment.php");
    exit();
}
