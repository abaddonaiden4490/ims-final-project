<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to edit your cart.");
    exit();
}

if (isset($_GET['cart_id'])) {
    $cart_id = $_GET['cart_id'];
    
    // Fetch cart item details
    $sql = "SELECT c.cart_id, p.name, c.quantity, c.price
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.cart_id = :cart_id AND c.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cart_id' => $cart_id, 'user_id' => $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        // If item is not found
        header("Location: cart.php?message=Item not found.");
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle form submission to update quantity
        $new_quantity = $_POST['quantity'];
        
        if ($new_quantity == 0) {
            // If quantity is 0, remove the item from the cart
            $deleteSql = "DELETE FROM cart WHERE cart_id = :cart_id";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute(['cart_id' => $cart_id]);
            
            // Redirect back to the cart with a message
            header("Location: cart.php?message=Item removed from cart.");
            exit();
        } else {
            // Update the cart item if quantity is not 0
            $updateSql = "UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute(['quantity' => $new_quantity, 'cart_id' => $cart_id]);
            
            // Redirect back to the cart with a success message
            header("Location: cart.php?message=Cart updated successfully.");
            exit();
        }
    }
} else {
    // Redirect if no cart_id is provided
    header("Location: cart.php?message=Invalid cart item.");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cart - Cirno's Fumos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Cirno's Fumos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="order_products.php">Order Products</a></li>
                <li class="nav-item"><a class="nav-link active" href="cart.php">Your Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php" onclick="return confirmLogout();">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center">Edit Cart Item</h2>

    <form action="edit_cart.php?cart_id=<?php echo $item['cart_id']; ?>" method="POST">
        <div class="mb-3">
            <label for="product_name" class="form-label">Product</label>
            <input type="text" class="form-control" id="product_name" value="<?php echo htmlspecialchars($item['name']); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update Quantity</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function confirmLogout() {
        return confirm("Are you sure you want to log out?");
    }
</script>

</body>
</html>
