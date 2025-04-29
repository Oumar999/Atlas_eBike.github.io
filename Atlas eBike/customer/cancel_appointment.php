<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login/login_costumer.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header("Location: appointments.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$appointment_id = (int)$_GET['id'];

// Fetch the appointment to verify ownership and check the datetime
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id AND customer_id = :customer_id");
    $stmt->execute([':id' => $appointment_id, ':customer_id' => $customer_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found or you do not have permission to cancel it.";
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
    $current_datetime = new DateTime();
    $appointment_datetime = new DateTime($appointment['appointment_datetime']);
    $is_past = $appointment_datetime < $current_datetime;
    $time_diff = $current_datetime->diff($appointment_datetime);
    $hours_until_appointment = ($time_diff->invert) ? -$time_diff->h - ($time_diff->d * 24) : $time_diff->h + ($time_diff->d * 24);

    if ($is_past || $hours_until_appointment <= 24) {
        $_SESSION['error'] = "You cannot cancel this appointment. It is within 24 hours or has already passed.";
        header("Location: appointments.php");
        exit();
    }

    // Update the appointment status to 'cancelled'
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id");
    $stmt->execute([':id' => $appointment_id]);

    $_SESSION['success'] = "Appointment cancelled successfully.";
    header("Location: appointments.php");
    exit();
} catch (PDOException $e) {
    error_log("Error cancelling appointment: " . $e->getMessage());
    $_SESSION['error'] = "Error cancelling appointment. Please try again.";
    header("Location: appointments.php");
    exit();
}
