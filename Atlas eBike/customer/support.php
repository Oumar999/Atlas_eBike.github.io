<?php
session_start();
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $_SESSION['success'] = "Message sent successfully.";
}
if (!isset($_SESSION['customer_id'])) {
    error_log("Unauthorized access attempt to support page");
    header("Location: ../login/login_costumer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Atlas eBike</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .support-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
            min-height: 100vh;
        }

        .support-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 0.5rem;
        }

        .support-header h1 {
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .support-header .back-btn {
            font-size: 1.5rem;
            text-decoration: none;
            color: #000;
        }

        .contact-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .contact-section h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-form label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.3rem;
            display: block;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .contact-form textarea {
            height: 150px;
            resize: vertical;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #ef3705;
            box-shadow: 0 0 5px rgba(239, 55, 5, 0.3);
        }

        .contact-form button {
            background-color: #ef3705;
            color: #fff;
            border: none;
            padding: 0.8rem;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }

        .contact-form button:hover {
            background-color: #d32f05;
        }

        .message {
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }

        .message.success {
            background-color: #e6f4ea;
            color: #28a745;
        }

        .message.error {
            background-color: #f8d7da;
            color: #dc3545;
        }

        @media (max-width: 480px) {
            .support-container {
                padding: 1rem;
            }

            .support-header h1 {
                font-size: 1.2rem;
            }

            .contact-section {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="support-container">
        <div class="support-header">
            <a href="customer_dashboard.php" class="back-btn">←</a>
            <h1>Support</h1>
            <div style="width: 24px;"></div> <!-- Spacer to balance the header -->
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success">
                <?php echo $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="contact-section">
            <h2>Contact Us</h2>
            <form action="https://api.web3forms.com/submit" method="POST" class="contact-form">
                <input type="hidden" name="access_key" value="701dc4e3-fca2-424d-9bae-7db6838f192e">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Your Name" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Your Email" required>
                </div>
                <div>
                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="How can I help you?" required></textarea>
                </div>
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>
</body>

</html>