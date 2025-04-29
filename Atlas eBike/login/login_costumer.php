<?php
session_start();

// Check for logout message
$logout_message = isset($_SESSION['logout_message']) ? $_SESSION['logout_message'] : '';
unset($_SESSION['logout_message']);

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
            // Prepare statement to check customer credentials
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && password_verify($password, $customer['password'])) {
                // Login successful
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                header("Location: ../customer/customer_dashboard.php");
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
    <title>Atlas eBikes - Customer Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <!-- <?php include './includes/navbar.php'; ?> -->

    <main class="login-page">
        <div class="form-container">
            <form class="form" action="" method="POST">
                <p class="title">Customer Login</p>
                <p class="message">Sign in to your customer account</p>

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
                <p class="signin">Don't have an account? <a href="costumer_register.php">Register</a></p>
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

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-column company-info">
                <a href="../home_page.php">
                    <img src="../assets/foto/Atlas_eBike_logo_1.png" alt="Atlas eBikes Logo" class="footer-logo">
                </a>
                <p>The power of Atlas, the freedom of e-bikes.</p>
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>


            <div class="footer-column contact-info">
                <h4>Contact Us</h4>
                <p><i class="fas fa-map-marker-alt"></i> Amsterdam, Netherlands</p>
                <p><i class="fas fa-phone"></i> +31 (0)20 123 4567</p>
                <p><i class="fas fa-envelope"></i> info@atlasebikes.com</p>
            </div>

            <div class="footer-column newsletter">
                <h4>Stay Updated</h4>
                <form class="newsletter-form" action="includes/subscribe.php" method="POST" id="newsletterForm">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
                <p id="newsletter-message" class="newsletter-message" style="display: none;"></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Atlas eBikes. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Font Awesome for social icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const messageElement = document.getElementById('newsletter-message');

            // Reset previous message styles
            messageElement.classList.remove('success', 'error');
            messageElement.style.display = 'none';

            fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    messageElement.innerHTML = data;

                    // Add appropriate class based on message content
                    if (data.includes('Succesvol')) {
                        messageElement.classList.add('success');
                    } else {
                        messageElement.classList.add('error');
                    }

                    messageElement.style.display = 'block';
                    form.reset();

                    // Automatically hide message after 5 seconds
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 5000);
                })
                .catch(error => {
                    messageElement.innerHTML = 'Er is een fout opgetreden. Probeer het opnieuw.';
                    messageElement.classList.add('error');
                    messageElement.style.display = 'block';
                    console.error('Error:', error);

                    // Automatically hide message after 5 seconds
                    setTimeout(() => {
                        messageElement.style.display = 'none';
                    }, 5000);
                });
        });
    </script>
</body>

</html>