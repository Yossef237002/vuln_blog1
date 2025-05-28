<?php
$servername = "localhost";
$username = "root";
$password = "root";  // set your password
$dbname = "blogdb";
$port = 8889;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
