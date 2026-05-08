<?php
$servername = "localhost";
$username = "admin";
$password = "@dm1n_061313";
$dbname = "insighted";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";
?>