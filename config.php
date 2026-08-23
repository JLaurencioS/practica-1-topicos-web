<?php
// config.php - Conexión a la base de datos usando PDO (permite Prepared Statements)

$host = 'db'; // o 'db' si usas el docker-compose que armamos
$dbname = 'miapp_db';
$user = 'miapp_user';
$password = 'ClaveSegura123!'; // usa tu contraseña real

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}