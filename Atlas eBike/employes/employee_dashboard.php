<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    error_log("Unauthorized employee dashboard access attempt");
    header("Location: ../login/login_employee.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .dashboard-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .dashboard-header .logout-btn {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .dashboard-header .logout-btn:hover {
            background-color: #c82333;
        }

        .welcome-message {
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-message h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .option-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: #333;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .option-card svg {
            width: 24px;
            height: 24px;
            margin-bottom: 0.5rem;
        }

        .option-card h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .option-card p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 1rem;
            }

            .dashboard-header h1 {
                font-size: 1.2rem;
            }

            .dashboard-header .logout-btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .welcome-message h2 {
                font-size: 1rem;
            }

            .option-card {
                padding: 1rem;
            }

            .option-card h3 {
                font-size: 0.9rem;
            }

            .option-card p {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Employee Dashboard</h1>
            <a href="../login/employee_logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="welcome-message">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['employee_name']); ?>!</h2>
        </div>

        <div class="options-grid">
            <!-- Add Product -->
            <a href="../products/add_product.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #ef3705;">
                    <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path>
                </svg>
                <h3>Add Product</h3>
                <p>Add a new product to the inventory.</p>
            </a>

            <!-- Update Product -->
            <a href="../products/update_product.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #007bff;">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path>
                </svg>
                <h3>Update Product</h3>
                <p>Modify existing product details.</p>
            </a>

            <!-- Delete Product -->
            <a href="../products/delete_product.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #dc3545;">
                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path>
                </svg>
                <h3>Delete Product</h3>
                <p>Remove a product from the inventory.</p>
            </a>

            <!-- Add Category -->
            <a href="../category/add_category.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #28a745;">
                    <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path>
                </svg>
                <h3>Add Category</h3>
                <p>Create a new product category.</p>
            </a>

            <!-- Update Category -->
            <a href="../category/update_category.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #007bff;">
                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path>
                </svg>
                <h3>Update Category</h3>
                <p>Modify existing category details.</p>
            </a>

            <!-- Delete Category -->
            <a href="../category/delete_category.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #dc3545;">
                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path>
                </svg>
                <h3>Delete Category</h3>
                <p>Remove a category from the system.</p>
            </a>

            <!-- Add Rental -->
            <a href="../employes/add_rental.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #ff6f61;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v4h4v2h-6z"></path>
                </svg>
                <h3>Add Rental</h3>
                <p>Add a new rental to the system.</p>
            </a>

            <!-- Appointments -->
            <a href="../employes/appointments_manager.php" class="option-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #6c757d;">
                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"></path>
                </svg>
                <h3>Appointments</h3>
                <p>View and manage appointments.</p>
            </a>
        </div>
    </div>
</body>

</html>