<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tid = $_SESSION['teacher_id'];
    $class_id = $_POST['class_id']; 
    
    $sy = mysqli_real_escape_string($conn, $_POST['school_year']);
    $sec = mysqli_real_escape_string($conn, $_POST['section']);
    $sub = mysqli_real_escape_string($conn, $_POST['subject']);

    if (!empty($class_id)) {
        $query = "UPDATE classes SET school_year='$sy', section='$sec', subject='$sub' WHERE id='$class_id' AND teacher_id='$tid'";
        $msg = "updated";
    } else {
        $query = "INSERT INTO classes (teacher_id, school_year, section, subject) 
                  VALUES ('$tid', '$sy', '$sec', '$sub')";
        $msg = "added";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: settings.php?msg=$msg");
    } else {
        header("Location: settings.php?msg=error");
    }
}
?>