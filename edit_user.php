<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] !== 1) { 
    $_SESSION['error'] = "You must be an admin to access this page.";
    session_unset();  
    session_destroy(); 
    header("Location: login.php");  
    exit();
}

require 'config.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT user_id, username, email, profile_picture FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header("Location: users.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No user ID provided.";
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $profile_picture = $_FILES['profile_picture'];

    if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
        $_SESSION['error'] = "Username must be 3-20 characters long and can only contain letters, numbers, underscores, or dashes.";
        header("Location: edit_user.php?id=" . $user_id);
        exit();
    }

    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/', $email)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: edit_user.php?id=" . $user_id);
        exit();
    }

    if ($profile_picture['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'photos/';
        $file_name = basename($profile_picture['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, profile_picture = :profile_picture WHERE user_id = :user_id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':profile_picture', 'photos/' . $file_name);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        } else {
            $_SESSION['error'] = "Error uploading the file.";
            header("Location: edit_user.php?id=" . $user_id);
            exit();
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE user_id = :user_id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }

    $_SESSION['success'] = "Profile updated successfully.";
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center center;
            background-attachment: fixed;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Cirno's Fumos</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php" onclick="return confirmLogout();">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Edit User Profile</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form action="edit_user.php?id=<?php echo $user['user_id']; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                <img src="<?php echo htmlspecialchars($user['profile_picture'] ? $user['profile_picture'] : 'photos/default.png'); ?>" alt="Current Profile Picture" style="width: 100px; height: 100px; margin-top: 10px;">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>
</html>
