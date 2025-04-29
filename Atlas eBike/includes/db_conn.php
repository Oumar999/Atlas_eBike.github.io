<?php
// Database connection configuration
$host = 'localhost';
$dbname = 'Atlas_EBIKES';
$username = 'root';
$password = ''; // Update if you have a password

try {
    // Create PDO connection with error mode set to exception
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Database connection successful!";
} catch (PDOException $e) {
    // Display error message
    die("Database Connection Failed: " . $e->getMessage());
}
