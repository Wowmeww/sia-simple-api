<?php
// Database configuration
$host = "localhost";
$db_name = "sia2_db"; // your database name
$username = "root";   // default user in XAMPP
$password = "secret";       // empty password by default

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);

    // Set error mode to exception for better debugging
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Disable emulated prepares for better security
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Return error as JSON and stop execution
    echo json_encode([
        "error" => "Database connection failed",
        "details" => $e->getMessage()
    ]);
    exit;
}
