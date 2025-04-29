<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to appointments page");
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch the customer's appointments
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE customer_id = :customer_id ORDER BY appointment_datetime DESC");
    $stmt->execute([':customer_id' => $customer_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
    $appointments = [];
}

// Get the current date and time for comparison
$current_datetime = new DateTime();
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
            max-width: 600px;
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

        .appointments-header .new-appointment-btn {
            background-color: #ef3705;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s;
        }

        .appointments-header .new-appointment-btn:hover {
            background-color: #d32f05;
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

        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .appointment-item {
            background-color: #fff;
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        .appointment-item.past {
            background-color: #f0f0f0;
            opacity: 0.6;
        }

        .appointment-item.past .type,
        .appointment-item.past .datetime,
        .appointment-item.past .appointment-status {
            color: #999;
        }

        .appointment-item.past svg {
            fill: #999;
        }

        .appointment-item svg {
            width: 24px;
            height: 24px;
        }

        .appointment-details {
            flex: 1;
        }

        .appointment-details .type {
            font-weight: 600;
            color: #333;
        }

        .appointment-details .datetime {
            font-size: 0.9rem;
            color: #666;
        }

        .appointment-status {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .appointment-status.active {
            background-color: #e6f4ea;
            color: #28a745;
        }

        .appointment-status.cancelled {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .appointment-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
        }

        .action-btn.cancel {
            background-color: #dc3545;
            color: #fff;
        }

        .action-btn.cancel:hover {
            background-color: #c82333;
        }

        .action-btn.reschedule {
            background-color: #007bff;
            color: #fff;
        }

        .action-btn.reschedule:hover {
            background-color: #0056b3;
        }

        .action-btn.disabled {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
            pointer-events: none;
        }

        @media (max-width: 480px) {
            .appointments-container {
                padding: 1rem;
            }

            .appointments-header {
                padding: 0 0.3rem;
            }

            .appointments-header h1 {
                font-size: 1.2rem;
            }

            .appointments-header .new-appointment-btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .appointment-item {
                flex-wrap: wrap;
            }

            .appointment-actions {
                margin-top: 0.5rem;
                width: 100%;
                justify-content: flex-end;
            }

            .action-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="appointments-container">
        <div class="appointments-header">
            <a href="customer_dashboard.php" class="back-btn">←</a>
            <h1>Appointments</h1>
            <a href="new_appointment.php" class="new-appointment-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" style="fill: #fff;">
                    <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path>
                </svg>
                New Appointment
            </a>
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

        <div class="appointments-list">
            <?php if (empty($appointments)): ?>
                <p>No appointments found.</p>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <?php
                    // Compare the appointment datetime with the current datetime
                    $appointment_datetime = new DateTime($appointment['appointment_datetime']);
                    $is_past = $appointment_datetime < $current_datetime;

                    // Check if the appointment is within 24 hours
                    $time_diff = $current_datetime->diff($appointment_datetime);
                    $hours_until_appointment = ($time_diff->invert) ? -$time_diff->h - ($time_diff->d * 24) : $time_diff->h + ($time_diff->d * 24);
                    $can_modify = !$is_past && $hours_until_appointment > 24 && $appointment['status'] === 'active';
                    ?>
                    <div class="appointment-item <?php echo $is_past ? 'past' : ''; ?>">
                        <?php if ($appointment['type'] === 'pickup'): ?>
                            <!-- Pickup Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #ff6f61;">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v4h4v2h-6z"></path>
                            </svg>
                        <?php else: ?>
                            <!-- Repair Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #6c757d;">
                                <path d="m2.344 15.271 2 3.46a1 1 0 0 0 1.366.365l1.396-.806c.58.457 1.221.832 1.895 1.112V21a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-1.597a8.87 8.87 0 0 0 1.895-1.112l1.396.806c.477.275 1.091.11 1.366-.365l2-3.46a1 1 0 0 0-.365-1.366l-1.372-.793a9.153 9.153 0 0 0 0-2.226l1.372-.793a1 1 0 0 0 .365-1.366l-2-3.46a1 1 0 0 0-1.366-.365l-1.396.806A8.904 8.904 0 0 0 15 5.597V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v1.597a8.904 8.904 0 0 0-1.895 1.112l-1.396-.806a1 1 0 0 0-1.366.365l-2 3.46a1 1 0 0 0 .365 1.366l1.372.793a9.153 9.153 0 0 0 0 2.226l-1.372.793a1 1 0 0 0-.365 1.366zM13 9.5a3.5 3.5 0 1 1-3.5 3.5A3.5 3.5 0 0 1 13 9.5z"></path>
                            </svg>
                        <?php endif; ?>
                        <div class="appointment-details">
                            <div class="type"><?php echo ucfirst($appointment['type']); ?></div>
                            <div class="datetime">
                                <?php echo date('d/m - H:i', strtotime($appointment['appointment_datetime'])); ?>
                            </div>
                        </div>
                        <div class="appointment-status <?php echo $appointment['status']; ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </div>
                        <div class="appointment-actions">
                            <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" class="action-btn cancel <?php echo !$can_modify ? 'disabled' : ''; ?>">Cancel</a>
                            <a href="reschedule_appointment.php?id=<?php echo $appointment['id']; ?>" class="action-btn reschedule <?php echo !$can_modify ? 'disabled' : ''; ?>">Reschedule</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add confirmation prompt for cancel buttons
        document.querySelectorAll('.action-btn.cancel').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!button.classList.contains('disabled')) {
                    if (!confirm('Are you sure you want to cancel your appointment?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>

</html>