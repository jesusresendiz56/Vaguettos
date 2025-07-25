<?php
include '../modelo/conexion.php';

// Consulta para obtener todos los productos con sus categorías
$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        ORDER BY p.id_producto DESC";
$res = $conn->query($sql);

if (!$res) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Listado de Productos - Vaguettos</title>
<link rel="stylesheet" href="../scr/css/stock.css" />

</head>
<body>

<nav>
    <a href="indexadmin.html" class="nav-link">Inicio</a>
    <a href="usuarios.php" class="nav-link">Administración de Clientes</a>
    <a href="pagos.html" class="nav-link">Administración de Pagos</a>
    <a href="inventario.php" class="nav-link">Gestión de Inventario</a>
    <a href="cerrarSesion.html" class="nav-link">Cerrar Sesión</a>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Listado de Productos</h1>
</header>

<section>
    <table>
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Disponibilidad</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <?php $estado = ($row['stock'] > 0) ? "En Stock" : "Agotado"; ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['imagen_url'])): ?>
                                <img src="../scr/imagenes/productos/<?= htmlspecialchars($row['imagen_url']) ?>" alt="Imagen de <?= htmlspecialchars($row['nombre']) ?>" class="product-img" />
                            <?php else: ?>
                                Sin imagen
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['categoria_nombre'] ?? 'Sin categoría') ?></td>
                        <td>$<?= htmlspecialchars(number_format($row['precio'], 2)) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>
                        <td><?= $estado ?></td>
                        <td><a href="inventario.php?id=<?= $row['id_producto'] ?>">
                            <img src='../scr/imagenes/editarproducto.png' alt='Editar' width='20' height='20'>
                        </a></td>
                        <td>
                            <a href="eliminar_producto.php?id=<?= $row['id_producto'] ?>" onclick="return confirm('¿Eliminar producto?');">
                                <img src='../scr/imagenes/eliminar.png' alt='Eliminar' width='20' height='20'>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No hay productos registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<button onclick="location.href='cerrarSesion.html'">Salir</button>

</body>
</html>

