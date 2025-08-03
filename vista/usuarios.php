<?php 
//include '../modelo/conexion.php';   // $conn (local)
include '../modelo/conexion2.php';  // $conn2 (remota)

// Puedes cambiar esta línea para usar la conexión deseada:
$db = $conn2; // usa $conn2 para remoto
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrador de Clientes</title>
    <link rel="stylesheet" href="../scr/css/usuarios.css">
</head>
<body>

<header>
    <a href="#" class="logo">
        <img src="../scr/imagenes/logo.jpg" alt="Logo de Vaguettos">
        <h2>Vaguettos</h2>
    </a>

    <nav>
        <a href="indexadmin.php" class="nav-link">Inicio</a>
        <a href="#" class="nav-link">Administración de Clientes</a>
        <a href="pagos.php" class="nav-link">Administración de Pagos</a>
        <a href="inventario.php" class="nav-link">Gestión de Inventario</a>
        <a href="stock.php" class="nav-link">Listado de Productos</a>
        <a href="../vista/login.php" class="nav-link">Cerrar Sesión</a>
    </nav>

    <div>
        <h1 class="titulo-principal">Administración de Clientes</h1>
    </div>
</header>

<div class="index-container">
    <form method="post">
        <div class="btn">
            <input type="search" name="buscarCliente" placeholder="Buscar Cliente...">
            <button type="submit" name="buscar">
                <img src='../scr/imagenes/busqueda.png' alt='Buscar' width='20' height='20'>
            </button>
        </div>
    </form>
</div><br>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Dirección</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Editar</th>
            <th>Eliminar</th>
        </tr>
    </thead>
    <tbody>
    <?php
        // Filtro de búsqueda
        $condicion = "";
        if (isset($_POST['buscar'])) {
            $busqueda = $db->real_escape_string($_POST['buscarCliente']);
            $condicion = "WHERE usuario LIKE '%$busqueda%' OR correo LIKE '%$busqueda%'";
        }

        // Consulta
        $sql = "SELECT id_usuario, usuario, direccion, correo, telefono FROM usuarios $condicion";
        $resultado = $db->query($sql);

        // Mostrar resultados
        while ($row = $resultado->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id_usuario']}</td>
                    <td>{$row['usuario']}</td>
                    <td>{$row['direccion']}</td>
                    <td>{$row['correo']}</td>
                    <td>{$row['telefono']}</td>
                    <td><a href='editar_cliente.php?id={$row['id_usuario']}'><img src='../scr/imagenes/editar.png' alt='Editar' width='20' height='20'></a></td>
                    <td><a href='../controlador/eliminar_cliente.php?id={$row['id_usuario']}' onclick=\"return confirm('¿Deseas eliminar este cliente?')\"><img src='../scr/imagenes/eliminar.png' alt='Eliminar' width='20' height='20'></a></td>
                  </tr>";
        }
    ?>
    </tbody>
</table>

</body>
</html>
