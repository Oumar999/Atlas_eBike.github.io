<?php
session_start();

// Unset all employee-specific session variables
unset($_SESSION['employee_id']);
unset($_SESSION['employee_name']);

// Destroy the entire session
session_destroy();

// Optional: Set a logout message that can be displayed on the login page
$_SESSION['logout_message'] = "You have been successfully logged out.";

// Redirect to employee login page
header("Location: login_employee.php");
exit();
?>