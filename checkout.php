<?php
session_start();

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    require 'config.php';

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    $sql = "SELECT product_id, name, price FROM products WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }

        if (isset($_SESSION['user_id'])) {  
            $user_id = $_SESSION['user_id'];
            $cartCheckSql = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
            $cartCheckStmt = $pdo->prepare($cartCheckSql);
            $cartCheckStmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
            $existingCartItem = $cartCheckStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCartItem) {
                $updateCartSql = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id";
                $updateCartStmt = $pdo->prepare($updateCartSql);
                $updateCartStmt->execute(['quantity' => $quantity, 'user_id' => $user_id, 'product_id' => $product_id]);
            } else {
                $insertCartSql = "INSERT INTO cart (user_id, product_id, quantity, price, created_at) 
                                  VALUES (:user_id, :product_id, :quantity, :price, NOW())";
                $insertCartStmt = $pdo->prepare($insertCartSql);
                $insertCartStmt->execute([
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'price' => $product['price']
                ]);
            }

            // Fetch user's email
            $userSql = "SELECT email FROM users WHERE user_id = :user_id";
            $userStmt = $pdo->prepare($userSql);
            $userStmt->execute(['user_id' => $user_id]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $email = $user['email'];

                // Send email notification
                $subject = "Cart Update Notification";
                $message = "Hello,\n\nThe following item has been added to your cart:\n\n"
                         . "Product: " . htmlspecialchars($product['name']) . "\n"
                         . "Quantity: " . $quantity . "\n"
                         . "Price per item: $" . number_format($product['price'], 2) . "\n"
                         . "Total: $" . number_format($product['price'] * $quantity, 2) . "\n\n"
                         . "Thank you for shopping with us!\nCirno's Fumos Team";

                $headers = "From: no-reply@cirnosfumos.com\r\n";
                $headers .= "Reply-To: support@cirnosfumos.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8";

                mail($email, $subject, $message, $headers);
            }
        }

        header("Location: order_products.php?message=Successfully added to cart!");
        exit();
    } else {
        header("Location: order_products.php?message=Product not found.");
        exit();
    }
} else {
    header("Location: order_products.php");
    exit();
}
?>
