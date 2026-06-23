<?php
// Database configuration
$host = "localhost";
$dbname = "crime_report";
$user = "labuser";
$pass = "lab1234";  // Change if your MySQL has a password

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
