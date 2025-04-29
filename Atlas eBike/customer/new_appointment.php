<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Default to the current month and year
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

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
    <title>New Appointment - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .new-appointment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .new-appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .new-appointment-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .new-appointment-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .type-section,
        .calendar-section,
        .time-slot-section {
            margin-bottom: 2rem;
        }

        .type-section h2,
        .calendar-section h2,
        .time-slot-section h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .type-options {
            display: flex;
            gap: 1rem;
        }

        .type-option {
            flex: 1;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .type-option.selected {
            color: #ef3705;
            border-color: #ef3705;
            background-color: #fff5f3;
        }

        .type-option:hover:not(.selected) {
            background-color: #f0f0f0;
        }

        .type-option svg {
            width: 24px;
            height: 24px;
            margin-bottom: 0.5rem;
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

        .book-btn {
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

        .book-btn:hover {
            background-color: #d32f05;
        }

        .book-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        @media (max-width: 480px) {
            .new-appointment-container {
                padding: 1rem;
            }

            .new-appointment-header h1 {
                font-size: 1.2rem;
            }

            .type-options {
                flex-direction: column;
            }

            .calendar-grid .day {
                padding: 0.3rem;
            }
        }
    </style>
</head>

<body>
    <div class="new-appointment-container">
        <div class="new-appointment-header">
            <a href="appointments.php" class="back-btn">←</a>
            <h1>New Appointment</h1>
            <div style="width: 24px;"></div> <!-- Spacer to balance the header -->
        </div>

        <form action="process_new_appointment.php" method="POST">
            <div class="type-section">
                <h2>Choose the type of visit</h2>
                <div class="type-options">
                    <div class="type-option <?php echo (isset($_GET['type']) && $_GET['type'] === 'pickup') ? 'selected' : ''; ?>" data-type="pickup" onclick="selectType('pickup')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #ff6f61;">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v4h4v2h-6z"></path>
                        </svg>
                        <div>Pickup</div>
                    </div>
                    <div class="type-option <?php echo (isset($_GET['type']) && $_GET['type'] === 'repair') ? 'selected' : ''; ?>" data-type="repair" onclick="selectType('repair')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #6c757d;">
                            <path d="m2.344 15.271 2 3.46a1 1 0 0 0 1.366.365l1.396-.806c.58.457 1.221.832 1.895 1.112V21a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-1.597a8.87 8.87 0 0 0 1.895-1.112l1.396.806c.477.275 1.091.11 1.366-.365l2-3.46a1 1 0 0 0-.365-1.366l-1.372-.793a9.153 9.153 0 0 0 0-2.226l1.372-.793a1 1 0 0 0 .365-1.366l-2-3.46a1 1 0 0 0-1.366-.365l-1.396.806A8.904 8.904 0 0 0 15 5.597V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v1.597a8.904 8.904 0 0 0-1.895 1.112l-1.396-.806a1 1 0 0 0-1.366.365l-2 3.46a1 1 0 0 0 .365 1.366l1.372.793a9.153 9.153 0 0 0 0 2.226l-1.372.793a1 1 0 0 0-.365 1.366zM13 9.5a3.5 3.5 0 1 1-3.5 3.5A3.5 3.5 0 0 1 13 9.5z"></path>
                        </svg>
                        <div>Repair</div>
                    </div>
                </div>
                <input type="hidden" name="type" id="selected-type" value="<?php echo isset($_GET['type']) ? htmlspecialchars($_GET['type']) : ''; ?>">
            </div>

            <div class="calendar-section">
                <h2>Choose the visit date</h2>
                <div class="calendar-header">
                    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>&type=<?php echo isset($_GET['type']) ? htmlspecialchars($_GET['type']) : ''; ?>" class="<?php echo $disable_prev ? 'disabled' : ''; ?>">←</a>
                    <span><?php echo $month_names[$current_month] . ' ' . $current_year; ?></span>
                    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>&type=<?php echo isset($_GET['type']) ? htmlspecialchars($_GET['type']) : ''; ?>">→</a>
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
                        $is_selected = isset($_GET['day']) && (int)$_GET['day'] === $day;
                    ?>
                        <div class="day <?php echo $is_past ? 'past' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>"
                            data-day="<?php echo $day; ?>"
                            onclick="<?php echo $is_past ? '' : "selectDay($day)"; ?>">
                            <?php echo $day; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="date" id="selected-date" value="<?php echo isset($_GET['day']) ? "$current_year-" . str_pad($current_month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET['day'], 2, '0', STR_PAD_LEFT) : ''; ?>">
            </div>

            <div class="time-slot-section">
                <h2>Choose the visit time slot</h2>
                <div class="time-slots">
                    <?php
                    $time_slots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];
                    $selected_date = isset($_GET['day']) ? new DateTime("$current_year-$current_month-" . (int)$_GET['day']) : null;
                    $is_today = $selected_date && $selected_date->format('Y-m-d') === $current_date->format('Y-m-d');

                    foreach ($time_slots as $slot):
                        $slot_time = new DateTime("$slot:00");
                        $is_slot_past = false;
                        if ($is_today) {
                            $slot_datetime = new DateTime($selected_date->format('Y-m-d') . " $slot:00");
                            $is_slot_past = $slot_datetime < $current_datetime;
                        }
                        $is_selected = isset($_GET['time']) && $_GET['time'] === $slot;
                    ?>
                        <div class="time-slot <?php echo $is_slot_past ? 'past' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>"
                            data-time="<?php echo $slot; ?>"
                            onclick="<?php echo $is_slot_past ? '' : "selectTime('$slot')"; ?>">
                            <?php echo $slot; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="time" id="selected-time" value="<?php echo isset($_GET['time']) ? htmlspecialchars($_GET['time']) : ''; ?>">
            </div>

            <button type="submit" class="book-btn" id="book-btn" disabled>Book Appointment</button>
        </form>
    </div>

    <script>
        let selectedType = null;
        let selectedDay = null;
        let selectedTime = null;

        function selectType(type) {
            // Remove selected class from previously selected type
            document.querySelectorAll('.type-option.selected').forEach(t => t.classList.remove('selected'));
            // Add selected class to the clicked type
            document.querySelector(`.type-option[data-type="${type}"]`).classList.add('selected');
            selectedType = type;
            document.getElementById('selected-type').value = type;
            updateBookButton();
        }

        function selectDay(day) {
            // Remove selected class from previously selected day
            document.querySelectorAll('.day.selected').forEach(d => d.classList.remove('selected'));
            // Add selected class to the clicked day
            document.querySelector(`.day[data-day="${day}"]`).classList.add('selected');
            selectedDay = day;
            document.getElementById('selected-date').value = `<?php echo $current_year; ?>-<?php echo str_pad($current_month, 2, '0', STR_PAD_LEFT); ?>-${day < 10 ? '0' + day : day}`;

            // Update time slots to reflect if they are past for the selected day
            updateTimeSlots(day);
            updateBookButton();
        }

        function selectTime(time) {
            // Remove selected class from previously selected time slot
            document.querySelectorAll('.time-slot.selected').forEach(t => t.classList.remove('selected'));
            // Add selected class to the clicked time slot
            document.querySelector(`.time-slot[data-time="${time}"]`).classList.add('selected');
            selectedTime = time;
            document.getElementById('selected-time').value = time;
            updateBookButton();
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

        function updateBookButton() {
            const bookBtn = document.getElementById('book-btn');
            if (selectedType && selectedDay && selectedTime) {
                bookBtn.disabled = false;
            } else {
                bookBtn.disabled = true;
            }
        }

        // Pre-select type if it's in the URL
        window.onload = function() {
            <?php if (isset($_GET['type'])): ?>
                selectType('<?php echo htmlspecialchars($_GET['type']); ?>');
            <?php endif; ?>
            <?php if (isset($_GET['day'])): ?>
                selectDay(<?php echo (int)$_GET['day']; ?>);
            <?php endif; ?>
            <?php if (isset($_GET['time'])): ?>
                selectTime('<?php echo htmlspecialchars($_GET['time']); ?>');
            <?php endif; ?>
        };
    </script>
</body>

</html>