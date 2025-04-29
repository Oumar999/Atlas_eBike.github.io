<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to update_product.php");
    header("Location: ../login/login_employee.php");
    exit();
}

// Fetch all products
try {
    $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}

// Fetch categories for the dropdown
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Fetch selected product if ID is provided
$selected_product = null;
if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        $_SESSION['error'] = "Error fetching product. Please try again.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
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
        header("Location: update_product.php?id=$product_id");
        exit();
    }

    // Handle image upload (if a new image is provided)
    $image_path = $selected_product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types) || $file_size > $max_size) {
            $_SESSION['error'] = "Invalid image file. Please upload a JPEG, PNG, or GIF file under 5MB.";
            header("Location: update_product.php?id=$product_id");
            exit();
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $_SESSION['error'] = "Failed to upload image. Please try again.";
            header("Location: update_product.php?id=$product_id");
            exit();
        }
        $image_path = str_replace('../', '', $image_path); // Store relative path in DB

        // Delete old image if it exists
        if ($selected_product['image'] && file_exists('../' . $selected_product['image'])) {
            unlink('../' . $selected_product['image']);
        }
    }

    // Update the product in the database
    try {
        $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, image = :image, category_id = :category_id, weight = :weight, range_km = :range_km, battery_power = :battery_power, wheel_type = :wheel_type, is_new = :is_new WHERE id = :id");
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
            ':is_new' => $is_new,
            ':id' => $product_id
        ]);
        $_SESSION['success'] = "Product updated successfully.";
        header("Location: ../employes/employee_dashboard.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        $_SESSION['error'] = "Error updating product. Please try again.";
        header("Location: update_product.php?id=$product_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .update-product-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .update-product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .update-product-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .update-product-header .back-btn {
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

        .product-list {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .product-list h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .product-list select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .update-product-form {
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

        .form-group img {
            max-width: 100px;
            margin-top: 0.5rem;
            border-radius: 5px;
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
            background-colorF: #d32f05;
        }

        @media (max-width: 480px) {
            .update-product-container {
                padding: 1rem;
            }

            .update-product-header h1 {
                font-size: 1.2rem;
            }

            .product-list,
            .update-product-form {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="update-product-container">
        <div class="update-product-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Update Product</h1>
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

        <div class="product-list">
            <h2>Select a Product to Update</h2>
            <form action="update_product.php" method="GET">
                <select name="id" onchange="this.form.submit()">
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>" <?php echo $selected_product && $selected_product['id'] == $product['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['name']) . ' (' . ($product['category_name'] ? htmlspecialchars($product['category_name']) : 'No Category') . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_product): ?>
            <form action="update_product.php" method="POST" enctype="multipart/form-data" class="update-product-form">
                <input type="hidden" name="product_id" value="<?php echo $selected_product['id']; ?>">

                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($selected_product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($selected_product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price (€) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $selected_product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <?php if ($selected_product['image']): ?>
                        <div>
                            <img src="../<?php echo htmlspecialchars($selected_product['image']); ?>" alt="Current Image">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $selected_product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="weight">Weight (kg) *</label>
                    <input type="number" id="weight" name="weight" step="0.1" min="0" value="<?php echo $selected_product['weight']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="range_km">Range (km) *</label>
                    <input type="number" id="range_km" name="range_km" min="0" value="<?php echo $selected_product['range_km']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="battery_power">Battery Power (Wh) *</label>
                    <input type="number" id="battery_power" name="battery_power" min="0" value="<?php echo $selected_product['battery_power']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="wheel_type">Wheel Type *</label>
                    <select id="wheel_type" name="wheel_type" required>
                        <option value="fat" <?php echo $selected_product['wheel_type'] == 'fat' ? 'selected' : ''; ?>>Fat</option>
                        <option value="normal" <?php echo $selected_product['wheel_type'] == 'normal' ? 'selected' : ''; ?>>Normal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_new" <?php echo $selected_product['is_new'] ? 'checked' : ''; ?>> Is New Product
                    </label>
                </div>

                <button type="submit" class="submit-btn">Update Product</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>