<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login/login_costumer.php");
    exit();
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid appointment ID.";
    header("Location: appointments.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$appointment_id = (int)$_GET['id'];

// Fetch the appointment to verify ownership and get current details
try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = :id AND customer_id = :customer_id");
    $stmt->execute([':id' => $appointment_id, ':customer_id' => $customer_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $_SESSION['error'] = "Appointment not found or you do not have permission to reschedule it.";
        header("Location: appointments.php");
        exit();
    }

    // Check if the appointment is already cancelled
    if ($appointment['status'] === 'cancelled') {
        $_SESSION['error'] = "This appointment is already cancelled.";
        header("Location: appointments.php");
        exit();
    }

    // Check if the appointment is within 24 hours
    $current_datetime = new DateTime();
    $appointment_datetime = new DateTime($appointment['appointment_datetime']);
    $is_past = $appointment_datetime < $current_datetime;
    $time_diff = $current_datetime->diff($appointment_datetime);
    $hours_until_appointment = ($time_diff->invert) ? -$time_diff->h - ($time_diff->d * 24) : $time_diff->h + ($time_diff->d * 24);

    if ($is_past || $hours_until_appointment <= 24) {
        $_SESSION['error'] = "You cannot reschedule this appointment. It is within 24 hours or has already passed.";
        header("Location: appointments.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching appointment: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching appointment. Please try again.";
    header("Location: appointments.php");
    exit();
}

// Extract current appointment date and time for pre-filling
$appointment_date = date('Y-m-d', strtotime($appointment['appointment_datetime']));
$appointment_time = date('H:i', strtotime($appointment['appointment_datetime']));
$appointment_type = $appointment['type'];

// Default to the current month and year of the appointment
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n', strtotime($appointment_date));
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y', strtotime($appointment_date));

// Ensure month and year are valid and not in the past
$current_date = new DateTime();
$min_year = $current_date->format('Y');
$min_month = $current_date->format('n');

if ($current_year < $min_year || ($current_year == $min_year && $current_month < $min_month)) {
    $current_month = $min_month;
    $current_year = $min_year;
}

// Calculate previous and next month/year for navigation
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Disable previous month navigation if it would go before the current month
$disable_prev = ($prev_year < $min_year || ($prev_year == $min_year && $prev_month < $min_month));

// Get the number of days in the current month
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

// Get the day of the week for the first day of the month (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
$first_day_of_month = date('w', strtotime("$current_year-$current_month-01"));

// Adjust for Monday as the first day of the week (0 = Monday, 1 = Tuesday, ..., 6 = Sunday)
$first_day_of_month = ($first_day_of_month + 6) % 7;

// Month names in Dutch
$month_names = [
    1 => 'januari',
    2 => 'februari',
    3 => 'maart',
    4 => 'april',
    5 => 'mei',
    6 => 'juni',
    7 => 'juli',
    8 => 'augustus',
    9 => 'september',
    10 => 'oktober',
    11 => 'november',
    12 => 'december'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .reschedule-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .reschedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .reschedule-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .reschedule-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .calendar-section,
        .time-slot-section {
            margin-bottom: 2rem;
        }

        .calendar-section h2,
        .time-slot-section h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .calendar-header a {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .calendar-header a.disabled {
            color: #ccc;
            pointer-events: none;
        }

        .calendar-header span {
            font-size: 1.2rem;
            font-weight: 600;
            text-transform: lowercase;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            background-color: #fff;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .calendar-grid .day-header {
            font-weight: 600;
            text-align: center;
            color: #666;
        }

        .calendar-grid .day {
            padding: 0.5rem;
            text-align: center;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .calendar-grid .day.empty {
            background-color: transparent;
        }

        .calendar-grid .day.past {
            color: #ccc;
            cursor: not-allowed;
        }

        .calendar-grid .day:hover:not(.past):not(.selected) {
            background-color: #f0f0f0;
        }

        .calendar-grid .day.selected {
            background-color: #ef3705;
            color: #fff;
        }

        .time-slot-section .time-slots {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .time-slot-section .time-slot {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.8rem;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .time-slot-section .time-slot.past {
            color: #ccc;
            cursor: not-allowed;
        }

        .time-slot-section .time-slot:hover:not(.selected):not(.past) {
            background-color: #f0f0f0;
        }

        .time-slot-section .time-slot.selected {
            background-color: #ef3705;
            color: #fff;
            border-color: #ef3705;
        }

        .reschedule-btn {
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
            margin: 0 auto;
            display: block;
            transition: background-color 0.3s;
        }

        .reschedule-btn:hover {
            background-color: #d32f05;
        }

        .reschedule-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        @media (max-width: 480px) {
            .reschedule-container {
                padding: 1rem;
            }

            .reschedule-header h1 {
                font-size: 1.2rem;
            }

            .calendar-grid .day {
                padding: 0.3rem;
            }
        }
    </style>
</head>

<body>
    <div class="reschedule-container">
        <div class="reschedule-header">
            <a href="appointments.php" class="back-btn">←</a>
            <h1>Reschedule Appointment</h1>
            <div style="width: 24px;"></div> <!-- Spacer to balance the header -->
        </div>

        <form action="process_reschedule_appointment.php" method="POST">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($appointment_type); ?>">
            <div class="calendar-section">
                <h2>Choose the new visit date</h2>
                <div class="calendar-header">
                    <a href="?id=<?php echo $appointment_id; ?>&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="<?php echo $disable_prev ? 'disabled' : ''; ?>">←</a>
                    <span><?php echo $month_names[$current_month] . ' ' . $current_year; ?></span>
                    <a href="?id=<?php echo $appointment_id; ?>&month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>">→</a>
                </div>
                <div class="calendar-grid">
                    <!-- Day headers (M, D, W, etc.) -->
                    <div class="day-header">M</div>
                    <div class="day-header">D</div>
                    <div class="day-header">W</div>
                    <div class="day-header">D</div>
                    <div class="day-header">V</div>
                    <div class="day-header">Z</div>
                    <div class="day-header">Z</div>

                    <!-- Empty days before the first day of the month -->
                    <?php for ($i = 0; $i < $first_day_of_month; $i++): ?>
                        <div class="day empty"></div>
                    <?php endfor; ?>

                    <!-- Days of the month -->
                    <?php
                    $current_date = new DateTime();
                    $current_date->setTime(0, 0, 0); // Reset time to midnight for date comparison
                    for ($day = 1; $day <= $days_in_month; $day++):
                        $date = new DateTime("$current_year-$current_month-$day");
                        $is_past = $date < $current_date;
                        $is_selected = (isset($_GET['day']) && (int)$_GET['day'] === $day) || (!isset($_GET['day']) && $appointment_date === "$current_year-$current_month-" . sprintf("%02d", $day));
                    ?>
                        <div class="day <?php echo $is_past ? 'past' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>"
                            data-day="<?php echo $day; ?>"
                            onclick="<?php echo $is_past ? '' : "selectDay($day)"; ?>">
                            <?php echo $day; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="date" id="selected-date" value="<?php echo isset($_GET['day']) ? "$current_year-" . str_pad($current_month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET['day'], 2, '0', STR_PAD_LEFT) : $appointment_date; ?>">
            </div>

            <div class="time-slot-section">
                <h2>Choose the new visit time slot</h2>
                <div class="time-slots">
                    <?php
                    $time_slots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
                    $selected_date = isset($_GET['day']) ? new DateTime("$current_year-$current_month-" . (int)$_GET['day']) : new DateTime($appointment_date);
                    $is_today = $selected_date && $selected_date->format('Y-m-d') === $current_date->format('Y-m-d');

                    foreach ($time_slots as $slot):
                        $slot_time = new DateTime("$slot:00");
                        $is_slot_past = false;
                        if ($is_today) {
                            $slot_datetime = new DateTime($selected_date->format('Y-m-d') . " $slot:00");
                            $is_slot_past = $slot_datetime < $current_datetime;
                        }
                        $is_selected = (isset($_GET['time']) && $_GET['time'] === $slot) || (!isset($_GET['time']) && $appointment_time === $slot);
                    ?>
                        <div class="time-slot <?php echo $is_slot_past ? 'past' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>"
                            data-time="<?php echo $slot; ?>"
                            onclick="<?php echo $is_slot_past ? '' : "selectTime('$slot')"; ?>">
                            <?php echo $slot; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="time" id="selected-time" value="<?php echo isset($_GET['time']) ? htmlspecialchars($_GET['time']) : $appointment_time; ?>">
            </div>

            <button type="submit" class="reschedule-btn" id="reschedule-btn" disabled>Reschedule</button>
        </form>
    </div>

    <script>
        let selectedDay = null;
        let selectedTime = null;

        function selectDay(day) {
            // Remove selected class from previously selected day
            document.querySelectorAll('.day.selected').forEach(d => d.classList.remove('selected'));
            // Add selected class to the clicked day
            document.querySelector(`.day[data-day="${day}"]`).classList.add('selected');
            selectedDay = day;
            document.getElementById('selected-date').value = `<?php echo $current_year; ?>-<?php echo str_pad($current_month, 2, '0', STR_PAD_LEFT); ?>-${day < 10 ? '0' + day : day}`;

            // Update time slots to reflect if they are past for the selected day
            updateTimeSlots(day);
            updateRescheduleButton();
        }

        function selectTime(time) {
            // Remove selected class from previously selected time slot
            document.querySelectorAll('.time-slot.selected').forEach(t => t.classList.remove('selected'));
            // Add selected class to the clicked time slot
            document.querySelector(`.time-slot[data-time="${time}"]`).classList.add('selected');
            selectedTime = time;
            document.getElementById('selected-time').value = time;
            updateRescheduleButton();
        }

        function updateTimeSlots(day) {
            const currentDateTime = new Date();
            const selectedDate = new Date(<?php echo $current_year; ?>, <?php echo $current_month - 1; ?>, day);
            const isToday = selectedDate.toDateString() === currentDateTime.toDateString();

            document.querySelectorAll('.time-slot').forEach(slot => {
                const time = slot.getAttribute('data-time');
                const [hour, minute] = time.split(':').map(Number);
                const slotDateTime = new Date(selectedDate);
                slotDateTime.setHours(hour, minute, 0, 0);

                if (isToday && slotDateTime < currentDateTime) {
                    slot.classList.add('past');
                    slot.onclick = null;
                } else {
                    slot.classList.remove('past');
                    slot.onclick = () => selectTime(time);
                }

                // Clear selected time if it's now past
                if (slot.classList.contains('selected') && slot.classList.contains('past')) {
                    slot.classList.remove('selected');
                    selectedTime = null;
                    document.getElementById('selected-time').value = '';
                }
            });
        }

        function updateRescheduleButton() {
            const rescheduleBtn = document.getElementById('reschedule-btn');
            if (selectedDay && selectedTime) {
                rescheduleBtn.disabled = false;
            } else {
                rescheduleBtn.disabled = true;
            }
        }

        // Pre-select day and time if they are in the URL or from the appointment
        window.onload = function() {
            <?php if (isset($_GET['day'])): ?>
                selectDay(<?php echo (int)$_GET['day']; ?>);
            <?php else: ?>
                selectDay(<?php echo (int)date('d', strtotime($appointment_date)); ?>);
            <?php endif; ?>
            <?php if (isset($_GET['time'])): ?>
                selectTime('<?php echo htmlspecialchars($_GET['time']); ?>');
            <?php else: ?>
                selectTime('<?php echo $appointment_time; ?>');
            <?php endif; ?>
        };
    </script>
</body>

</html>