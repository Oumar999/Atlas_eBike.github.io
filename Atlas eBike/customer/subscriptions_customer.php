<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to subscriptions.php");
    header("Location: ../login/login_customer.php");
    exit();
}

// Fetch the customer's active rentals
try {
    $stmt = $pdo->prepare("
        SELECT r.*, p.name AS product_name, p.image AS product_image
        FROM rentals r
        JOIN products p ON r.product_id = p.id
        WHERE r.customer_id = :customer_id AND r.status = 'active'
        ORDER BY r.start_date DESC
    ");
    $stmt->execute([':customer_id' => $_SESSION['customer_id']]);
    $rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching rentals: " . $e->getMessage());
    $rentals = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .subscriptions-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .subscriptions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .subscriptions-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .subscriptions-header .back-btn {
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

        .subscription-list {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .subscription-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #ddd;
        }

        .subscription-item:last-child {
            border-bottom: none;
        }

        .subscription-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-right: 1rem;
        }

        .subscription-details {
            flex: 1;
        }

        .subscription-details p {
            margin: 0.2rem 0;
            font-size: 0.9rem;
            color: #333;
        }

        .subscription-details .status {
            font-weight: 600;
            text-transform: capitalize;
            color: #28a745;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }

        @media (max-width: 480px) {
            .subscriptions-container {
                padding: 1rem;
            }

            .subscriptions-header h1 {
                font-size: 1.2rem;
            }

            .subscription-item img {
                width: 60px;
                height: 60px;
            }

            .subscription-details p {
                font-size: 0.8rem;
            }

            .cancel-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="subscriptions-container">
        <div class="subscriptions-header">
            <a href="../customer/customer_dashboard.php" class="back-btn">←</a>
            <h1>Your Subscriptions</h1>
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

        <div class="subscription-list">
            <h2>Active Subscriptions</h2>
            <?php if (empty($rentals)): ?>
                <p>No active subscriptions found.</p>
                <a href="../home_page.php" class="cancel-btn"> Apply for Rental</a>
            <?php else: ?>
                <?php foreach ($rentals as $rental): ?>
                    <div class="subscription-item">
                        <img src="<?php
                                    echo !empty($rental['product_image'])
                                        ? htmlspecialchars($rental['product_image'])
                                        : '../assets/images/default-bike.jpg';
                                    ?>" alt="<?php echo htmlspecialchars($rental['product_name']); ?>">
                        <div class="subscription-details">
                            <p><strong>Bike:</strong> <?php echo htmlspecialchars($rental['product_name']); ?></p>
                            <p><strong>Start Date:</strong> <?php echo date('d-m-Y', strtotime($rental['start_date'])); ?></p>
                            <p><strong>End Date:</strong> <?php echo date('d-m-Y', strtotime($rental['end_date'])); ?></p>
                            <p><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($rental['status']); ?></span></p>
                        </div>
                        <a href="cancel_subscription.php?rental_id=<?php echo $rental['id']; ?>" class="cancel-btn" onclick="return confirm('Are you sure you want to cancel this subscription?');">Cancel Subscription</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>