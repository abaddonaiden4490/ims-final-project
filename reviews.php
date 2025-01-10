<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to view your purchased products.");
    exit();
}

$user_id = $_SESSION['user_id'];

$bad_words = file('mura.txt', FILE_IGNORE_NEW_LINES);

function filter_bad_words($text, $bad_words) {
    foreach ($bad_words as $bad_word) {
        $pattern = '/\b' . preg_quote($bad_word, '/') . '\b/i';
        $text = preg_replace($pattern, '****', $text);
    }
    return $text;
}

$sql = "SELECT p.product_id, p.name, r.review_id, r.rating, r.comment  
        FROM products p
        LEFT JOIN reviews r ON p.product_id = r.product_id AND r.user_id = :user_id
        WHERE p.product_id IN (SELECT product_id FROM purchased WHERE user_id = :user_id)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $review_text = filter_bad_words($_POST['review_text'], $bad_words);

    $existing_review_sql = "SELECT review_id FROM reviews WHERE user_id = :user_id AND product_id = :product_id";
    $existing_review_stmt = $pdo->prepare($existing_review_sql);
    $existing_review_stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    $existing_review = $existing_review_stmt->fetch();

    if ($existing_review) {
        $update_sql = "UPDATE reviews SET rating = :rating, comment = :review_text, created_at = NOW()
                      WHERE review_id = :review_id";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([
            'rating' => $rating,
            'review_text' => $review_text,
            'review_id' => $existing_review['review_id']
        ]);
        $message = "Review updated successfully!";
    } else {
        $insert_sql = "INSERT INTO reviews (user_id, product_id, rating, comment, created_at)
                       VALUES (:user_id, :product_id, :rating, :review_text, NOW())";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            'user_id' => $user_id,
            'product_id' => $product_id,
            'rating' => $rating,
            'review_text' => $review_text
        ]);
        $message = "Review submitted successfully!";
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews - Cirno's Fumos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('background.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white; 
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1;
        }
        .header {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 3em;
            color: #fff;
        }
        .navbar {
            background-color: #007bff;
        }
        .card-body {
            background-color: rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="order_products.php" class="nav-link">Order Products</a> 
                    </li>
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a href="reviews.php" class="nav-link">My Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Write or Update Your Review</h1>
    </div>

    <div class="container mt-5 content-wrapper">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if (isset($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body">
                        <?php foreach ($products as $product): ?>
                            <div class="mb-4">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>

                                <?php if (isset($product['review_id'])): ?>
                                    <p>Current Rating: <?php echo htmlspecialchars($product['rating']); ?> / 10</p>
                                    <p>Current Review: <?php echo htmlspecialchars($product['comment']); ?></p>
                                <?php else: ?>
                                    <p>No review yet.</p>
                                <?php endif; ?>

                                <form method="POST" action="reviews.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating (1-10)</label>
                                        <input type="number" name="rating" id="rating" class="form-control" min="1" max="10" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="review_text" class="form-label">Your Review</label>
                                        <textarea name="review_text" id="review_text" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
