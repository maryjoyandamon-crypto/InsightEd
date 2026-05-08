<?php
$servername = "sql202.infinityfree.com";
$username = "if0_41864127";
$password = "ANG_IMONG_PASSWORD"; 
$dbname = "if0_41864127_insighted_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>