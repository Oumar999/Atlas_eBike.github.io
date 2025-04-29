<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to cancel_subscription.php");
    header("Location: ../login/login_customer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$rental_id = isset($_GET['rental_id']) ? (int)$_GET['rental_id'] : 0;

error_log("Cancel subscription attempt: customer_id=$customer_id, rental_id=$rental_id");

if ($rental_id <= 0) {
    error_log("Invalid rental ID: $rental_id");
    $_SESSION['error'] = "Invalid rental ID.";
    header("Location: subscriptions_customer.php");
    exit();
}

// Verify the rental belongs to the customer and is active
try {
    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = :id AND customer_id = :customer_id AND status = 'active'");
    $stmt->execute([':id' => $rental_id, ':customer_id' => $customer_id]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        error_log("Rental not found or not eligible for cancellation: rental_id=$rental_id, customer_id=$customer_id");
        $_SESSION['error'] = "Rental not found or not eligible for cancellation.";
        header("Location: subscriptions_customer.php");
        exit();
    }

    error_log("Rental found: " . json_encode($rental));
} catch (PDOException $e) {
    error_log("Error fetching rental: " . $e->getMessage());
    $_SESSION['error'] = "Error processing cancellation. Please try again.";
    header("Location: subscriptions_customer.php");
    exit();
}

// Start a transaction to ensure data consistency
try {
    $pdo->beginTransaction();

    // Update the rental status to 'cancelled'
    // Verwijder de updated_at referentie
    $stmt = $pdo->prepare("UPDATE rentals SET status = 'cancelled' WHERE id = :id");
    $stmt->execute([':id' => $rental_id]);
    $rows_affected = $stmt->rowCount();
    error_log("Rental status updated to cancelled: rental_id=$rental_id, rows_affected=$rows_affected");

    if ($rows_affected === 0) {
        throw new PDOException("No rows were updated in rentals table.");
    }

    // Check for a pending payment and cancel it
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE rental_id = :rental_id AND status = 'pending'");
    $stmt->execute([':rental_id' => $rental_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        // Verwijder de updated_at referentie
        $stmt = $pdo->prepare("UPDATE payments SET status = 'cancelled' WHERE id = :payment_id");
        $stmt->execute([':payment_id' => $payment['id']]);
        $payment_rows_affected = $stmt->rowCount();
        error_log("Payment status updated to cancelled: payment_id={$payment['id']}, rows_affected=$payment_rows_affected");

        if ($payment_rows_affected === 0) {
            throw new PDOException("No rows were updated in payments table.");
        }
    } else {
        error_log("No pending payment found for rental_id=$rental_id");
    }

    $pdo->commit();
    error_log("Subscription cancelled successfully: rental_id=$rental_id");
    $_SESSION['success'] = "Subscription cancelled successfully.";
    header("Location: subscriptions_customer.php");
    exit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error cancelling subscription: " . $e->getMessage());
    $_SESSION['error'] = "Error cancelling subscription: " . $e->getMessage(); // Toon de specifieke fout aan de gebruiker voor debugging
    header("Location: subscriptions_customer.php");
    exit();
}
