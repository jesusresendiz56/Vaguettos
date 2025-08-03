<?php
session_start();
require_once '../modelo/conexion2.php';

// Búsqueda
$where = "";
if (isset($_POST['search']) && !empty($_POST['valueToSearch'])) {
    $valueToSearch = $conn2->real_escape_string($_POST['valueToSearch']);
    $where = "AND (u.nombre_completo LIKE '%$valueToSearch%' OR c.fecha_creacion LIKE '%$valueToSearch%')";
}

// Consulta carritos con totales calculados
$query = "SELECT c.id_carrito, u.nombre_completo, c.fecha_creacion,
          (SELECT SUM(p.precio * cp.cantidad)
           FROM carrito_productos cp
           JOIN productos p ON cp.id_producto = p.id_producto
           WHERE cp.id_carrito = c.id_carrito) AS total
          FROM carritos c
          JOIN usuarios u ON c.id_usuario = u.id_usuario
          WHERE 1=1 $where
          ORDER BY c.fecha_creacion DESC";
$result = $conn2->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Administrador de Pagos (Basado en Carritos)</title>
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
    <div>
        <h1 class="titulo-principal">Administración de Pagos (Basado en Carritos)</h1>
    </div>
</header>

<div class="index-container">
    <form method="post">
        <div class="btn">
            <input type="search" name="valueToSearch" placeholder="Buscar..." value="<?= htmlspecialchars($_POST['valueToSearch'] ?? '') ?>" />
            <button type="submit" class="singnupbtn" name="search">
                <img src="../scr/imagenes/busqueda.png" alt="Buscar" width="20" height="20" />
            </button>
        </div>
    </form>
</div><br />

<table>
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Fecha</th>
            <th>Monto Total</th>
            <th>Eliminar Carrito</th>
            <th>Imprimir Factura</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
            <td><?= htmlspecialchars($row['fecha_creacion']) ?></td>
            <td>$<?= number_format($row['total'] ?? 0, 2) ?></td>
            <td>
                <a href="eliminar_carrito.php?id_carrito=<?= $row['id_carrito'] ?>" onclick="return confirm('¿Eliminar este carrito?')"><img src='../scr/imagenes/eliminar.png' alt='Eliminar' width='20' height='20'></a></td>
                  </a>
            </td>
            <td>
                <a href="../controlador/generar_factura.php?id_carrito=<?= $row['id_carrito'] ?>" target="_blank">🧾</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>

