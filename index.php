<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$role_id = $_SESSION['role_id'];

if (!isset($_SESSION['profile_picture'])) {
    $sql = "SELECT profile_picture FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if ($row && !empty($row['profile_picture'])) {
        $_SESSION['profile_picture'] = $row['profile_picture'];
    } else {
        $_SESSION['profile_picture'] = 'photos/default.png';
    }
}

$profile_picture = $_SESSION['profile_picture'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cirno's Fumos - Home</title>

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
    </style>
</head>
<body class="bg-light">

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
                    <?php if ($role_id !== 1): ?>
                    <li class="nav-item">
                        <a href="order_products.php" class="nav-link">Order Products</a> 
                    </li>
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link">Cart</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Manage
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                            <li><a class="dropdown-item" href="#" id="manageProductsLink">Manage Products</a></li>
                            <li><a class="dropdown-item" href="#" id="manageUsersLink">Manage Users</a></li>
                            <li><a class="dropdown-item" href="#" id="manageOrdersLink">Manage Orders</a></li> <!-- New Manage Orders link -->
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="logoutButton">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 content-wrapper">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h3>Welcome to Cirno's Fumos, <?php echo htmlspecialchars($username); ?>!</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="rounded-circle" style="width: 200px; height: 200px; object-fit: cover;">
                        </div>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <p><strong>Role:</strong> <?php echo $role_id === 1 ? 'Admin' : 'User'; ?></p>
                        <div class="alert alert-info" role="alert">
                            Use the navigation links above to manage your profile and access your pages.
                        </div>
                        <div class="text-center mt-3">
                            <a href="update_profile.php" class="btn btn-warning">Update Profile</a>
                        </div>
                        <?php if ($role_id !== 1): ?>
                        <div class="text-center mt-3">
                            <a href="reviews.php" class="btn btn-primary">Write a Review for Product</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-primary text-white text-center py-3 mt-auto">
        <p class="mb-0">Project in INFORMATION MANAGEMENT SYSTEMS (ITIM211-T)</p>
        <p class="mb-0">Submitted by:</p>
        <p class="mb-0"><strong>BERNABE, JOSH CHRISTIAN I.</strong></p>
        <p class="mb-0"><strong>MADERA, RENZ MARK A.</strong></p>
        <p class="mb-0"><strong>BSIT-S-T-2A-T</strong></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const roleId = <?php echo json_encode($role_id); ?>;

        document.getElementById('manageProductsLink').addEventListener('click', function(event) {
            if (roleId !== 1) { 
                event.preventDefault(); 
                alert("You are not authorized to access this page.");
                fetch('logout.php').then(() => window.location.href = 'logout.php');
            } else {
                window.location.href = 'products.php'; 
            }
        });

        document.getElementById('manageUsersLink').addEventListener('click', function(event) {
            if (roleId !== 1) { 
                event.preventDefault(); 
                alert("You are not authorized to access this page.");
                fetch('logout.php').then(() => window.location.href = 'logout.php');
            } else {
                window.location.href = 'users.php'; 
            }
        });

        document.getElementById('manageOrdersLink').addEventListener('click', function(event) {
            if (roleId !== 1) { 
                event.preventDefault(); 
                alert("You are not authorized to access this page.");
                fetch('logout.php').then(() => window.location.href = 'logout.php');
            } else {
                window.location.href = 'manage_orders.php'; 
            }
        });

        document.getElementById('logoutButton').addEventListener('click', function(event) {
            event.preventDefault(); 
            var confirmation = confirm("Are you sure you want to logout?");
            if (confirmation) {
                window.location.href = 'logout.php'; 
            }
        });
    </script>
</body>
</html>
