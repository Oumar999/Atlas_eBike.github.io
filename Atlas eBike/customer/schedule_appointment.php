<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to schedule_appointment.php");
    header("Location: ../login/login_customer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$appointment_type = isset($_GET['type']) ? $_GET['type'] : '';
$rental_id = isset($_GET['rental_id']) ? (int)$_GET['rental_id'] : 0;

// Validate appointment type and rental ID
if (!in_array($appointment_type, ['pickup', 'repair']) || $rental_id <= 0) {
    $_SESSION['error'] = "Invalid appointment type or rental ID.";
    header("Location: customer_dashboard.php");
    exit();
}

// Verify the rental belongs to the customer and is active
try {
    $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = :id AND customer_id = :customer_id AND status = 'active'");
    $stmt->execute([':id' => $rental_id, ':customer_id' => $customer_id]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        $_SESSION['error'] = "Rental not found or not active.";
        header("Location: customer_dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching rental: " . $e->getMessage());
    $_SESSION['error'] = "Error processing appointment. Please try again.";
    header("Location: customer_dashboard.php");
    exit();
}

// Additional validation for repair appointment
if ($appointment_type === 'repair') {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM appointments
            WHERE customer_id = :customer_id
            AND rental_id = :rental_id
            AND appointment_type = 'pickup'
            AND status = 'scheduled'
        ");
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':rental_id' => $rental_id
        ]);
        $has_pickup_appointment = $stmt->fetchColumn() > 0;

        if (!$has_pickup_appointment) {
            $_SESSION['error'] = "You must schedule a Pickup appointment before scheduling a Repair appointment.";
            header("Location: customer_dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error checking pickup appointment: " . $e->getMessage());
        $_SESSION['error'] = "Error processing appointment. Please try again.";
        header("Location: customer_dashboard.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Validate date and time
    if (empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error'] = "Please select a date and time for your appointment.";
        header("Location: schedule_appointment.php?type=$appointment_type&rental_id=$rental_id");
        exit();
    }

    // Ensure the date is in the future
    $selected_date = strtotime($appointment_date);
    $today = strtotime(date('Y-m-d'));
    if ($selected_date < $today) {
        $_SESSION['error'] = "Please select a future date for your appointment.";
        header("Location: schedule_appointment.php?type=$appointment_type&rental_id=$rental_id");
        exit();
    }

    // Insert the appointment into the database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO appointments (customer_id, rental_id, appointment_type, appointment_date, appointment_time, status)
            VALUES (:customer_id, :rental_id, :appointment_type, :appointment_date, :appointment_time, 'scheduled')
        ");
        $stmt->execute([
            ':customer_id' => $customer_id,
            ':rental_id' => $rental_id,
            ':appointment_type' => $appointment_type,
            ':appointment_date' => $appointment_date,
            ':appointment_time' => $appointment_time
        ]);

        $_SESSION['success'] = ucfirst($appointment_type) . " appointment scheduled successfully.";
        header("Location: customer_dashboard.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error scheduling appointment: " . $e->getMessage());
        $_SESSION['error'] = "Error scheduling appointment. Please try again.";
        header("Location: schedule_appointment.php?type=$appointment_type&rental_id=$rental_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .appointment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .appointment-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .appointment-header .back-btn {
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

        .appointment-form {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .submit-btn {
            background-color: #ef3705;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            max-width: 300px;
            margin: 1rem auto 0;
            display: block;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #d32f05;
        }

        @media (max-width: 480px) {
            .appointment-container {
                padding: 1rem;
            }

            .appointment-header h1 {
                font-size: 1.2rem;
            }

            .appointment-form {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="appointment-container">
        <div class="appointment-header">
            <a href="customer_dashboard.php" class="back-btn">←</a>
            <h1>Schedule <?php echo ucfirst($appointment_type); ?> Appointment</h1>
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

        <form action="schedule_appointment.php?type=<?php echo $appointment_type; ?>&rental_id=<?php echo $rental_id; ?>" method="POST" class="appointment-form">
            <div class="form-group">
                <label for="appointment_date">Appointment Date *</label>
                <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="appointment_time">Appointment Time *</label>
                <input type="time" id="appointment_time" name="appointment_time" required>
            </div>
            <button type="submit" class="submit-btn">Schedule Appointment</button>
        </form>
    </div>
</body>
</html>