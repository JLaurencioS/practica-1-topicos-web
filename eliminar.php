<?php
// eliminar.php - Eliminar un usuario (Delete del CRUD)

require 'auth.php';   // Bloquea el acceso si no hay sesión activa
require 'config.php';

$id = $_GET['id'] ?? null;

// Validar que venga un ID numérico válido antes de tocar la base de datos
if ($id && filter_var($id, FILTER_VALIDATE_INT)) {
    // Prepared statement: evita inyección SQL
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: dashboard.php');
exit();