<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized access attempt to add_category.php");
    header("Location: ../login/login_employee.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    // Validate the category name
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
        header("Location: add_category.php");
        exit();
    }

    // Check if the category name already exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
        $stmt->execute([':name' => $name]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "A category with this name already exists.";
            header("Location: add_category.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error checking category name: " . $e->getMessage());
        $_SESSION['error'] = "Error checking category name. Please try again.";
        header("Location: add_category.php");
        exit();
    }

    // Insert the category into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        $_SESSION['success'] = "Category added successfully.";
        header("Location:../employes/employee_dashboard.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error adding category: " . $e->getMessage());
        $_SESSION['error'] = "Error adding category. Please try again.";
        header("Location: add_category.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .add-category-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .add-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .add-category-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .add-category-header .back-btn {
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

        .add-category-form {
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
            .add-category-container {
                padding: 1rem;
            }

            .add-category-header h1 {
                font-size: 1.2rem;
            }

            .add-category-form {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="add-category-container">
        <div class="add-category-header">
            <a href="../employes/employee_dashboard.php" class="back-btn">←</a>
            <h1>Add Category</h1>
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

        <form action="add_category.php" method="POST" class="add-category-form">
            <div class="form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" required>
            </div>

            <button type="submit" class="submit-btn">Add Category</button>
        </form>
    </div>
</body>
</html>