<?php
session_start();
require_once '../includes/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: appointments.php");
    exit();
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$appointment_id = (int)$_POST['appointment_id'];
$type = $_POST['type'];
$date = $_POST['date'];
$time = $_POST['time'];

// Validate input
if (empty($type) || !in_array($type, ['pickup', 'repair'])) {
    $_SESSION['error'] = "Invalid appointment type.";
    header("Location: reschedule_appointment.php?id=$appointment_id");
    exit();
}

if (empty($date) || empty($time)) {
    $_SESSION['error'] = "Please select a date and time.";
    header("Location: reschedule_appointment.php?id=$appointment_id");
    exit();
}

// Validate time slot (must be between 8:00 and 15:00, on the hour)
$valid_time_slots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
if (!in_array($time, $valid_time_slots)) {
    $_SESSION['error'] = "Invalid time slot. Please select a time between 8:00 and 15:00.";
    header("Location: reschedule_appointment.php?id=$appointment_id");
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
    header("Location: reschedule_appointment.php?id=$appointment_id");
    exit();
}

// Verify the appointment exists and belongs to the user
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id AND customer_id = :customer_id");
    $stmt->execute([':id' => $appointment_id, ':customer_id' => $customer_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found or you do not have permission to reschedule it.";
        header("Location: appointments.php");
        exit();
    }

    // Check if the appointment is already cancelled
    if ($appointment['status'] === 'cancelled') {
        $_SESSION['error'] = "This appointment is already cancelled.";
        header("Location: appointments.php");
        exit();
    }

    // Check if the appointment is within 24 hours
    $appointment_datetime = new DateTime($appointment['appointment_datetime']);
    $is_past = $appointment_datetime < $current_datetime;
    $time_diff = $current_datetime->diff($appointment_datetime);
    $hours_until_appointment = ($time_diff->invert) ? -$time_diff->h - ($time_diff->d * 24) : $time_diff->h + ($time_diff->d * 24);

    if ($is_past || $hours_until_appointment <= 24) {
        $_SESSION['error'] = "You cannot reschedule this appointment. It is within 24 hours or has already passed.";
        header("Location: appointments.php");
        exit();
    }

    // Update the appointment with the new datetime
    $stmt = $pdo->prepare("UPDATE appointments SET appointment_datetime = :datetime WHERE id = :id");
    $stmt->execute([
        ':datetime' => $datetime,
        ':id' => $appointment_id
    ]);
    $_SESSION['success'] = "Appointment rescheduled successfully.";
    header("Location: appointments.php");
    exit();
} catch (PDOException $e) {
    error_log("Error rescheduling appointment: " . $e->getMessage());
    $_SESSION['error'] = "Error rescheduling appointment. Please try again.";
    header("Location: reschedule_appointment.php?id=$appointment_id");
    exit();
}
