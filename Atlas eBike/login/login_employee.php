<?php
session_start();
include_once '../includes/db_conn.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $errors = [];

    // Validate input
    if (empty($email)) {
        $errors[] = "Email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        try {
            // Prepare statement to check employee credentials
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
            $stmt->execute([$email]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee && password_verify($password, $employee['password'])) {
                // Login successful
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_name'] = $employee['name'];
                header("Location: ../employes/employee_dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Login failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atlas eBikes - Employee Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="login-page">
        <div class="form-container">
            <form class="form" action="" method="POST">
                <p class="title">Employee Login</p>
                <p class="message">Sign in to your employee account</p>

                <label>
                    <span>Email</span>
                    <input name="email" required placeholder="Enter email" type="email" class="input">
                </label>

                <label>
                    <span>Password</span>
                    <input name="password" required placeholder="Enter password" type="password" class="input">
                </label>

                <button type="submit" class="submit">Login</button>
                <a href="../home_page.php"> <button type="button" class="submit">Home Page </button></a>
                <p class="signin">Don't have an account? <a href="employee_register.php">Register</a></p>
            </form>

            <?php
            // Display errors
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
                }
            }
            ?>

            <?php if (!empty($logout_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($logout_message); ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>