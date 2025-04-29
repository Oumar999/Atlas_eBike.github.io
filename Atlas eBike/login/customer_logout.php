<?php
session_start();

// Unset all customer-specific session variables
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);

// Destroy the entire session
session_destroy();

// Optional: Set a logout message that can be displayed on the login page
$_SESSION['logout_message'] = "You have been successfully logged out.";

// Redirect to customer login page
header("Location: login_costumer.php");
exit();
?>