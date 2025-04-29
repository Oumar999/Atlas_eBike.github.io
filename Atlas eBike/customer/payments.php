<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to payments.php");
    header("Location: ../login/login_customer.php");
    exit();
}

// Fetch the customer's payment history
try {
    $stmt = $pdo->prepare("
        SELECT p.*, r.start_date, r.end_date, pr.name AS product_name
        FROM payments p
        JOIN rentals r ON p.rental_id = r.id
        JOIN products pr ON r.product_id = pr.id
        WHERE p.customer_id = :customer_id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([':customer_id' => $_SESSION['customer_id']]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching payments: " . $e->getMessage());
    $payments = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .payments-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .payments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .payments-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .payments-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .message {
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
            background-color: #e6f4ea;
            color: #28a745;
        }

        .message.error {
            background-color: #f8d7da;
            color: #dc3545;
        }

        .payment-list {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

        .payment-item:last-child {
            border-bottom: none;
        }

        .payment-item .details {
            flex: 1;
        }

        .payment-item .details p {
            margin: 0.2rem 0;
            font-size: 0.9rem;
            color: #333;
        }

        .payment-item .status {
            font-weight: 600;
            text-transform: capitalize;
        }

        .payment-item .status.pending {
            color: #856404;
        }

        .payment-item .status.completed {
            color: #28a745;
        }

        .payment-item .status.failed {
            color: #dc3545;
        }

        .pay-btn {
            background-color: #ef3705;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .pay-btn:hover {
            background-color: #d32f05;
        }

        @media (max-width: 480px) {
            .payments-container {
                padding: 1rem;
            }

            .payments-header h1 {
                font-size: 1.2rem;
            }

            .payment-item .details p {
                font-size: 0.8rem;
            }

            .pay-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="payments-container">
        <div class="payments-header">
            <a href="../customer/customer_dashboard.php" class="back-btn">←</a>
            <h1>Your Payments</h1>
            <div style="width: 24px;"></div> <!-- Spacer -->
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message">
                <?php echo $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="payment-list">
            <h2>Payment History</h2>
            <?php if (empty($payments)): ?>
                <p>No payments found.</p>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                    <div class="payment-item">
                        <div class="details">
                            <p><strong>Bike:</strong> <?php echo htmlspecialchars($payment['product_name']); ?></p>
                            <p><strong>Rental Period:</strong> <?php echo date('d-m-Y', strtotime($payment['start_date'])); ?> to <?php echo date('d-m-Y', strtotime($payment['end_date'])); ?></p>
                            <p><strong>Amount:</strong> €<?php echo number_format($payment['amount'], 2); ?></p>
                            <p><strong>Status:</strong> <span class="status <?php echo $payment['status']; ?>"><?php echo htmlspecialchars($payment['status']); ?></span></p>
                            <?php if ($payment['status'] === 'completed' && $payment['payment_date']): ?>
                                <p><strong>Paid On:</strong> <?php echo date('d-m-Y H:i', strtotime($payment['payment_date'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($payment['status'] === 'pending'): ?>
                            <a href="process_payment.php?payment_id=<?php echo $payment['id']; ?>" class="pay-btn">Pay Now</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>