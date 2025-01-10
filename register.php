<?php
session_start();
require 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
            $error = "Username must be 3-20 characters long and can only contain letters, numbers, underscores, or dashes.";
        }

        elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $email)) {
            $error = "Invalid email format.";
        }

        elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
        }

        elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'photos/';  
            $fileName = basename($_FILES['profile_picture']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
            if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
                $error = "Only JPEG, PNG, and GIF image files are allowed for the profile picture.";
            }

            elseif ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
                $error = "Profile picture size must be less than 2MB.";
            }

            else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); 
                }

                if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
                    $error = "Error uploading profile picture.";
                } else {
                    $profilePicturePath = $uploadFile;
                }
            }
        } else {
            $profilePicturePath = null; 
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userExists) {
                $error = "Username or Email already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, profile_picture, is_active, role_id) 
                                        VALUES (:username, :email, :password, :profile_picture, 1, 2)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password); 
                $stmt->bindParam(':profile_picture', $profilePicturePath);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User registration complete! Please login.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Error registering user. Please try again.";
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
    <title>Cirno's Fumos - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url('background.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <img src="logo.png" alt="Cirno's Fumos" style="width: 200px; height: auto;" class="mb-2">
                        <h2>Register</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="register.php" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Profile Picture</label>
                                <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-secondary w-100">Already have an account? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
