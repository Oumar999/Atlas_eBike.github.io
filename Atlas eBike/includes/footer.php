<?php
include_once 'db_conn.php';
include_once 'subscribe.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Footer</title>
    <style>
        .newsletter-message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            transition: opacity 0.3s ease;
        }

        .newsletter-message.success {
            background-color: rgba(76, 175, 80, 0.3);
            color: #4CAF50;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .newsletter-message.error {
            background-color: rgba(1, 1, 1, 0.3);
            color: white;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }
    </style>
</head>

<body>
    <!-- Footer Section -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-column company-info">
                <!-- Bij het kliken van de logo gaat het pagina omhoog en naar boven. -->
                <a href="../atlas_ebike-pdop/home_page.php">
                    <img src="./assets/foto/Atlas_eBike_logo_1.png" alt="Atlas eBikes Logo" class="footer-logo">
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