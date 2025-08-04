<?php
session_start();
require_once '../modelo/conexion2.php';

$searchValue = '';
$where = "";

if (isset($_POST['search']) && !empty(trim($_POST['valueToSearch']))) {
    $searchValue = trim($_POST['valueToSearch']);
    $searchEscaped = $conn2->real_escape_string($searchValue);
    $where = "AND (u.usuario LIKE '%$searchEscaped%' OR c.fecha_creacion LIKE '%$searchEscaped%')";
}

$query = "SELECT 
            c.id_carrito, 
            u.usuario, 
            c.fecha_creacion,
            IFNULL((
                SELECT SUM(p.precio * cp.cantidad)
                FROM carrito_productos cp
                JOIN productos p ON cp.id_producto = p.id_producto
                WHERE cp.id_carrito = c.id_carrito
            ), 0) AS total
          FROM carritos c
          JOIN usuarios u ON c.id_usuario = u.id_usuario
          WHERE 1=1 $where
          ORDER BY c.fecha_creacion DESC";

$result = $conn2->query($query);
if (!$result) {
    die("Error en la consulta: " . $conn2->error);
}
?>
<!-- aquí continúa el HTML igual -->
 

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Administrador de Pagos - Vaguettos</title>
    <link rel="stylesheet" href="../scr/css/pagos.css" />
</head>
<body>
<header>
    <a href="#" class="logo">
        <img src="../scr/imagenes/logo.jpg" alt="logo" />
        <h2>Vaguettos</h2>
    </a>
    <nav>
        <a href="indexadmin.php" class="nav-link">Inicio</a>
        <a href="usuarios.php" class="nav-link">Administración de Clientes</a>
        <a href="#" class="nav-link">Administración de Pagos</a>
        <a href="inventario.php" class="nav-link">Gestión de Inventario</a>
        <a href="stock.php" class="nav-link">Listado de Productos</a>
        <a href="../vista/login.php" class="nav-link">Cerrar Sesión</a>
    </nav>
</header>

<h1>Administración de Pagos</h1>

<div>
    <form method="post" action="">
        <input 
            type="search" 
            name="valueToSearch" 
            placeholder="Buscar usuario o fecha..." 
            value="<?= htmlspecialchars($searchValue) ?>" 
            autocomplete="off"
        />
        <button type="submit" name="search" title="Buscar">
            <img src="../scr/imagenes/busqueda.png" alt="Buscar" width="20" height="20" />
        </button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Fecha</th>
            <th>Monto Total</th>
            <th>Eliminar Carrito</th>
            <!--<th>Imprimir Factura</th>-->
        </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="5" style="text-align:center;">No se encontraron resultados.</td></tr>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= htmlspecialchars($row['fecha_creacion']) ?></td>
                <td>$<?= number_format(floatval($row['total']), 2) ?></td>
                <td style="text-align: center;">
                    <a href="../controlador/eliminar_carrito.php?id_carrito=<?= (int)$row['id_carrito'] ?>" onclick="return confirm('¿Eliminar este carrito?')">
                        <img src='../scr/imagenes/eliminar.png' alt='Eliminar' width='20' height='20' />
                    </a>
                </td>
                <!--<td style="text-align: center;">
                    <a href="../controlador/generar_factura.php?id_carrito=<?= (int)$row['id_carrito'] ?>" target="_blank" title="Imprimir Factura">
                        <img src='../scr/imagenes/imprimir.png' alt='Factura' width='20' height='20' />
                    </a>
                </td>// -->
            </tr>
        <?php endwhile; ?>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
