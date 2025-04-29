<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', '/xampp/htdocs/Atlas eBikes/error.log');

require_once '../includes/db_conn.php';

// Function to log custom messages
function custom_log($message) {
    error_log($message, 3, '/xampp/htdocs/Atlas eBikes/registration.log');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log all received POST data (be careful with sensitive info in production)
    custom_log("Received POST data: " . print_r($_POST, true));

    // Validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    try {
        // Verify PDO connection
        if (!isset($pdo) || !$pdo) {
            throw new Exception("Database connection failed");
        }

        // Log database connection details
        custom_log("Database connection status: Connected");

        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already exists";
        }

        // If no errors, proceed with registration
        if (empty($errors)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute insert statement
            $stmt = $pdo->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
            
            try {
                $result = $stmt->execute([$name, $email, $hashed_password]);

                if ($result) {
                    custom_log("Registration successful for email: $email");
                    header("Location: login.php?register=success");
                    exit();
                } else {
                    // Log detailed error information
                    $errorInfo = $stmt->errorInfo();
                    custom_log("Insert failed: " . print_r($errorInfo, true));
                    $errors[] = "Registration failed: " . $errorInfo[2];
                }
            } catch (PDOException $e) {
                custom_log("PDO Exception during insert: " . $e->getMessage());
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    } catch (Exception $e) {
        // Log detailed error information
        custom_log("Registration Error: " . $e->getMessage());
        $errors[] = "Registration failed: " . $e->getMessage();
    }

    // If there are errors, redirect back to registration page with error messages
    if (!empty($errors)) {
        $error_string = urlencode(implode("|", $errors));
        header("Location: login.php?error=" . $error_string);
        exit();
    }
}