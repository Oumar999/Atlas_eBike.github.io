<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to delete_product.php");
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

// Handle deletion
if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $product_id = (int)$_GET['id'];

    // Fetch the product to get the image path
    try {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Delete the product
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $product_id]);

            // Delete the image file if it exists
            if ($product['image'] && file_exists('../' . $product['image'])) {
                unlink('../' . $product['image']);
            }

            $_SESSION['success'] = "Product deleted successfully.";
            header("Location: delete_product.php");
            exit();
        } else {
            $_SESSION['error'] = "Product not found.";
            header("Location: delete_product.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error deleting product: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting product. Please try again.";
        header("Location: delete_product.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .delete-product-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .delete-product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .delete-product-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .delete-product-header .back-btn {
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
        }

        .product-list h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-item span {
            font-size: 1rem;
            color: #333;
        }

        .delete-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        @media (max-width: 480px) {
            .delete-product-container {
                padding: 1rem;
            }

            .delete-product-header h1 {
                font-size: 1.2rem;
            }

            .product-list {
                padding: 1rem;
            }

            .product-item span {
                font-size: 0.9rem;
            }

            .delete-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="delete-product-container">
        <div class="delete-product-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Delete Product</h1>
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
            <h2>Select a Product to Delete</h2>
            <?php if (empty($products)): ?>
                <p>No products found.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <span><?php echo htmlspecialchars($product['name']) . ' (' . ($product['category_name'] ? htmlspecialchars($product['category_name']) : 'No Category') . ')'; ?></span>
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="delete-btn">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add confirmation prompt for delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                    e.preventDefault();
                } else {
                    // Append confirm=yes to the URL to proceed with deletion
                    e.preventDefault();
                    window.location.href = button.href + '&confirm=yes';
                }
            });
        });
    </script>
</body>

</html>