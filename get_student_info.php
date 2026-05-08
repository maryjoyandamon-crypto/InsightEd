<?php
include 'connection.php';

if(isset($_POST['student_id'])) {
    header('Content-Type: application/json');

    $id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $query = mysqli_query($conn, "SELECT * FROM students WHERE student_id = '$id'");
    $data = mysqli_fetch_assoc($query);

    if($data) {
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Student not found']);
    }
}
?>