<?php
session_start();
include 'connection.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    $delete_query = "DELETE FROM students WHERE student_id = '$id'";

    if (mysqli_query($conn, $delete_query)) {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body style='background-color: #121212;'>
            <script>
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The student record has been permanently removed.',
                    icon: 'success',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#2ecc71'
                }).then(() => {
                    window.location.href = 'students.php';
                });
            </script>
        </body>
        </html>";
        exit();
    } else {
        $error_msg = mysqli_real_escape_string($conn, mysqli_error($conn));
        echo "
        <!DOCTYPE html>
        <html>
        <head>
        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body style='background-color: #121212;'>
            <script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Could not delete record: $error_msg',
                    icon: 'error',
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#e74c3c'
                }).then(() => {
                    window.location.href = 'students.php';
                });
            </script>
        </body>
        </html>";
    }
} else {
    header("Location: students.php");
    exit();
}
?>