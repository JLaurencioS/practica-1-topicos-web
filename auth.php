<?php
// auth.php - Verifica que exista una sesión activa antes de permitir el acceso

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}