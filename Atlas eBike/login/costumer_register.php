<?php
session_start();
include_once '../includes/db_conn.php';
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atlas eBikes - Customer Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

    <main class="login-page">
        <div class="form-container">
            <form class="form" action="" method="POST">
                <p class="title">Register Customer</p>
                <p class="message">Create a new customer account</p>
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

                <p class="signin">Already have an account? <a href="login_costumer.php">Login</a></p>
                <p class="signin">Are you a employee? <a href="login_employee.php">Login</a></p>
            </form>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $name = $_POST["name"];
                $email = $_POST["email"];
                $password = $_POST["password"];
                $confirm_password = $_POST["confirm_password"];

                $errors = [];
                if ($password !== $confirm_password) {
                    $errors[] = "Passwords do not match";
                }

                if (empty($errors)) {
                    try {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        $stmt = $GLOBALS['pdo']->prepare("INSERT INTO customers (name, email, password) VALUES (:name, :email, :password)");
                        $stmt->bindParam(":name", $name);
                        $stmt->bindParam(":email", $email);
                        $stmt->bindParam(":password", $hashed_password);
                        $stmt->execute();

                        echo "<p class='success'>Customer registered successfully!</p>";
                    } catch (PDOException $e) {
                        $errors[] = "Registration failed: " . $e->getMessage();
                    }
                }

                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        echo "<p class='error'>" . htmlspecialchars($error) . "</p>";
                    }
                }
            }
            ?>
        </div>
    </main>
</body>

</html>