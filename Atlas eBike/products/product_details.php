<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_GET['id'])) {
    header("Location: ../home_page.php");
    exit();
}

$product_id = intval($_GET['id']);
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p
                           LEFT JOIN categories c ON p.category_id = c.id
                           WHERE p.id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: ../home_page.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/details.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title><?php echo htmlspecialchars($product['name']); ?> - Atlas eBike</title>
</head>

<body>
    <section class="product-details">
        <div class="product-details-content">
            <!-- Product Image (Smaller and Left-Aligned) -->
            <div class="product-image">
                <img src="<?php
                            echo !empty($product['image'])
                                ? '../' . htmlspecialchars($product['image']) // Gebruik het pad uit de database
                                : '../uploads/products/default-bike.jpg'; // Standaardafbeelding
                            ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <!-- Product Information -->
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="description">This will make your trips easier.</p>
                <ul class="specs">
                    <li>🔋 Battery Power: <?php echo $product['battery_power']; ?> Wh</li>
                    <li>🛞 Weels: <?php echo ucfirst(htmlspecialchars($product['wheel_type'])); ?></li>
                    <li>📏 weight: <?php echo $product['weight']; ?> kg</li>
                    <li>🏁 Range in KM: <?php echo $product['range_km']; ?> km</li>
                    <li>📦 Category: <?php echo htmlspecialchars($product['category_name'] ?? 'eBikes'); ?></li>
                </ul>
                <p class="price">€<?php echo number_format($product['price'], 2); ?> /month</p>
                <a href="../atlas_ebike-pdop/customer/apply_rental.php?product_id=<?php echo $product['id']; ?>" class="cta">Rent Now</a>
            </div>
        </div>
    </section>

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