<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../home_page.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Welkom, Admin <?php echo $_SESSION['user_name']; ?></h1>
    <!-- Add admin-specific controls -->
    <a href="../logout.php">Uitloggen</a>
</body>
</html>