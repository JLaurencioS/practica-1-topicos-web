<?php
// logout.php - Destruye la sesión activa y redirige al login

session_start();
session_unset();      // Elimina todas las variables de sesión
session_destroy();    // Destruye la sesión por completo
header('Location: login.php');
exit();