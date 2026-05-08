<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $status = 'Approved';
    } elseif ($action === 'decline') {
        $status = 'Deactivated'; 
    }

    $sql = "UPDATE users SET status = '$status' WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_dashboard.php?msg=success");
    } else {
        header("Location: admin_dashboard.php?msg=error");
    }
}
?>