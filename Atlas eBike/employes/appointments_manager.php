<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to appointments.php");
    header("Location: ../login/login_employee.php");
    exit();
}

// Fetch all appointments with customer names
try {
    $stmt = $pdo->query("
        SELECT a.*, c.name AS customer_name
        FROM appointments a
        JOIN customers c ON a.customer_id = c.id
        ORDER BY a.appointment_datetime DESC
    ");
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
    $appointments = [];
    $_SESSION['error'] = "Error fetching appointments. Please try again.";
}

// Handle appointment cancellation
if (isset($_GET['cancel_id']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $appointment_id = (int)$_GET['cancel_id'];

    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $appointment_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Appointment cancelled successfully.";
        } else {
            $_SESSION['error'] = "Appointment not found or already cancelled.";
        }
        header("Location: appointments_manager.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error cancelling appointment: " . $e->getMessage());
        $_SESSION['error'] = "Error cancelling appointment. Please try again.";
        header("Location: appointments_manager  .php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .appointments-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .appointments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .appointments-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .appointments-header .back-btn {
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

        .appointments-table {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .appointments-table h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        td {
            color: #666;
        }

        .status-active {
            color: #28a745;
            font-weight: 600;
        }

        .status-cancelled {
            color: #dc3545;
            font-weight: 600;
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

        .cancel-btn.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        @media (max-width: 480px) {
            .appointments-container {
                padding: 1rem;
            }

            .appointments-header h1 {
                font-size: 1.2rem;
            }

            .appointments-table {
                padding: 1rem;
            }

            th,
            td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .cancel-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="appointments-container">
        <div class="appointments-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Appointments</h1>
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

        <div class="appointments-table">
            <h2>All Appointments</h2>
            <?php if (empty($appointments)): ?>
                <p>No appointments found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Type</th>
                            <th>Date/Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($appointment['type'])); ?></td>
                                <td><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($appointment['appointment_datetime']))); ?></td>
                                <td class="status-<?php echo strtolower($appointment['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($appointment['status'])); ?>
                                </td>
                                <td>
                                    <?php if ($appointment['status'] === 'active'): ?>
                                        <a href="appointments_manager.php?cancel_id=<?php echo $appointment['id']; ?>" class="cancel-btn">Cancel</a>
                                    <?php else: ?>
                                        <span class="cancel-btn disabled">Cancel</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add confirmation prompt for cancel buttons
        document.querySelectorAll('.cancel-btn:not(.disabled)').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to cancel this appointment? This action cannot be undone.')) {
                    e.preventDefault();
                } else {
                    // Append confirm=yes to the URL to proceed with cancellation
                    e.preventDefault();
                    window.location.href = button.href + '&confirm=yes';
                }
            });
        });
    </script>
</body>

</html>