<?php
// Database configuration
$host = "localhost";
$dbname = "crime_report";
$user = "root";
$pass = "";  // Change if your MySQL has a password

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
