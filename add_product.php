<?php
session_start();
require 'config.php';

$error = '';
$success = '';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 1) {
    $_SESSION['error'] = "You must be an admin to access this page.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];

        $name_pattern = "/^[a-zA-Z0-9\s]+$/";
        $description_pattern = "/^[a-zA-Z0-9\s,\.!?]+$/";
        $price_pattern = "/^\d+(\.\d{1,2})?$/"; 

        if (!preg_match($name_pattern, $name) || strlen($name) < 3) {
            $error = "Product name must be at least 3 characters long and contain only letters, numbers, and spaces.";
        }

        if (!preg_match($description_pattern, $description)) {
            $error = "Description can only contain letters, numbers, spaces, and punctuation (e.g., commas, periods).";
        }

        if (!preg_match($price_pattern, $price)) {
            $error = "Price must be a valid number with up to two decimal places.";
        }

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price) VALUES (:name, :description, :price)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->execute();

            $product_id = $pdo->lastInsertId();

            // Set default photo path
            $photo_path = 'photos/default_product_image.jpg';

            if (isset($_FILES['photos']) && $_FILES['photos']['error'][0] == 0) {
                $photo_name = $_FILES['photos']['name'][0];
                $photo_tmp = $_FILES['photos']['tmp_name'][0];
                $photo_error = $_FILES['photos']['error'][0];

                if ($photo_error === 0) {
                    $photo_ext = pathinfo($photo_name, PATHINFO_EXTENSION);
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array(strtolower($photo_ext), $allowed_ext)) {
                        $photo_new_name = uniqid('', true) . '.' . $photo_ext;
                        $upload_dir = __DIR__ . "/photos/";
                        
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $photo_path = $upload_dir . $photo_new_name;

                        if (move_uploaded_file($photo_tmp, $photo_path)) {
                            $photo_path = 'photos/' . $photo_new_name;
                        } else {
                            $error = "Failed to upload the photo.";
                        }
                    } else {
                        $error = "Invalid file type for photo. Only JPG, JPEG, PNG, and GIF are allowed.";
                    }
                } else {
                    $error = "Error uploading photo.";
                }
            }

            if (!$error) {
                // Update product photo only if a photo was uploaded
                $stmt = $pdo->prepare("UPDATE products SET photo = :photo WHERE product_id = :product_id");
                $stmt->bindParam(':photo', $photo_path);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();

                $success = "Product added successfully!";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT p.product_id, p.name, p.description, p.price, p.photo FROM products p ORDER BY p.product_id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cirno's Fumos - Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: black;
        }

        .container {
            background: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            padding: 30px;
            border-radius: 10px;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent navbar */
        }

        .navbar-brand, .nav-link {
            color: white !important;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
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
        <h2 class="text-center">Add New Product</h2>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_product.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" required>
            </div>

            <div class="mb-3">
                <label for="photos" class="form-label">Upload Photos</label>
                <input type="file" id="photos" name="photos[]" class="form-control" multiple>
            </div>

            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
        </form>

        <hr>

        <h3>All Products</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Photos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td>
                            <?php if ($product['photo']): ?>
                                <div class="d-flex">
                                    <img src="<?php echo htmlspecialchars($product['photo']); ?>" alt="Product Photo" style="width: 50px; height: 50px; margin-right: 10px;">
                                </div>
                            <?php else: ?>
                                No photos available.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-center">
            <a href="products.php" class="btn btn-secondary">Back to Product Management</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
