<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['sy']) && isset($_GET['sec']) && isset($_GET['sub'])) {
    
    $sy   = mysqli_real_escape_string($conn, $_GET['sy']);
    $sec  = mysqli_real_escape_string($conn, $_GET['sec']);
    $sub  = mysqli_real_escape_string($conn, $_GET['sub']);
    $dept = $_SESSION['department'] ?? $_SESSION['subject'] ?? '';
    $filterField = isset($_SESSION['subject']) ? 'subject' : 'department';

    $query = "DELETE FROM students 
              WHERE school_year = '$sy' 
              AND section = '$sec' 
              AND subject = '$sub' 
              AND $filterField = '$dept'";

    if (mysqli_query($conn, $query)) {
        header("Location: settings.php?msg=deleted_successfully");
    } else {
        header("Location: settings.php?msg=database_error");
    }
} else {
    header("Location: settings.php");
}
exit();
?>