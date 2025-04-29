<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to delete_category.php");
    header("Location: ../login/login_employee.php");
    exit();
}

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Handle deletion
if (isset($_GET['id']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $category_id = (int)$_GET['id'];

    // Delete the category
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $category_id]);
        $_SESSION['success'] = "Category deleted successfully.";
        header("Location: delete_category.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error deleting category: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting category. Please try again.";
        header("Location: delete_category.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Category - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .delete-category-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .delete-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .delete-category-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .delete-category-header .back-btn {
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

        .category-list {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .category-list h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-item span {
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
            .delete-category-container {
                padding: 1rem;
            }

            .delete-category-header h1 {
                font-size: 1.2rem;
            }

            .category-list {
                padding: 1rem;
            }

            .category-item span {
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
    <div class="delete-category-container">
        <div class="delete-category-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Delete Category</h1>
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

        <div class="category-list">
            <h2>Select a Category to Delete</h2>
            <?php if (empty($categories)): ?>
                <p>No categories found.</p>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                        <a href="delete_category.php?id=<?php echo $category['id']; ?>" class="delete-btn">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add confirmation prompt for delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this category? This action cannot be undone, and related products will have their category set to NULL.')) {
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