<?php
// Include database connection
require_once 'db_conn.php';

$message = ""; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<span style='color: red;'>Ongeldig e-mailadres.</span>";
        exit;
    }

    try {
        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers WHERE email = :email");
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();
        
        if ($checkStmt->fetchColumn() > 0) {
            echo "<span style='color: red;'>Dit e-mailadres is al geregistreerd.</span>";
            exit;
        }

        // Insert email into database
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (:email)");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "<span style='color: green;'>Succesvol ingeschreven!</span>";
        } else {
            echo "<span style='color: white;'>Fout bij inschrijven. Probeer het opnieuw.</span>";
        }
    } catch(PDOException $e) {
        echo "<span style='color: red;'>Fout: " . htmlspecialchars($e->getMessage()) . "</span>";
    }
} else {
    echo "<span style='color: red;'>Ongeldige aanvraag.</span>";
}
?>
