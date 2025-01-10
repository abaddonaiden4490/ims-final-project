<?php
session_start();

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #343a40;
        }
        .card {
            max-width: 400px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8); 
        }
    </style>
</head>
<body>
    <div class="card shadow text-center">
        <div class="card-header bg-primary text-white">
            <h3>You have been logged out</h3>
        </div>
        <div class="card-body">
            <p class="mb-4">Thank you for visiting. Click the button below to log in again.</p>
            <a href="login.php" class="btn btn-primary">Login Again</a>
        </div>
    </div>
</body>
</html>
