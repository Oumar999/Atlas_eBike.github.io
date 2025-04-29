<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to settings page");
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch the customer's current information
try {
    $stmt = $pdo->prepare("SELECT name, email, phone, street, city, zip_code FROM customers WHERE id = :customer_id");
    $stmt->execute([':customer_id' => $customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        error_log("Customer not found for ID: $customer_id");
        header("Location: ../login/login_costumer.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching customer data: " . $e->getMessage());
    $customer = ['name' => '', 'email' => '', 'phone' => '', 'street' => '', 'city' => '', 'zip_code' => ''];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .settings-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .settings-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .settings-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .profile-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .profile-section h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .profile-form label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.3rem;
            display: block;
        }

        .profile-form input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .profile-form input:focus {
            outline: none;
            border-color: #ef3705;
            box-shadow: 0 0 5px rgba(239, 55, 5, 0.3);
        }

        .update-btn {
            background-color: #ef3705;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }

        .update-btn:hover {
            background-color: #d32f05;
        }

        .message {
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }

        .message.success {
            background-color: #e6f4ea;
            color: #28a745;
        }

        .message.error {
            background-color: #f8d7da;
            color: #dc3545;
        }

        @media (max-width: 480px) {
            .settings-container {
                padding: 1rem;
            }

            .settings-header h1 {
                font-size: 1.2rem;
            }

            .profile-section {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="settings-container">
        <div class="settings-header">
            <a href="customer_dashboard.php" class="back-btn">←</a>
            <h1>Settings</h1>
            <div style="width: 24px;"></div> <!-- Spacer to balance the header -->
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <?php echo $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="profile-section">
            <h2>Profile Information</h2>
            <form action="process_update_profile.php" method="POST" class="profile-form">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                </div>
                <div>
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" maxlength="10" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                </div>
                <div>
                    <label for="street">Street</label>
                    <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($customer['street'] ?? ''); ?>">
                </div>
                <div>
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>">
                </div>
                <div>
                    <label for="zip_code">Zip Code</label>
                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($customer['zip_code'] ?? ''); ?>">
                </div>
                <div>
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">
                </div>
                <div>
                    <label for="password_confirm">Confirm New Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm new password">
                </div>
                <button type="submit" class="update-btn">Update</button>
            </form>
        </div>
    </div>
</body>

</html>