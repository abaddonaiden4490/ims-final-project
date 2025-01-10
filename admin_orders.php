<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// Correct the use of PHPMailer and Exception classes with actual namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files (adjust these paths to your project structure)
require 'D:/Xampp/htdocs/php_CirnosFumos/src/Exception.php';
require 'D:/Xampp/htdocs/php_CirnosFumos/src/PHPMailer.php';
require 'D:/Xampp/htdocs/php_CirnosFumos/src/SMTP.php';

// Ensure the user is logged in and has an admin role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the user information to verify their role
$user_query = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

// Check if the user is an admin, if not, deny access
if (!isset($user_data['role']) || $user_data['role'] !== 'admin') {
    echo "<p>You do not have permission to view this page.</p>";
    exit;
}

// If this is a POST request to update the order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Prepare and execute the update query
    $update_query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, 'si', $status, $order_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p class='success-msg'>Order status updated successfully!</p>";

        // Fetch customer and order details for the email
        $orderinfo_query = "
        SELECT 
            o.order_id, o.status, o.order_date, o.shipping, o.total, 
            c.fname, c.lname, c.title, u.email, c.address, c.town, c.zipcode, 
            c.phone, c.profile_image
        FROM orders o
        JOIN customer c ON o.customer_id = c.customer_id
        JOIN users u ON c.user_id = u.user_id
        WHERE o.order_id = $order_id
    ";
        $orderinfo_result = mysqli_query($conn, $orderinfo_query);
        $orderinfo_data = mysqli_fetch_assoc($orderinfo_result);

        if ($orderinfo_data) {
            $customer_name = $orderinfo_data['title'] . ' ' . $orderinfo_data['fname'] . ' ' . $orderinfo_data['lname'];
            $user_email = $orderinfo_data['email'];
            $customer_address = $orderinfo_data['address'];
            $customer_town = $orderinfo_data['town'];
            $customer_zipcode = $orderinfo_data['zipcode'];
            $customer_phone = $orderinfo_data['phone'];
            $profile_image = $orderinfo_data['profile_image'];
            $order_date = $orderinfo_data['order_date'];
            $shipping = $orderinfo_data['shipping'];
            $total = $orderinfo_data['total'];

            // Initialize PHPMailer for email notification
            try {
                $mail = new PHPMailer(true); // Instantiate the PHPMailer object here

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'joshbernabe0829@gmail.com';  // Your Gmail address
                $mail->Password = 'gxve szls yifq rjqx';         // App password for Gmail or your password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;  // Use 587 for TLS encryption
                


                // Sender and recipient
                $mail->setFrom('joshbernabe0829@gmail.com', 'joshB');
                $mail->addAddress($user_email, $customer_name);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = "Order Confirmation for Order ID: {$order_id}";
                $mail->Body = "
                <h1>Thank you for your order, {$customer_name}!</h1>
                <p>We appreciate your purchase. Here are your order details:</p>
                <p><strong>Order ID:</strong> {$order_id}</p>
                <p><strong>Status:</strong> {$status}</p>
                <p><strong>Order Date:</strong> {$order_date}</p>
                <p><strong>Shipping Rate:</strong> ₱{$shipping}</p>
                <p><strong>Total Amount:</strong> ₱{$total}</p>
                <p><strong>Shipping Address:</strong></p>
                <p>{$customer_address}, {$customer_town}, {$customer_zipcode}</p>
                <p><strong>Phone:</strong> {$customer_phone}</p>
                <p>Thank you for your purchase!</p>
                <p>Best regards,<br>Cirno's Fumos</p>
                ";

                // Send email
                $mail->send();
                echo "Order confirmation email sent successfully!";
            } catch (Exception $e) {
                error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                echo "Error: Unable to send email. Please contact support.";
            }
        } else {
            echo "<p class='error-msg'>Error: Order details not found.</p>";
        }

        mysqli_free_result($orderinfo_result);
    } else {
        echo "<p class='error-msg'>Failed to update order status. Please try again.</p>";
    }

    mysqli_stmt_close($stmt);
}

// Fetch orders and their details
$query = "
    SELECT 
        o.order_id, 
        c.customer_id, 
        c.fname AS customer_name, 
        o.order_date, 
        o.total, 
        o.status, 
        o.shipping,
        GROUP_CONCAT(CONCAT(od.product_id, ' (Qty: ', od.quantity, ', Price: ₱', od.price, ')') SEPARATOR '<br>') AS product_details
    FROM orders o
    JOIN customer c ON o.customer_id = c.customer_id
    LEFT JOIN order_details od ON o.order_id = od.order_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching orders: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .heading {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .order-table th, .order-table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .order-table th {
            background-color: #4CAF50;
            color: white;
        }

        .order-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .order-table tr:hover {
            background-color: #ddd;
        }

        .update-form {
            display: inline;
        }

        .status-select {
            padding: 6px;
            margin-right: 10px;
        }

        .update-btn {
            padding: 6px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .update-btn:hover {
            background-color: #45a049;
        }

        .success-msg {
            color: green;
            text-align: center;
            font-weight: bold;
        }

        .error-msg {
            color: red;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="heading">Admin Orders Management</h2>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Product Details</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo $row['customer_name']; ?></td>
                        <td><?php echo $row['order_date']; ?></td>
                        <td>₱<?php echo number_format($row['total'], 2); ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['product_details']; ?></td>
                        <td>
                            <form class="update-form" method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Shipped" <?php if ($row['status'] == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="Delivered" <?php if ($row['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                </select>
                                <button type="submit" class="update-btn">Update Status</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
mysqli_free_result($result);
mysqli_close($conn);
?>
