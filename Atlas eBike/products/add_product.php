<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to add_product.php");
    header("Location: ../login/login_employee.php");
    exit();
}

// Fetch categories for the dropdown
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $weight = floatval($_POST['weight']);
    $range_km = (int)$_POST['range_km'];
    $battery_power = (int)$_POST['battery_power'];
    $wheel_type = $_POST['wheel_type'];
    $is_new = isset($_POST['is_new']) ? 1 : 0;

    // Validate required fields
    if (empty($name) || $price <= 0 || $weight <= 0 || $range_km <= 0 || $battery_power <= 0 || !in_array($wheel_type, ['fat', 'normal'])) {
        $_SESSION['error'] = "Please fill in all required fields with valid values.";
        header("Location: add_product.php");
        exit();
    }

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        // Validate file type and size (e.g., allow only images, max 5MB)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types) || $file_size > $max_size) {
            $_SESSION['error'] = "Invalid image file. Please upload a JPEG, PNG, or GIF file under 5MB.";
            header("Location: add_product.php");
            exit();
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $_SESSION['error'] = "Failed to upload image. Please try again.";
            header("Location: add_product.php");
            exit();
        }
        $image_path = str_replace('../', '', $image_path); // Store relative path in DB
    }

    // Insert the product into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category_id, weight, range_km, battery_power, wheel_type, is_new) VALUES (:name, :description, :price, :image, :category_id, :weight, :range_km, :battery_power, :wheel_type, :is_new)");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':image' => $image_path,
            ':category_id' => $category_id,
            ':weight' => $weight,
            ':range_km' => $range_km,
            ':battery_power' => $battery_power,
            ':wheel_type' => $wheel_type,
            ':is_new' => $is_new
        ]);
        $_SESSION['success'] = "Product added successfully.";
        header("Location: ../employes/employee_dashboard.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        $_SESSION['error'] = "Error adding product. Please try again.";
        header("Location: add_product.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .add-product-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .add-product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .add-product-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .add-product-header .back-btn {
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

        .add-product-form {
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

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="checkbox"] {
            width: auto;
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
            .add-product-container {
                padding: 1rem;
            }

            .add-product-header h1 {
                font-size: 1.2rem;
            }

            .add-product-form {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="add-product-container">
        <div class="add-product-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Add Product</h1>
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

        <form action="add_product.php" method="POST" enctype="multipart/form-data" class="add-product-form">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (€) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg) *</label>
                <input type="number" id="weight" name="weight" step="0.1" min="0" required>
            </div>

            <div class="form-group">
                <label for="range_km">Range (km) *</label>
                <input type="number" id="range_km" name="range_km" min="0" required>
            </div>

            <div class="form-group">
                <label for="battery_power">Battery Power (Wh) *</label>
                <input type="number" id="battery_power" name="battery_power" min="0" required>
            </div>

            <div class="form-group">
                <label for="wheel_type">Wheel Type *</label>
                <select id="wheel_type" name="wheel_type" required>
                    <option value="fat">Fat</option>
                    <option value="normal">Normal</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_new"> Is New Product
                </label>
            </div>

            <button type="submit" class="submit-btn">Add Product</button>
        </form>
    </div>
</body>

</html>