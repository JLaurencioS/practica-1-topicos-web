<?php
// editar.php - Modificar un usuario existente (Update del CRUD)

require 'auth.php';   // Bloquea el acceso si no hay sesión activa
require 'config.php';

$error = '';
$id = $_GET['id'] ?? $_POST['id'] ?? null;

// Validar que venga un ID numérico válido
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    header('Location: dashboard.php');
    exit();
}

// Cargar los datos actuales del usuario
$stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // opcional al editar

    // Validación de campos obligatorios
    if (empty($nombre) || empty($email)) {
        $error = 'Nombre y correo son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($nombre) < 2 || strlen($nombre) > 100) {
        $error = 'El nombre debe tener entre 2 y 100 caracteres.';
    } else {
        // Verificar que el email no lo tenga OTRO usuario
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);

        if ($check->fetch()) {
            $error = 'Ese correo ya está en uso por otro usuario.';
        } else {
            if (!empty($password)) {
                // Si se indicó nueva contraseña, se valida y se actualiza también
                if (strlen($password) < 6) {
                    $error = 'La contraseña debe tener al menos 6 caracteres.';
                } else {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$nombre, $email, $passwordHash, $id]);
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                // Sin cambiar contraseña
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
                $stmt->execute([$nombre, $email, $id]);
                header('Location: dashboard.php');
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar usuario - Mi App</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="contenedor">
        <h2>Editar usuario</h2>

        <?php if ($error): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="editar.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="password">Nueva contraseña (dejar en blanco para no cambiarla):</label>
            <input type="password" id="password" name="password">

            <button type="submit">Actualizar</button>
        </form>

        <a href="dashboard.php">Cancelar</a>
    </div>
</body>
</html>