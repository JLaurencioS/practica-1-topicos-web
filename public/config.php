<?php
// config.php - Conexión a la base de datos usando PDO (permite Prepared Statements)

$host = 'localhost'; 
$dbname = 'bd_22030236'; // nombre de tu base de datos
$user = 'u22030236';
$password = '22030236'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}