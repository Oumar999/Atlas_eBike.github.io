<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to customer_dashboard.php");
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer details
try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->execute([':id' => $customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        error_log("Customer not found for ID: $customer_id");
        header("Location: ../login/login_customer.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching customer: " . $e->getMessage());
    header("Location: ../login/login_customer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            flex: 1;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #2a2a2a;
            margin: 0;
        }

        .dashboard-header .logout-btn {
            background-color: #dc3545;
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .dashboard-header .logout-btn:hover {
            background-color: #c82333;
        }

        .welcome-message {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 2rem;
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

        .dashboard-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* Forceren van 3 kaarten per rij */
            gap: 1.5rem;
        }

        .dashboard-card {
            background-color: #fff;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
            text-decoration: none;
            color: #2a2a2a;
        }

        .dashboard-card.disabled {
            background-color: #f0f0f0;
            pointer-events: none;
            cursor: not-allowed;
        }

        .dashboard-card:hover:not(.disabled) {
            transform: translateY(-5px);
        }

        .dashboard-card .icon {
            width: 24px;
            height: 24px;
            margin-bottom: 1rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .dashboard-card .icon.add {
            color: #dc3545;
        }

        .dashboard-card .icon.update {
            color: #007bff;
        }

        .dashboard-card .icon.delete {
            color: #dc3545;
        }

        .dashboard-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
        }

        .dashboard-card p {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
        }

        .dashboard-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding: 1rem 0;
            border-top: 1px solid #ddd;
        }

        .dashboard-footer .terms {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .dashboard-footer .terms:hover {
            text-decoration: underline;
        }

        .dashboard-footer .logout-btn {
            background-color: #dc3545;
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dashboard-footer .logout-btn:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            .dashboard-links {
                grid-template-columns: 1fr;
                /* Op kleine schermen 1 kaart per rij */
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .welcome-message {
                font-size: 1.2rem;
            }

            .dashboard-card {
                padding: 1rem;
            }

            .dashboard-card h3 {
                font-size: 1rem;
            }

            .dashboard-card p {
                font-size: 0.8rem;
            }

            .dashboard-footer {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Customer Dashboard</h1>
            <a href="../login/customer_logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="welcome-message">
            Welcome, <?php echo htmlspecialchars($customer['name']); ?>!
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

        <div class="dashboard-links">
            <!-- Rij 1: View Subscriptions, View Payments, Settings -->
            <a href="subscriptions_customer.php" class="dashboard-card">
                <svg class="icon add" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #dc3545;">
                    <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"></path>
                </svg>
                <h3>View Subscriptions</h3>
                <p>Check your active subscriptions.</p>
            </a>
            <a href="payments.php" class="dashboard-card">
                <svg class="icon update" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #007bff;">
                    <path d="M5 2c-1.103 0-2 .897-2 2v12c0 1.103.897 2 2 2h3v2c0 1.103.897 2 2 2h4c1.103 0 2-.897 2-2v-2h3c1.103 0 2-.897 2-2V4c0-1.103-.897-2-2-2H5zm5 16v2h4v-2h-4z"></path>
                </svg>
                <h3>View Payments</h3>
                <p>Manage your payment history.</p>
            </a>
            <a href="settings.php" class="dashboard-card">
                <svg class="icon add" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #dc3545;">
                    <path d="M12 16c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4zm0-6c1.084 0 2 .916 2 2s-.916 2-2 2-2-.916-2-2 .916-2 2-2z"></path>
                    <path d="m2.845 16.136 1 1.73c.531.917 1.809 1.261 2.73.73l.529-.306A8.1 8.1 0 0 0 9 19.402V20c0 1.103.897 2 2 2h2c1.103 0 2-.897 2-2v-.598a8.132 8.132 0 0 0 1.896-1.111l.529.306c.923.53 2.198.188 2.731-.731l.999-1.729a2.001 2.001 0 0 0-.731-2.732l-.505-.292a7.718 7.718 0 0 0 0-2.224l.505-.292a2.002 2.002 0 0 0 .731-2.732l-.999-1.729c-.531-.92-1.808-1.265-2.731-.732l-.529.306A8.1 8.1 0 0 0 15 4.598V4c0-1.103-.897-2-2-2h-2c-1.103 0-2 .897-2 2v.598a8.132 8.132 0 0 0-1.896 1.111l-.529-.306c-.924-.531-2.2-.187-2.731.732l-.999 1.729a2.001 2.001 0 0 0 .731 2.732l.505.292a7.683 7.683 0 0 0 0 2.223l-.505.292a2.003 2.003 0 0 0-.731 2.733zm3.326-2.758A5.703 5.703 0 0 1 6 12c0-.462.058-.926.17-1.378a.999.999 0 0 0-.47-1.108l-1.123-.65.998-1.729 1.145.662a.997.997 0 0 0 1.188-.142 6.071 6.071 0 0 1 2.384-1.399A1 1 0 0 0 11 5.3V4h2v1.3a1 1 0 0 0 .708.956 6.083 6.083 0 0 1 2.384 1.399.999.999 0 0 0 1.188.142l1.144-.661 1 1.729-1.124.649a1 1 0 0 0-.47 1.108c.112.452.17.916.17 1.378 0 .461-.058.925-.171 1.378a1 1 0 0 0 .471 1.108l1.123.649-.998 1.729-1.145-.661a.996.996 0 0 0-1.188.142 6.071 6.071 0 0 1-2.384 1.399A1 1 0 0 0 13 18.7l.002 1.3H11v-1.3a1 1 0 0 0-.708-.956 6.083 6.083 0 0 1-2.384-1.399.992.992 0 0 0-1.188-.141l-1.144.662-1-1.729 1.124-.651a1 1 0 0 0 .471-1.108z"></path>
                </svg>
                <h3>Settings</h3>
                <p>Manage your account settings.</p>
            </a>

            <!-- Rij 2: Support en Appointments -->
            <a href="support.php" class="dashboard-card">
                <svg class="icon update" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #007bff;">
                    <path d="M12 2C6.486 2 2 6.486 2 12v4.143C2 17.167 2.897 18 4 18h1a1 1 0 0 0 1-1v-5.143a1 1 0 0 0-1-1h-.908C4.648 6.987 7.978 4 12 4s7.352 2.987 7.908 6.857H19a1 1 0 0 0-1 1V18c0 1.103-.897 2-2 2h-2v-1h-4v3h6c2.206 0 4-1.794 4-4 1.103 0 2-.833 2-1.857V12c0-5.514-4.486-10-10-10z"></path>
                </svg>
                <h3>Support</h3>
                <p>Contact support for assistance.</p>
            </a>
            <a href="appointments.php" class="dashboard-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #6c757d;">
                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"></path>
                </svg>
                <h3>Appointments</h3>
                <p>View and manage appointments.</p>
            </a>
        </div>

        <div class="dashboard-footer">
            <a href="terms_conditions.php" class="terms">Terms & Conditions</a>
            <a href="../login/customer_logout.php"><button class="logout-btn">Log out</button></a>
            <a href="../home_page.php"><button class="logout-btn">Home Page</button></a>
        </div>
    </div>
</body>

</html>