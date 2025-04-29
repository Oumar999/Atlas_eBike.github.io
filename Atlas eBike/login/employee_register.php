<?php
session_start();
include_once '../includes/db_conn.php';
include_once 'login_process.php';
// bij het registreren van een nieuwe medewerker wordt hij naar de login pagina gestuurd
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    header("Location: ../login/employee_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atlas eBikes - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="login-page">
        <div class="form-container">
            <form class="form" action="login_process.php" method="POST">
                <p class="title">Register Employee</p>
                <p class="message">Create a new employee account</p>
                <label>
                    <span>Full Name</span>
                    <input name="name" required placeholder="Enter full name" type="text" class="input">
                </label>

                <label>
                    <span>Email</span>
                    <input name="email" required placeholder="Enter email" type="email" class="input">
                </label>

                <label>
                    <span>Password</span>
                    <input name="password" required placeholder="Create password" type="password" class="input">
                </label>
                <label>
                    <span>Confirm Password</span>
                    <input name="confirm_password" required placeholder="Confirm password" type="password" class="input">
                </label>
                <button type="submit" class="submit">Register</button>
                <a href="../home_page.php"> <button type="button" class="submit">Home Page </button></a>
                <p class="signin">Already have an account? <a href="../login/login_employee.php">Login</a></p>
            </form>
            <?php
            if (isset($_GET['error'])) {
                $errors = explode("|", urldecode($_GET['error']));
                foreach ($errors as $error) {
                    echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
                }
            }
            if (isset($_GET['register']) && $_GET['register'] == 'success') {
                echo "<div class='success'>Registration successful! Please log in.</div>";
            }
            ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>