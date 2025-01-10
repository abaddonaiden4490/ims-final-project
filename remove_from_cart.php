<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to remove products from your cart.");
    exit();
}

if (isset($_GET['cart_id'])) {
    $cart_id = $_GET['cart_id'];
    $user_id = $_SESSION['user_id'];

    // Prepare the SQL query to remove the product from the cart
    $sql = "DELETE FROM cart WHERE cart_id = :cart_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cart_id' => $cart_id, 'user_id' => $user_id]);

    // Redirect back to the cart page with a success message
    header("Location: cart.php?message=Item removed from your cart.");
    exit();
} else {
    // Redirect to cart page if no cart_id is passed
    header("Location: cart.php?message=Invalid request.");
    exit();
}
?>
