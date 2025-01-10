<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];

    $sql = "SELECT product_id, name, description, price, photo
            FROM products
            WHERE product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $review_sql = "SELECT r.review_id, r.user_id, r.rating, r.comment, r.created_at, u.username AS user_name, u.profile_picture 
                   FROM reviews r
                   JOIN users u ON r.user_id = u.user_id
                   WHERE r.product_id = :product_id 
                   ORDER BY r.created_at DESC";
    $review_stmt = $pdo->prepare($review_sql);
    $review_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $review_stmt->execute();
    $reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: order_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
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
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .product-image {
            max-height: 300px;
            object-fit: cover;
            width: 100%;
        }

        .reviews-section {
            margin-top: 30px;
        }

        .review {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .review .rating {
            font-weight: bold;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
        }

        .reviewer-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>

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
                            <a class="nav-link" href="order_products.php">Order Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reviews.php">Write a Review</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mt-5">
            <h3 class="text-center mb-4"><?php echo htmlspecialchars($product['name']); ?></h3>

            <div class="row">
                <div class="col-md-6">
                    <?php
                    $first_photo = htmlspecialchars($product['photo']);
                    if (empty($first_photo) || $first_photo == 'photos/default_product_image.jpg') {
                        $first_photo = "photos/default_product_image.jpg";
                    }
                    ?>
                    <img src="<?php echo $first_photo; ?>" class="product-image" alt="Product Image">
                </div>
                <div class="col-md-6">
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                    <a href="order.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-primary">Order Now</a>
                </div>
            </div>

            <div class="reviews-section">
                <h4 class="text-center mb-4">Customer Reviews</h4>

                <?php if (empty($reviews)): ?>
                    <p class="text-center">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review">
                            <div class="reviewer-info">
                                <?php
                                $profile_picture = htmlspecialchars($review['profile_picture']);
                                if (empty($profile_picture)) {
                                    $profile_picture = "photos/default.png";
                                }
                                ?>
                                <img src="<?php echo $profile_picture; ?>" alt="User Profile Picture">
                                <strong><?php echo htmlspecialchars($review['user_name']); ?></strong>
                            </div>
                            <p class="rating">Rating: <?php echo $review['rating']; ?> stars</p>
                            <p><strong>Comment:</strong> <?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            <p><small>Reviewed on: <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small></p>
                        </div>
                    <?php endforeach; ?>
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
