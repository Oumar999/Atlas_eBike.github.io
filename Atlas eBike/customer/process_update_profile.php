<?php
session_start();
require_once '../includes/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: settings.php");
    exit();
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../login/login_costumer.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$street = trim($_POST['street']);
$city = trim($_POST['city']);
$zip_code = trim($_POST['zip_code']);
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

// Validate input
if (empty($name)) {
    $_SESSION['error'] = "Name is required.";
    header("Location: settings.php");
    exit();
}

if (empty($email)) {
    $_SESSION['error'] = "Email is required.";
    header("Location: settings.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header("Location: settings.php");
    exit();
}

// Check if the new email is already in use by another customer
try {
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = :email AND id != :customer_id");
    $stmt->execute([':email' => $email, ':customer_id' => $customer_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "This email is already in use by another account.";
        header("Location: settings.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error checking email uniqueness: " . $e->getMessage());
    $_SESSION['error'] = "Error checking email. Please try again.";
    header("Location: settings.php");
    exit();
}

// Validate phone (if provided)
if (!empty($phone) && !preg_match('/^[0-9+\-\(\) ]+$/', $phone)) {
    $_SESSION['error'] = "Invalid phone number format.";
    header("Location: settings.php");
    exit();
}

// Validate zip code (if provided, assuming a Dutch format like "1234 AB")
if (!empty($zip_code) && !preg_match('/^[0-9]{4}\s?[A-Z]{2}$/', $zip_code)) {
    $_SESSION['error'] = "Invalid zip code format. Use format like '1234 AB'.";
    header("Location: settings.php");
    exit();
}

// Validate password (if provided)
if (!empty($password)) {
    if ($password !== $password_confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: settings.php");
        exit();
    }
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: settings.php");
        exit();
    }
    // Hash the new password
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
} else {
    // If password is not provided, keep the current password
    $password_hashed = null;
}

try {
    if ($password_hashed) {
        // Update with new password
        $stmt = $pdo->prepare("UPDATE customers SET name = :name, email = :email, password = :password, phone = :phone, street = :street, city = :city, zip_code = :zip_code WHERE id = :customer_id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password_hashed,
            ':phone' => $phone ?: null,
            ':street' => $street ?: null,
            ':city' => $city ?: null,
            ':zip_code' => $zip_code ?: null,
            ':customer_id' => $customer_id
        ]);
    } else {
        // Update without changing the password
        $stmt = $pdo->prepare("UPDATE customers SET name = :name, email = :email, phone = :phone, street = :street, city = :city, zip_code = :zip_code WHERE id = :customer_id");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone ?: null,
            ':street' => $street ?: null,
            ':city' => $city ?: null,
            ':zip_code' => $zip_code ?: null,
            ':customer_id' => $customer_id
        ]);
    }
    $_SESSION['success'] = "Profile updated successfully.";
    header("Location: settings.php");
    exit();
} catch (PDOException $e) {
    error_log("Error updating profile: " . $e->getMessage());
    $_SESSION['error'] = "Error updating profile. Please try again.";
    header("Location: settings.php");
    exit();
}
