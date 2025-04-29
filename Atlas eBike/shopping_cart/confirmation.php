<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Application Confirmation - Atlas eBike</title>
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 2rem;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .confirmation-container h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .confirmation-container p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .confirmation-container a {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
        }

        .confirmation-container a:hover {
            background-color: #0056b3;
        }

        .success {
            color: #28a745;
            font-weight: 600;
        }

        .error {
            color: #dc3545;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar (same as in product_details.php) -->
    <nav class="navbar">
        <div class="logo">
            <a href="../home_page.php">
                <img src="../assets/foto/Atlas_eBike_logo_1.png" alt="Logo">
            </a>
        </div>
        <ul>
            <div class="login-logo">
                <a href="./customer/customer_dashboard.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);">
                        <path d="M7.5 6.5C7.5 8.981 9.519 11 12 11s4.5-2.019 4.5-4.5S14.481 2 12 2 7.5 4.019 7.5 6.5zM20 21h1v-1c0-3.859-3.141-7-7-7h-4c-3.86 0-7 3.141-7 7v1h17z"></path>
                    </svg>
                </a>
            </div>
            <div class="shopping-cart-logo">
                <a href="../shopping_cart/shopping_cart.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);">
                        <path d="M21 4H2v2h2.3l3.521 9.683A2.004 2.004 0 0 0 9.7 17H18v-2H9.7l-.728-2H18c.4 0 .762-.238.919-.606l3-7A.998.998 0 0 0 21 4z"></path>
                        <circle cx="10.5" cy="19.5" r="1.5"></circle>
                        <circle cx="16.5" cy="19.5" r="1.5"></circle>
                    </svg>
                </a>
            </div>
        </ul>
    </nav>

    <div class="confirmation-container">
        <h2>Application Confirmation</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; ?></p>
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <p>Your application will be reviewed by our team. You will be notified of the status soon.</p>
        <a href="../home_page.php">Return to Home</a>
    </div>

    <!-- Footer (same as in product_details.php) -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-column company-info">
                <img src="../assets/foto/Atlas_eBike_logo_1.png" alt="Atlas eBikes Logo" class="footer-logo">
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
            <p>© <?php echo date('Y'); ?> Atlas eBikes. All Rights Reserved.</p>
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

            messageElement.classList.remove('success', 'error');
            messageElement.style.display = 'none';

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                messageElement.innerHTML = data;
                if (data.includes('Succesvol')) {
                    messageElement.classList.add('success');
                } else {
                    messageElement.classList.add('error');
                }
                messageElement.style.display = 'block';
                form.reset();
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                messageElement.innerHTML = 'Er is een fout opgetreden. Probeer het opnieuw.';
                messageElement.classList.add('error');
                messageElement.style.display = 'block';
                console.error('Error:', error);
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 5000);
            });
        });
    </script>
</body>
</html>