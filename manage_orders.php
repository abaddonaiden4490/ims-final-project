<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 1) {
    header("Location: login.php");
    exit();
}

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $purchase_id = $_POST['purchase_id'];
    $status = $_POST['status'];

    if ($status === 'Pending') {
        $status = 1;
    } elseif ($status === 'Shipped') {
        $status = 2;
    } elseif ($status === 'Delivered') {
        $status = 3;
    }

    $sql = "UPDATE purchased SET status = ? WHERE purchase_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status, $purchase_id]);
}

$sql = "
    SELECT 
        purchased.purchase_id,
        purchased.quantity,
        purchased.total_price,
        purchased.purchase_date,
        purchased.status,
        users.username AS user_name,
        products.name AS product_name
    FROM purchased
    JOIN users ON purchased.user_id = users.user_id
    JOIN products ON purchased.product_id = products.product_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll();

function getStatusText($status) {
    switch ($status) {
        case 1: return 'Pending';
        case 2: return 'Shipped';
        case 3: return 'Delivered';
        default: return 'Unknown';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: black;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); 
            border-radius: 10px;
            padding: 20px;
        }

        .navbar {
            z-index: 9999;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Cirno's Fumos</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h3 class="text-center">Manage Orders</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User Name</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Purchase Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['purchase_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($order['total_price']); ?></td>
                        <td><?php echo htmlspecialchars($order['purchase_date']); ?></td>
                        <td><?php echo getStatusText($order['status']); ?></td>
                        <td>
                            <form method="POST" action="manage_orders.php">
                                <input type="hidden" name="purchase_id" value="<?php echo $order['purchase_id']; ?>">
                                <select name="status" class="form-select" required>
                                    <option value="Pending" <?php if ($order['status'] == 1) echo 'selected'; ?>>Pending</option>
                                    <option value="Shipped" <?php if ($order['status'] == 2) echo 'selected'; ?>>Shipped</option>
                                    <option value="Delivered" <?php if ($order['status'] == 3) echo 'selected'; ?>>Delivered</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary mt-2">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer class="bg-primary text-white text-center py-3 mt-auto">
        <p class="mb-0">Project in INFORMATION MANAGEMENT SYSTEMS (ITIM211-T)</p>
        <p class="mb-0">Submitted by:</p>
        <p class="mb-0"><strong>BERNABE, JOSH CHRISTIAN I.</strong></p>
        <p class="mb-0"><strong>MADERA, RENZ MARK A.</strong></p>
        <p class="mb-0"><strong>BSIT-S-T-2A-T</strong></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
