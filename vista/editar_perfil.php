<?php
session_start();
require_once '../modelo/conexion2.php';

// Verificar sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../vista/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = "";
$claveHash = null; // <- Aseguramos la existencia de la variable

// Obtener datos actuales del usuario
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn2->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_completo']);
    $user = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $clave1 = $_POST['clave1'];
    $clave2 = $_POST['clave2'];

    if (!empty($clave1) || !empty($clave2)) {
        if ($clave1 !== $clave2) {
            $mensaje = "Las contraseñas no coinciden.";
        } else {
            $claveHash = password_hash($clave1, PASSWORD_DEFAULT);
        }
    }

    if ($mensaje === "") {
        $sql = "UPDATE usuarios SET nombre_completo=?, usuario=?, correo=?, direccion=?, telefono=?";
        if ($claveHash !== null) {
            $sql .= ", clave=?";
        }
        $sql .= " WHERE id_usuario=?";

        $stmt = $conn2->prepare($sql);

        if ($claveHash !== null) {
            $stmt->bind_param("ssssssi", $nombre, $user, $correo, $direccion, $telefono, $claveHash, $id_usuario);
        } else {
            $stmt->bind_param("sssssi", $nombre, $user, $correo, $direccion, $telefono, $id_usuario);
        }

        if ($stmt->execute()) {
            $mensaje = "Perfil actualizado correctamente.";
            // Refrescar datos locales
            $usuario['nombre_completo'] = $nombre;
            $usuario['usuario'] = $user;
            $usuario['correo'] = $correo;
            $usuario['direccion'] = $direccion;
            $usuario['telefono'] = $telefono;
        } else {
            $mensaje = "Error al actualizar: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - Vaguettos</title>
    <link rel="stylesheet" href="../scr/css/stock.css">
    <link rel="stylesheet" href="../scr/css/editar_perfil.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a href="index.php" class="nav-link">Inicio</a>
    <a href="catalogo.php" class="nav-link">Catalogo de Productos</a>
    <a href="carrito.php" class="nav-link">Carrito de Compras</a>
    <a href="#" class="nav-link">Editar Perfil</a>
    <a href="../vista/login.php" class="nav-link">Cerrar Sesión</a>
</nav>

<!-- ENCABEZADO -->
<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos">
    <h1>Editar Perfil</h1>
</header>

<!-- MENSAJE -->
<?php if ($mensaje): ?>
    <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<!-- FORMULARIO -->
<section>
    <form method="POST" action="">
        <label for="nombre_completo">Nombre completo:</label>
        <input type="text" name="nombre_completo" id="nombre_completo" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" required>

        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" id="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>

        <label for="correo">Correo electrónico:</label>
        <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>

        <label for="direccion">Dirección:</label>
        <textarea name="direccion" id="direccion"><?= htmlspecialchars($usuario['direccion']) ?></textarea>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>">

        

        <input type="submit" value="Actualizar perfil">
    </form>
</section>

</body>
</html>
