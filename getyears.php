<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dmpcs_dashboard";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Execute SQL query to get years data
$sql1 = "SELECT  DISTINCT SchoolYear FROM time_period";
$result1 = $conn->query($sql1);

if ($result1 === FALSE) {
    die("Query failed: " . $conn->error);
} else if ($result1->num_rows === 0) {
    die("No data found");
} else {
}

// put the years data into an array

$years = [];
while ($row = $result1->fetch_assoc()) {
    $years[] = $row;
}

// Closing database connection
$conn->close();

// Combining years data into a single array
$data = [
    'years' => $years
];
?>
