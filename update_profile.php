<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, email, profile_picture, password FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $profile_picture = $_FILES['profile_picture'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters long and can only contain letters, numbers, underscores, or dashes.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $email)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change the password.";
        } elseif ($current_password !== $user['password']) {
            $errors[] = "Current password is incorrect.";
        }

        if (empty($new_password)) {
            $errors[] = "New password is required.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
            $errors[] = "New password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "New password and confirmation do not match.";
        }
    }

    $new_profile_picture = $user['profile_picture']; 
    if ($profile_picture['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'photos/';
        $file_name = basename($profile_picture['name']);
        $target_file = $upload_dir . $file_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($profile_picture['type'], $allowed_types)) {
            $errors[] = "Only JPEG, PNG, and GIF files are allowed.";
        } elseif ($profile_picture['size'] > 2 * 1024 * 1024) {
            $errors[] = "File size should not exceed 2MB.";
        } else {
            if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
                $new_profile_picture = $target_file;
            } else {
                $errors[] = "Error uploading the file.";
            }
        }
    }

    if (empty($errors)) {
        $updated_password = !empty($new_password) ? $new_password : $user['password'];

        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, profile_picture = :profile_picture, password = :password WHERE user_id = :user_id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':profile_picture', $new_profile_picture);
        $stmt->bindParam(':password', $updated_password);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $_SESSION['username'] = $username; 
        $_SESSION['profile_picture'] = $new_profile_picture;

        $_SESSION['success'] = "Profile updated successfully.";
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h3>Update Profile</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="update_profile.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3 text-center">
                                <label for="currentProfilePicture" class="form-label">Current Profile Picture</label>
                                <br>
                                <img src="<?php echo htmlspecialchars($user['profile_picture'] ?: 'photos/default.png'); ?>" 
                                     alt="Profile Picture" 
                                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="profilePicture" class="form-label">Upload New Profile Picture</label>
                                <input type="file" name="profile_picture" id="profilePicture" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="currentPassword" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="newPassword" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-control">
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-success">Update Profile</button>
                                <a href="index.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
