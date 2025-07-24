<?php
include '../modelo/conexion.php';

$id = $_GET['id'];

$sql = "SELECT * FROM usuarios WHERE id_usuario = $id";
$result = $conn->query($sql);
$cliente = $result->fetch_assoc();
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
            
        
            <input type="hidden" name="id_usuario" value="<?= $cliente['id_usuario'] ?>">

            <label>Usuario:</label>
            <input type="text" name="usuario" value="<?= $cliente['usuario'] ?>" required>

            <label>Dirección:</label>
            <input type="text" name="direccion" value="<?= $cliente['direccion'] ?>">

            <label>Correo:</label>
            <input type="email" name="correo" value="<?= $cliente['correo'] ?>" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?= $cliente['telefono'] ?>">

            <div class="form-group">
                <button type="submit" name="actualizar">Actualizar</button>
                <a href="usuarios.php" class="nav-link">Cancelar</a>
            </div>
        </form>
    </div>

</body>
</html>


