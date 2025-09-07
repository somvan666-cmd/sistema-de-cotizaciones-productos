<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mi_base';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    
    // Set PDO attributes for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Function to get database connection
function getConnection() {
    global $pdo;
    return $pdo;
}
?>
