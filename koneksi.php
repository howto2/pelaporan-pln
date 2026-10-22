<?php
$servername = "localhost";
$username = "root";
$password = ""; // Sesuaikan dengan password MySQL Anda
$dbname = "data_godzilla";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");

// Fungsi untuk membantu debugging
function debugSQL($conn, $query)
{
    if (!$result = $conn->query($query)) {
        die("Error in query: " . $query . " - " . $conn->error);
    }
    return $result;
}
?>