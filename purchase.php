<?php
session_start();
require 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'D:/Xampp/htdocs/php_CirnosFumos/src/Exception.php';
require 'D:/Xampp/htdocs/php_CirnosFumos/src/PHPMailer.php';
require 'D:/Xampp/htdocs/php_CirnosFumos/src/SMTP.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to complete the purchase.");
    exit();
}

$user_id = $_SESSION['user_id'];

// Start a transaction
try {
    $pdo->beginTransaction();

    // Fetch items from the cart
    $sql = "SELECT c.cart_id, c.product_id, c.quantity, c.price, p.name 
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if the cart is empty
    if (empty($cart_items)) {
        header("Location: cart.php?message=Your cart is empty.");
        exit();
    }

    // Prepare for inserting data into the 'purchased' table
    $insert_sql = "INSERT INTO purchased (user_id, product_id, quantity, price, total_price, purchase_date)
                   VALUES (:user_id, :product_id, :quantity, :price, :total_price, NOW())";
    $insert_stmt = $pdo->prepare($insert_sql);

    $total_cart_value = 0;
    $order_details = '';  // To store product details for the email

    // Loop through cart items and insert into 'purchased' table
    foreach ($cart_items as $item) {
        $total_price = $item['quantity'] * $item['price'];
        $total_cart_value += $total_price;

        // Append product details for the email body
        $order_details .= "{$item['name']} (Qty: {$item['quantity']}) - $" . number_format($total_price, 2) . "<br>";

        // Insert each item into the 'purchased' table
        $insert_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'total_price' => $total_price
        ]);
    }

    // Delete items from the cart after purchase
    $delete_sql = "DELETE FROM cart WHERE user_id = :user_id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute(['user_id' => $user_id]);

    // Commit the transaction
    $pdo->commit();

    // Fetch user's email and name for sending the purchase confirmation email
    $userSql = "SELECT email, username FROM users WHERE user_id = :user_id";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute(['user_id' => $user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($cart_items)) {
        $email = $user['email'];
        $customer_name = $user['username'];

        // Get the current date and time
        $current_date_time = date("Y-m-d H:i:s");

        // Send confirmation email using PHPMailer
        require 'vendor/autoload.php';
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'joshbernabe0829@gmail.com'; // Replace with your Gmail address
            $mail->Password = 'gxve szls yifq rjqx';      // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Recipients
            $mail->setFrom('joshbernabe0829@gmail.com', 'Cirno\'s Fumos');
            $mail->addAddress($email, $customer_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Order Confirmation from Cirno's Fumos";
            $mail->Body = "
                <h1>Thank you for your purchase, {$customer_name}!</h1>
                <p>Your order details are as follows:</p>
                <p><strong>Order Items:</strong><br>{$order_details}</p>
                <p><strong>Total Amount:</strong> $" . number_format($total_cart_value, 2) . "</p>
                <p><strong>Order Date and Time:</strong> {$current_date_time}</p>
                <p>We will process your order and send you a shipping confirmation once it's on the way.</p>
                <p>Best regards,<br>Cirno's Fumos Team</p>
            ";

            // Send the email
            $mail->send();
            $mail_status = "Your order has been placed successfully, and a confirmation email has been sent.";

        } catch (Exception $e) {
            $mail_status = "Error sending confirmation email: " . $mail->ErrorInfo;
        }
    } else {
        $mail_status = "There was an issue with your order. Please try again later.";
    }

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error message
    error_log("Purchase error: " . $e->getMessage());

    // Show error message
    echo "Error: " . $e->getMessage();

    // Redirect with error message
    header("Location: cart.php?message=An error occurred while processing your purchase. Please try again.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Cirno's Fumos</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="cart.php">Your Cart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" onclick="return confirmLogout();">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center">Order Confirmation</h2>
    <div class="alert alert-info mt-3">
        <?php echo htmlspecialchars($mail_status); ?>
    </div>
    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-primary">Go Back to Home</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }
</script>

</body>
</html>
