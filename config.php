<?php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'abaraonyechidera@gmail.com');
define('SMTP_PASSWORD', 'ubthpoweycaqemvc');
function getConnection() {
    // Database configuration
    $servername = "localhost";  // From your phpMyAdmin screenshot
    $username = "root";             // Your database username
    $password = "";                 // Your database password
    $dbname = "ashesi_athletics";  // From your phpMyAdmin screenshot

    // Database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Optionally set timezone
    date_default_timezone_set('UTC');

    // Return the connection object
    return $conn;
}
?>