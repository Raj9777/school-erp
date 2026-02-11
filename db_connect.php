<?php
// AUTO-DETECT ENVIRONMENT
$server_name = $_SERVER['SERVER_NAME'];

if ($server_name == "localhost" || $server_name == "127.0.0.1") {
    // 🏠 LOCALHOST SETTINGS (Your Laptop)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "school_erp_db";
} else {
    // 🌐 LIVE SERVER SETTINGS (Hostinger)
    // We will fill these in later, but for now, put placeholders
    $servername = "localhost"; 
    $username = "u123456789_admin"; // We will update this soon
    $password = "YourStrongPassword"; // We will update this soon
    $dbname = "u123456789_school"; // We will update this soon
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>