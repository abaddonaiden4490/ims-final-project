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

$stmt = $pdo->prepare("SELECT user_id, username, email, role_id, is_active, profile_picture, created_at FROM users ORDER BY user_id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['deactivate_id'])) {
    $deactivate_id = $_GET['deactivate_id'];

    $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $deactivate_id);
    $stmt->execute();

    $_SESSION['success'] = "User deactivated successfully.";
    header("Location: users.php");
    exit();
}

if (isset($_GET['update_role_id'])) {
    $update_role_id = $_GET['update_role_id'];
    $new_role = $_GET['new_role']; 

    $stmt = $pdo->prepare("UPDATE users SET role_id = :role_id WHERE user_id = :user_id");
    $stmt->bindParam(':role_id', $new_role);
    $stmt->bindParam(':user_id', $update_role_id);
    $stmt->execute();

    $_SESSION['success'] = "User role updated successfully.";
    header("Location: users.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $delete_id);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $delete_id);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM purchased WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $delete_id);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $delete_id);
        $stmt->execute();

        $pdo->commit();

        $_SESSION['success'] = "User and their associated data deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }

    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id']; 
    $username = $_POST['username'];
    $email = $_POST['email'];
    $profile_picture = $_FILES['profile_picture'];

    if ($profile_picture['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'photos/';  
        $file_name = basename($profile_picture['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($profile_picture['tmp_name'], $target_file)) {
            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, profile_picture = :profile_picture WHERE user_id = :user_id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':profile_picture', $target_file); 
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        } else {
            $_SESSION['error'] = "Error uploading the file.";
            header("Location: users.php");
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
    <title>Cirno's Fumos - Manage Users</title>
    
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
                        <a class="nav-link active" href="users.php">Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php" onclick="return confirmLogout();">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Manage Users</h2>

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

        <div class="row justify-content-center">
            <div class="col-md-10">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Profile Picture</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php echo $user['role_id'] == 1 ? 'Admin' : 'User'; ?>
                                    <br>
                                    <a href="users.php?update_role_id=<?php echo $user['user_id']; ?>&new_role=1" class="btn btn-warning btn-sm">Make Admin</a>
                                    <a href="users.php?update_role_id=<?php echo $user['user_id']; ?>&new_role=2" class="btn btn-info btn-sm">Make User</a>
                                </td>
                                <td>
                                    <?php $profile_pic = $user['profile_picture'] ? $user['profile_picture'] : 'photos/default.png'; ?>
                                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" style="width: 50px; height: 50px;">
                                </td>
                                <td>
                                    <?php echo $user['is_active'] ? 'Active' : 'Deactivated'; ?>
                                    <?php if ($user['is_active']): ?>
                                        <a href="users.php?deactivate_id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to deactivate this user?')" class="btn btn-danger btn-sm">Deactivate</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm">Edit</a>
                                    <a href="users.php?delete_id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to log out?");
        }
    </script>
</body>
</html>
