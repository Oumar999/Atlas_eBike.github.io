<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to apply_rental.php");
    header("Location: ../login/login_costumer.php");
    exit();
}

// Check if the customer already has an active rental or a pending/approved application
try {
    // Check for active rentals
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM rentals
        WHERE customer_id = :customer_id AND status = 'active'
    ");
    $stmt->execute([':customer_id' => $_SESSION['customer_id']]);
    $active_rentals = $stmt->fetchColumn();

    // Check for pending or approved applications
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM rental_applications
        WHERE customer_id = :customer_id AND status IN ('pending', 'approved')
    ");
    $stmt->execute([':customer_id' => $_SESSION['customer_id']]);
    $existing_applications = $stmt->fetchColumn();

    if ($active_rentals > 0 || $existing_applications > 0) {
        $_SESSION['error'] = "You already have an active rental or a pending/approved application. You can only apply for one bike at a time.";
        header("Location: ../customer/customer_dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error checking existing rentals/applications: " . $e->getMessage());
    $_SESSION['error'] = "Error checking your rental status. Please try again.";
    header("Location: ../customer/customer_dashboard.php");
    exit();
}

// Fetch the product details (assuming product_id is passed via GET)
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($product_id <= 0) {
    $_SESSION['error'] = "Invalid product selected.";
    header("Location: ../home_page.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header("Location: ../home_page.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching product details. Please try again.";
    header("Location: ../home_page.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    $street = trim($_POST['street']);
    $house_number = trim($_POST['house_number']);
    $city = trim($_POST['city']);
    $zip_code = trim($_POST['zip_code']);
    $country = trim($_POST['country']);
    $burgerservicenummer = trim($_POST['burgerservicenummer']);

    // Validate form inputs
    if (
        empty($first_name) || empty($last_name) || empty($phone_number) || empty($street) ||
        empty($house_number) || empty($city) || empty($zip_code) || empty($country) || empty($burgerservicenummer)
    ) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ./customer/apply_rental.php?product_id=$product_id");
        exit();
    }

    // Insert the rental application into the database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO rental_applications (
                customer_id, product_id, first_name, last_name, phone_number, street,
                house_number, city, zip_code, country, burgerservicenummer, status
            ) VALUES (
                :customer_id, :product_id, :first_name, :last_name, :phone_number, :street,
                :house_number, :city, :zip_code, :country, :burgerservicenummer, 'pending'
            )
        ");
        $stmt->execute([
            ':customer_id' => $_SESSION['customer_id'],
            ':product_id' => $product_id,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':phone_number' => $phone_number,
            ':street' => $street,
            ':house_number' => $house_number,
            ':city' => $city,
            ':zip_code' => $zip_code,
            ':country' => $country,
            ':burgerservicenummer' => $burgerservicenummer
        ]);

        $_SESSION['success'] = "Rental application submitted successfully. Please wait for approval.";
        header("Location: ../shopping_cart/confirmation.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error submitting rental application: " . $e->getMessage());
        $_SESSION['error'] = "Error submitting your application. Please try again.";
        header("Location: ./customer/apply_rental.php?product_id=$product_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Rental - Atlas eBike</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .apply-rental-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .apply-rental-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .apply-rental-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .apply-rental-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .message {
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
            background-color: #e6f4ea;
            color: #28a745;
        }

        .message.error {
            background-color: #f8d7da;
            color: #dc3545;
        }

        .ConcurrentModificationException .apply-rental-form {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .submit-btn {
            background-color: #ef3705;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            max-width: 300px;
            margin: 1rem auto 0;
            display: block;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #d32f05;
        }

        @media (max-width: 480px) {
            .apply-rental-container {
                padding: 1rem;
            }

            .apply-rental-header h1 {
                font-size: 1.2rem;
            }

            .apply-rental-form {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="apply-rental-container">
        <div class="apply-rental-header">
            <a href="../home_page.php" class="back-btn">←</a>
            <h1>Apply for Rental - <?php echo htmlspecialchars($product['name']); ?></h1>
            <div style="width: 24px;"></div> <!-- Spacer -->
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message">
                <?php echo $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="apply_rental.php?product_id=<?php echo $product_id; ?>" method="POST" class="apply-rental-form">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number *</label>
                <input type="text" id="phone_number" name="phone_number" maxlength="10" required>
            </div>
            <div class="form-group">
                <label for="street">Street *</label>
                <input type="text" id="street" name="street" required>
            </div>
            <div class="form-group">
                <label for="house_number">House Number *</label>
                <input type="text" id="house_number" name="house_number" required>
            </div>
            <div class="form-group">
                <label for="city">City *</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="zip_code">Zip Code *</label>
                <input type="text" id="zip_code" name="zip_code" maxlength="6" required>
            </div>
            <div class="form-group">
                <label for="country">Country *</label>
                <input type="text" id="country" name="country" required>
            </div>
            <div class="form-group">
                <label for="burgerservicenummer">Burgerservicenummer (BSN) *</label>
                <input type="text" id="burgerservicenummer" name="burgerservicenummer" maxlength="9" required>
            </div>

            <button type="submit" class="submit-btn">Submit Application</button>
        </form>
    </div>
</body>

</html>