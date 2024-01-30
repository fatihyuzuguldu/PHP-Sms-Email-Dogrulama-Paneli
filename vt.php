<?php
setlocale(LC_TIME, 'tr_TR.utf8');
date_default_timezone_set('Europe/Istanbul');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ali";

try {
    // Create a PDO database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

    // Set PDO to throw exceptions on errors
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Display error message and stop the execution in case of connection failure
    die("Failed to connect to the database: " . $e->getMessage());
}
