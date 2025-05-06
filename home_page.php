<?php
session_start();
include_once 'includes/db_conn.php';

// Fetch products with their category
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         LIMIT 12");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    error_log("Error fetching products: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Atlas eBike</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Product Section Styles */
        .product-section {
            padding: 4rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ef3705;
        }

        .product-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #f7d8b7;
            font-weight: 600;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.6rem;
            color: #f7d8b7;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: #fff;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            position: relative;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card .badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 1rem;
        }

        .product-card h3 {
            color: #2a2a2a;
            font-size: 1.5rem;
            margin: 0.5rem 0;
            font-weight: 600;
        }

        .product-card .description {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .product-card .price {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 1.5rem;
        }

        .product-card .price span {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2a2a2a;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .info-btn {
            background: #fff;
            color: #2a2a2a;
            border: 1px solid #2a2a2a;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s, color 0.3s;
        }

        .info-btn:hover {
            background: #2a2a2a;
            color: #fff;
        }

        .cta {
            background: #2a2a2a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }

        .cta:hover {
            background: #555;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
                gap: 0.5rem;
            }

            .info-btn,
            .cta {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes\navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero" id="hero-section">
        <div class="hero-content">
            <h1>Experience the Future of Urban Mobility.</h1>
            <p>Discover our premium electric bikes, designed for modern city life. Durable, stylish and built to last.</p>
            <a href="#product-section" class="cta-button" onclick="scrollToProducts()">View Models →</a>
        </div>
    </section>

    <script>
        function scrollToHero() {
            const targetSection = document.querySelector('.hero');
            const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset;
            const startPosition = window.pageYOffset;
            const distance = targetPosition - startPosition;
            const duration = 1000;
            let start = null;

            function animation(currentTime) {
                if (start === null) start = currentTime;
                const timeElapsed = currentTime - start;
                const run = ease(timeElapsed, startPosition, distance, duration);
                window.scrollTo(0, run);
                if (timeElapsed < duration) requestAnimationFrame(animation);
            }

            function ease(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }

            requestAnimationFrame(animation);
        }

        function scrollToProducts() {
            const targetSection = document.querySelector('.product-section');
            const targetPosition = targetSection.getBoundingClientRect().top + window.pageYOffset;
            const startPosition = window.pageYOffset;
            const distance = targetPosition - startPosition;
            const duration = 1000;
            let start = null;

            function animation(currentTime) {
                if (start === null) start = currentTime;
                const timeElapsed = currentTime - start;
                const run = ease(timeElapsed, startPosition, distance, duration);
                window.scrollTo(0, run);
                if (timeElapsed < duration) requestAnimationFrame(animation);
            }

            function ease(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }

            requestAnimationFrame(animation);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const logoImage = document.querySelector('.logo img');
            if (logoImage) {
                logoImage.addEventListener('click', function(e) {
                    e.preventDefault();
                    scrollToHero();
                });
            }
        });
    </script>

    <!-- Features Section -->
    <br><br>
    <section class="features-section">
        <h1>Why choose Atlas eBike?</h1>
        <div class="features-container">
            <div class="feature-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" style="fill: #f7d8b7;margin-bottom: 15px;">
                    <path d="M4 18h14c1.103 0 2-.897 2-2v-2h2v-4h-2V8c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2zM4 8h14l.002 8H4V8z"></path>
                </svg>
                <h3>Large range</h3>
                <p>Up to 180km range on a single charge</p>
            </div>
            <div class="feature-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" style="fill: #f7d8b7;margin-bottom: 15px;">
                    <path d="M11 15.414V20h2v-4.586c0-.526-.214-1.042-.586-1.414l-2-2L13 9.414l2 2c.372.372.888.586 1.414.586H20v-2h-3.586l-3.707-3.707a.999.999 0 0 0-1.414 0L8 9.586c-.378.378-.586.88-.586 1.414s.208 1.036.586 1.414l3 3z"></path>
                    <circle cx="16" cy="5" r="2"></circle>
                    <path d="M18 14c-2.206 0-4 1.794-4 4s1.794 4 4 4 4-1.794 4-4-1.794-4-4-4zm0 6c-1.103 0-2-.897-2-2s.897-2 2-2 2 .897 2 2-.897 2-2 2zM6 22c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4zm0-6c1.103 0 2 .897 2 2s-.897 2-2 2-2-.897-2-2 .897-2 2-2z"></path>
                </svg>
                <h3>Premium Design</h3>
                <p>Sleek, modern design meets functionality</p>
            </div>
            <div class="feature-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" style="fill: #f7d8b7;margin-bottom: 15px;">
                    <path d="m20.496 6.106-7.973-4a.997.997 0 0 0-.895-.002l-8.027 4c-.297.15-.502.437-.544.767-.013.097-1.145 9.741 8.541 15.008a.995.995 0 0 0 .969-.009c9.307-5.259 8.514-14.573 8.476-14.967a1 1 0 0 0-.547-.797z"></path>
                </svg>
                <h3>Sustainable Built</h3>
                <p>2 year warranty on all our bicycles</p>
            </div>
        </div>
    </section>

    <!-- Product Section -->
    <br><br>
    <section class="product-section">
        <h2>Models</h2>
        <p class="section-subtitle">Discover our premium electric bicycles.</p>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Badge (optional, if the product is new) -->
                    <?php if (isset($product['is_new']) && $product['is_new']): ?>
                        <span class="badge">Nieuwe release</span>
                    <?php endif; ?>

                    <!-- Product Image -->
                    <img src="<?php
                                echo !empty($product['image'])
                                    ? htmlspecialchars($product['image'])
                                    : 'assets/images/default-bike.jpg';
                                ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">

                    <!-- Product Title -->
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>

                    <!-- Product Description -->
                    <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>

                    <!-- Pricing -->
                    <p class="price">vanaf <span>€<?php echo number_format($product['price'], 0); ?>/month</span></p>

                    <!-- Buttons -->
                    <div class="button-group">
                        <a href="./products/product_details.php?id=<?php echo $product['id']; ?>" class="info-btn">More Info</a>
                        <a href="../atlas_ebike-pdop/customer/apply_rental.php?product_id=<?php echo $product['id']; ?>" class="cta">Rent Now</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($products)): ?>
                <p>Momenteel geen producten beschikbaar.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include_once 'includes\footer.php'; ?>
</body>

</html>