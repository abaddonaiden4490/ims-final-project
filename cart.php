<?php 
session_start();
require 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to view your cart.");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$sql = "SELECT c.cart_id, p.name, c.quantity, c.price, (c.quantity * c.price) AS total_price
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);

$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the cart is empty
if (empty($cart_items)) {
    $mail_status = "Your cart is empty. Please add items before proceeding.";
    error_log("Cart is empty for user_id: " . $user_id);
} else {
    // Calculate the total cart value
    $total_cart_value = 0;
    foreach ($cart_items as $item) {
        $total_cart_value += $item['total_price'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Cirno's Fumos</title>
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
    <h2 class="text-center">Your Shopping Cart</h2>

    <!-- Show message if there is any -->
    <?php if (!empty($mail_status)): ?>
        <div class="alert alert-info mt-3">
            <?php echo htmlspecialchars($mail_status); ?>
        </div>
    <?php endif; ?>

    <!-- Check if cart is empty -->
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
        <?php 
        $total_cart_value = 0;
        foreach ($cart_items as $item): 
        ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td>$<?php echo number_format($item['price'], 2); ?></td>
                <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                <td>
                    <!-- Edit Button -->
                    <a href="edit_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    
                    <!-- Remove Button -->
                    <a href="remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                </td>
            </tr>
        <?php 
            $total_cart_value += $item['total_price'];
        endforeach;
        ?>
    </tbody>
        </table>

        <p class="total-cart">Total Cart Value: $<?php echo number_format($total_cart_value, 2); ?></p>

        <div class="text-center mt-3">
            <!-- Pass user_id to purchase.php -->
            <a href="purchase.php?user_id=<?php echo $user_id; ?>" class="btn btn-success">Purchase Item</a>
        </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }
</script>

</body>
</html>
