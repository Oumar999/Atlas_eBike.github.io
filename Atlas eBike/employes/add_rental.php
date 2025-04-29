<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to add_rental.php");
    header("Location: ../login/login_employee.php");
    exit();
}

// Fetch all pending rental applications with customer and product details
try {
    $stmt = $pdo->query("
        SELECT ra.*, c.name AS customer_name, p.name AS product_name
        FROM rental_applications ra
        JOIN customers c ON ra.customer_id = c.id
        JOIN products p ON ra.product_id = p.id
        WHERE ra.status = 'pending'
        ORDER BY ra.created_at DESC
    ");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching rental applications: " . $e->getMessage());
    $applications = [];
    $_SESSION['error'] = "Error fetching rental applications. Please try again.";
}

// Handle Accept/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = (int)$_POST['application_id'];
    $action = $_POST['action'];

    // Fetch the application to get customer_id and product_id
    try {
        $stmt = $pdo->prepare("SELECT customer_id, product_id FROM rental_applications WHERE id = :id AND status = 'pending'");
        $stmt->execute([':id' => $application_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            $_SESSION['error'] = "Rental application not found or already processed.";
            header("Location: add_rental.php");
            exit();
        }

        $customer_id = $application['customer_id'];
        $product_id = $application['product_id'];

        // Start a transaction to ensure data consistency
        $pdo->beginTransaction();

        if ($action === 'accept') {
            // Get start_date and end_date from the form
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];

            // Validate dates
            if (empty($start_date) || empty($end_date)) {
                throw new Exception("Start date and end date are required.");
            }

            if (strtotime($end_date) <= strtotime($start_date)) {
                throw new Exception("End date must be after start date.");
            }

            // Insert into rentals table
            $stmt = $pdo->prepare("
                INSERT INTO rentals (customer_id, product_id, start_date, end_date, status)
                VALUES (:customer_id, :product_id, :start_date, :end_date, 'active')
            ");
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':product_id' => $product_id,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);

            // Get the newly created rental ID
            $rental_id = $pdo->lastInsertId();

            // Fetch the product price to determine the payment amount
            $stmt = $pdo->prepare("SELECT price FROM products WHERE id = :product_id");
            $stmt->execute([':product_id' => $product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $amount = $product['price'] ?? 0.00; // Default to 0 if price not found

            // Insert into payments table
            $stmt = $pdo->prepare("
                INSERT INTO payments (rental_id, customer_id, amount, status)
                VALUES (:rental_id, :customer_id, :amount, 'pending')
            ");
            $stmt->execute([
                ':rental_id' => $rental_id,
                ':customer_id' => $customer_id,
                ':amount' => $amount
            ]);

            // Update the rental application status to 'approved'
            $stmt = $pdo->prepare("UPDATE rental_applications SET status = 'approved', updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $application_id]);

            $_SESSION['success'] = "Rental application approved and rental created successfully.";
        } elseif ($action === 'reject') {
            // Update the rental application status to 'rejected'
            $stmt = $pdo->prepare("UPDATE rental_applications SET status = 'rejected', updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $application_id]);

            $_SESSION['success'] = "Rental application rejected successfully.";
        } else {
            throw new Exception("Invalid action specified.");
        }

        $pdo->commit();
        header("Location: add_rental.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error processing rental application: " . $e->getMessage());
        $_SESSION['error'] = "Error processing rental application: " . $e->getMessage();
        header("Location: add_rental.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Rental - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .add-rental-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .add-rental-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .add-rental-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .add-rental-header .back-btn {
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

        .application-list {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .application-list h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .application-item {
            border-bottom: 1px solid #ddd;
            padding: 1rem 0;
        }

        .application-item:last-child {
            border-bottom: none;
        }

        .application-details {
            margin-bottom: 1rem;
        }

        .application-details p {
            margin: 0.3rem 0;
            font-size: 0.9rem;
            color: #333;
        }

        .application-details strong {
            color: #000;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons label {
            color: #007bff;
        }

        .accept-btn,
        .reject-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .accept-btn {
            background-color: #28a745;
            color: #fff;
        }

        .accept-btn:hover {
            background-color: #218838;
        }

        .reject-btn {
            background-color: #dc3545;
            color: #fff;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        @media (max-width: 480px) {
            .add-rental-container {
                padding: 1rem;
            }

            .add-rental-header h1 {
                font-size: 1.2rem;
            }

            .application-list {
                padding: 1rem;
            }

            .application-details p {
                font-size: 0.8rem;
            }

            .accept-btn,
            .reject-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="add-rental-container">
        <div class="add-rental-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Add Rental</h1>
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

        <div class="application-list">
            <h2>Pending Rental Applications</h2>
            <?php if (empty($applications)): ?>
                <p>No pending rental applications found.</p>
            <?php else: ?>
                <?php foreach ($applications as $application): ?>
                    <div class="application-item">
                        <div class="application-details">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($application['customer_name']); ?></p>
                            <p><strong>Product:</strong> <?php echo htmlspecialchars($application['product_name']); ?></p>
                            <p><strong>First Name:</strong> <?php echo htmlspecialchars($application['first_name']); ?></p>
                            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($application['last_name']); ?></p>
                            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($application['phone_number']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($application['street'] . ' ' . $application['house_number'] . ', ' . $application['city'] . ', ' . $application['zip_code'] . ', ' . $application['country']); ?></p>
                            <p><strong>BSN:</strong> <?php echo htmlspecialchars($application['burgerservicenummer']); ?></p>
                            <p><strong>Applied On:</strong> <?php echo htmlspecialchars($application['created_at']); ?></p>
                        </div>
                        <div class="action-buttons">
                            <form action="add_rental.php" method="POST" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                <input type="hidden" name="action" value="accept">
                                <label>Start Date: <input type="date" name="start_date" required></label>
                                <label>End Date: <input type="date" name="end_date" required></label>
                                <button type="submit" class="accept-btn">Accept</button>
                            </form>
                            <form action="add_rental.php" method="POST" style="display: inline;">
                                <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="reject-btn">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>