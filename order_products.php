<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'D:/Xampp/htdocs/php_CirnosFumos/src/Exception.php';
require 'D:/Xampp/htdocs/php_CirnosFumos/src/PHPMailer.php';
require 'D:/Xampp/htdocs/php_CirnosFumos/src/SMTP.php';

// Check for purchase action in the URL
if (isset($_GET['action']) && $_GET['action'] === 'purchase') {
    // Handle the purchase action, send email, and process the cart

    $user_id = $_SESSION['user_id'];

    // Fetch user email and other details
    $sql = "SELECT email, username FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $email = $user['email'];
        $customer_name = $user['username'];

        // Fetch cart items
        $cartSql = "SELECT c.cart_id, p.name, c.quantity, c.price, (c.quantity * c.price) AS total_price
                    FROM cart c
                    JOIN products p ON c.product_id = p.product_id
                    WHERE c.user_id = :user_id";
        $cartStmt = $pdo->prepare($cartSql);
        $cartStmt->execute(['user_id' => $user_id]);
        $cart_items = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            $message = "Your cart is empty. Please add items before proceeding.";
        } else {
            // Calculate the total cart value
            $total_cart_value = 0;
            foreach ($cart_items as $item) {
                $total_cart_value += $item['total_price'];
            }

            try {
                // Send the email
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'joshbernabe0829@gmail.com'; // Your Gmail address
                $mail->Password = 'gxve szls yifq rjqx';      // App password or Gmail password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('joshbernabe0829@gmail.com', 'Cirno\'s Fumos');
                $mail->addAddress($email, $customer_name);

                $mail->isHTML(true);
                $mail->Subject = "Order Confirmation from Cirno's Fumos";
                $mail->Body = "
                    <h1>Thank you for your purchase, {$customer_name}!</h1>
                    <p>Your order details are as follows:</p>
                    <p><strong>Order Items:</strong><br>";

                foreach ($cart_items as $item) {
                    $mail->Body .= "{$item['name']} (Qty: {$item['quantity']}) - $" . number_format($item['total_price'], 2) . "<br>";
                }

                $mail->Body .= "<p><strong>Total Amount:</strong> $" . number_format($total_cart_value, 2) . "</p>
                                <p>We will process your order and send you a shipping confirmation once it's on the way.</p>
                                <p>Best regards,<br>Cirno's Fumos Team</p>
                                ";

                $mail->send();
                $message = "Your order has been placed successfully, and a confirmation email has been sent.";

                // Clear the cart after successful purchase
                $clearCartSql = "DELETE FROM cart WHERE user_id = :user_id";
                $pdo->prepare($clearCartSql)->execute(['user_id' => $user_id]);

            } catch (Exception $e) {
                $message = "Error sending confirmation email: " . $mail->ErrorInfo;
            }
        }
    } else {
        $message = "User not found. Please try again later.";
    }
} else {
    $message = ""; // No purchase action, no email sent
}

$sql = "SELECT product_id, name, description, price, photo
        FROM products
        ORDER BY product_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cirno's Fumos - Order Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content-wrapper {
            flex: 1;
        }

        footer {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: auto;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Optional shadow for more depth */
        }
    </style>
</head>
<body class="bg-light">

    <div class="content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <img src="headerlogo.png" alt="Cirno's Fumos Logo" style="width: 50px; height: auto; margin-right: 10px;">
                    Cirno's Fumos
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="order_products.php">Order Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reviews.php">Write a Review</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">Cart</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mt-5">
            <h3 class="text-center mb-4">Order Products</h3>

            <?php
            if (!empty($message)) {
                echo "<div class='alert alert-info' role='alert'>$message</div>";
            }
            ?>

            <div class="row justify-content-center">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card shadow">
                                <?php
                                $first_photo = htmlspecialchars($product['photo']);
                                if (empty($first_photo) || $first_photo == 'photos/default_product_image.jpg') {
                                    $first_photo = "photos/default_product_image.jpg";
                                }
                                ?>
                                <img src="<?php echo $first_photo; ?>" class="card-img-top" alt="Product Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                                    <a href="order.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-primary">Order Now</a>
                                    <a href="product_details.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-info mt-2">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No products available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p class="mb-0">Project in INFORMATION MANAGEMENT SYSTEMS (ITIM211-T)</p>
        <p class="mb-0">Submitted by:</p>
        <p class="mb-0"><strong>BERNABE, JOSH CHRISTIAN I.</strong></p>
        <p class="mb-0"><strong>MADERA, RENZ MARK A.</strong></p>
        <p class="mb-0"><strong>BSIT-S-T-2A-T</strong></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
