<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'];

if (isset($_POST['upload'])) {
    $filename = $_FILES["image"]["name"];
    $tempname = $_FILES["image"]["tmp_name"];
    $new_filename = time() . "_" . $filename; // Para dili magkapareha ang ngalan
    $folder = "uploads/" . $new_filename;

    if (!is_dir('uploads')) { mkdir('uploads'); }

    if (move_uploaded_file($tempname, $folder)) {
        $sql = "UPDATE users SET profile_picture = '$new_filename' WHERE user_id = '$user_id'";
        mysqli_query($conn, $sql);
        header("Location: admin_dashboard.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/admin.css">
    <title>Edit Profile</title>
</head>
<body>
    <div class="admin-wrapper" style="justify-content: center; align-items: center;">
        <div class="glass-card" style="width: 400px; text-align: center;">
            <h2>Update Profile Picture</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="file" name="image" required style="margin: 20px 0;">
                <br>
                <button type="submit" name="upload" class="btn approve">Save Changes</button>
                <a href="admin_dashboard.php" class="btn decline">Back</a>
            </form>
        </div>
    </div>
</body>
</html>