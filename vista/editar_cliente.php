<?php
//include '../modelo/conexion.php';
include '../modelo/conexion2.php';

// Elige la conexión que quieres usar: $conn o $conn2
$db = $conn2;  // Cambia a $conn2 si quieres usar la conexión remota

$id = (int)($_GET['id'] ?? 0);

$sql = "SELECT * FROM usuarios WHERE id_usuario = $id";
$result = $db->query($sql);
$cliente = $result ? $result->fetch_assoc() : null;

if (!$cliente) {
    // Si no se encontró el cliente, redirigir al listado
    header("Location: usuarios.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="../scr/css/editar_cliente.css">
</head>
<body>

    <header>
        <a href="#" class="logo">
            <img src="../scr/imagenes/logo.jpg" alt="Logo de Vaguettos">
            <h2>Vaguettos</h2>
        </a>

        <nav>
            <a href="#" class="nav-link">Editar datos del cliente</a>
        </nav>
    </header>

    <!-- Formulario de edición -->
    <div class="index-container">
        <form method="post" action="../controlador/actualizar_cliente.php" class="form-editar">
            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($cliente['id_usuario']) ?>">

            <label>Usuario:</label>
            <input type="text" name="usuario" value="<?= htmlspecialchars($cliente['usuario']) ?>" required>

            <label>Dirección:</label>
            <input type="text" name="direccion" value="<?= htmlspecialchars($cliente['direccion']) ?>">

            <label>Correo:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correo']) ?>" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>">

            <div class="form-group">
                <button type="submit" name="actualizar">Actualizar</button>
                <a href="usuarios.php" class="nav-link">Cancelar</a>
            </div>
        </form>
    </div>

</body>
</html>



