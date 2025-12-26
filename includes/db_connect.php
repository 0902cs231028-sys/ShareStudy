<?php
// Database Configuration
$servername = "whatever.com"; 
$username   = "your name";            
$dbname     = "your choice"; 
$password   = "whatever u wish"; 

try {
    // PDO is safer and better for preventing SQL injection than old MySQLi
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set the PDO error mode to exception
    // This will help you see errors if something goes wrong
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array (easier to use data)
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Uncomment the line below only to test if it works, then re-comment it
    // echo "Connected successfully"; 

} catch(PDOException $e) {
    // If connection fails, stop everything and show error
    die("Connection failed: " . $e->getMessage());
}
?>
