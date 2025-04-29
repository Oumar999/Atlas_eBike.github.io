<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to process_payment.php");
    header("Location: ../login/login_customer.php");
    exit();
}

$payment_id = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;
if ($payment_id <= 0) {
    $_SESSION['error'] = "Invalid payment ID.";
    header("Location: payments.php");
    exit();
}

// Verify the payment belongs to the customer
try {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = :id AND customer_id = :customer_id AND status = 'pending'");
    $stmt->execute([':id' => $payment_id, ':customer_id' => $_SESSION['customer_id']]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error'] = "Payment not found or not eligible for processing.";
        header("Location: payments.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching payment: " . $e->getMessage());
    $_SESSION['error'] = "Error processing payment. Please try again.";
    header("Location: payments.php");
    exit();
}

// Simulate payment processing (in a real app, integrate with a payment gateway here)
try {
    $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW() WHERE id = :id");
    $stmt->execute([':id' => $payment_id]);

    $_SESSION['success'] = "Payment completed successfully.";
    header("Location: payments.php");
    exit();
} catch (PDOException $e) {
    error_log("Error processing payment: " . $e->getMessage());
    $_SESSION['error'] = "Error processing payment. Please try again.";
    header("Location: payments.php");
    exit();
}
?>