<?php
// dashboard.php - Lista de usuarios registrados (Read del CRUD) + panel principal

require 'auth.php';   // Bloquea el acceso si no hay sesión activa
require 'config.php';

// Prepared statement para leer todos los usuarios
$stmt = $pdo->query("SELECT id, nombre, email, fecha_registro FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Mi App</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="contenedor">
        <div class="barra-superior">
            <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></h2>
            <a href="logout.php">Cerrar sesión</a>
        </div>

        <a href="crear.php" class="boton-nuevo">+ Nuevo usuario</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha de registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id']) ?></td>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['fecha_registro']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $u['id'] ?>">Editar</a> |
                        <a href="eliminar.php?id=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>