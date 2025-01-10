<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 1) {
    $_SESSION['error'] = "You must be an admin to access this page.";
    header("Location: login.php");
    exit();
}

require 'config.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header("Location: products.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];

        $name_pattern = "/^[a-zA-Z0-9\s]+$/"; 
        $description_pattern = "/^[a-zA-Z0-9\s,\.!?]+$/"; 
        $price_pattern = "/^\d+(\.\d{1,2})?$/"; 

        if (!preg_match($name_pattern, $name) || strlen($name) < 3) {
            $_SESSION['error'] = "Product name must be at least 3 characters long and contain only letters, numbers, and spaces.";
        }

        if (!preg_match($description_pattern, $description)) {
            $_SESSION['error'] = "Description can only contain letters, numbers, spaces, and punctuation (e.g., commas, periods).";
        }

        if (!preg_match($price_pattern, $price)) {
            $_SESSION['error'] = "Price must be a valid number with up to two decimal places.";
        }

        if (!isset($_SESSION['error'])) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/photos/";
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            $new_photo_path = $product['photo']; 

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $photo_name = $_FILES['photo']['name'];
                $photo_tmp = $_FILES['photo']['tmp_name'];
                $photo_error = $_FILES['photo']['error'];

                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                if ($photo_error === 0) {
                    $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
                    if (in_array($photo_ext, $allowed_ext)) {
                        $photo_new_name = uniqid('', true) . '.' . $photo_ext;
                        $photo_path = $upload_dir . $photo_new_name;
                        $relative_photo_path = '/photos/' . $photo_new_name;

                        if (move_uploaded_file($photo_tmp, $photo_path)) {
                            $new_photo_path = $relative_photo_path; 
                        } else {
                            $_SESSION['error'] = "Error moving uploaded photo.";
                        }
                    } else {
                        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                    }
                } else {
                    $_SESSION['error'] = "Error uploading photo.";
                }
            }

            if (!isset($_SESSION['error'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, photo = :photo WHERE product_id = :product_id");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':photo', $new_photo_path);
                    $stmt->bindParam(':product_id', $product_id);
                    $stmt->execute();

                    $_SESSION['success'] = "Product updated successfully.";
                    header("Location: products.php");
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Failed to update product: " . $e->getMessage();
                    header("Location: edit_product.php?id=$product_id");
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cirno's Fumos - Edit Product</title>
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
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Manage Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center">Edit Product</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit_product.php?id=<?php echo $product['product_id']; ?>" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Product Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Upload Photo</label>
            <input type="file" id="photo" name="photo" class="form-control">
            <?php if ($product['photo']): ?>
                <img src="<?php echo htmlspecialchars($product['photo']); ?>" alt="Product Photo" class="img-fluid mt-3" style="max-height: 200px;">
            <?php endif; ?>
        </div>

        <button type="submit" name="edit_product" class="btn btn-primary">Update Product</button>
    </form>

    <div class="text-center mt-3">
        <a href="products.php" class="btn btn-secondary">Back to Product Management</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
