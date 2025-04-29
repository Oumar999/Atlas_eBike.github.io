<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to update_category.php");
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

// Fetch selected category if ID is provided
$selected_category = null;
if (isset($_GET['id'])) {
    $category_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $category_id]);
        $selected_category = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching category: " . $e->getMessage());
        $_SESSION['error'] = "Error fetching category. Please try again.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);

    // Validate the category name
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
        header("Location: update_category.php?id=$category_id");
        exit();
    }

    // Check if the category name already exists (excluding the current category)
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND id != :id");
        $stmt->execute([':name' => $name, ':id' => $category_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "A category with this name already exists.";
            header("Location: update_category.php?id=$category_id");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error checking category name: " . $e->getMessage());
        $_SESSION['error'] = "Error checking category name. Please try again.";
        header("Location: update_category.php?id=$category_id");
        exit();
    }

    // Update the category in the database
    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
        $stmt->execute([':name' => $name, ':id' => $category_id]);
        $_SESSION['success'] = "Category updated successfully.";
        header("Location: ../employes/employee_dashboard.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error updating category: " . $e->getMessage());
        $_SESSION['error'] = "Error updating category. Please try again.";
        header("Location: update_category.php?id=$category_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Category - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .update-category-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .update-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .update-category-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .update-category-header .back-btn {
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
            margin-bottom: 2rem;
        }

        .category-list h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .category-list select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .update-category-form {
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
            .update-category-container {
                padding: 1rem;
            }

            .update-category-header h1 {
                font-size: 1.2rem;
            }

            .category-list,
            .update-category-form {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="update-category-container">
        <div class="update-category-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Update Category</h1>
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
            <h2>Select a Category to Update</h2>
            <form action="update_category.php" method="GET">
                <select name="id" onchange="this.form.submit()">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $selected_category && $selected_category['id'] == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selected_category): ?>
            <form action="update_category.php" method="POST" class="update-category-form">
                <input type="hidden" name="category_id" value="<?php echo $selected_category['id']; ?>">

                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($selected_category['name']); ?>" required>
                </div>

                <button type="submit" class="submit-btn">Update Category</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>