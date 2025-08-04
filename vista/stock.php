<?php
include '../modelo/conexion2.php';
$usarConexionRemota = true;
$conn = $usarConexionRemota ? $conn2 : $conn;

// Obtener categorías para el combobox
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");

// Consulta de productos con categorías
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
<style>
    .filtro-categoria {
        margin: 20px 5%;
        font-size: 1rem;
    }
</style>
</head>
<body>

<nav>
    <a href="indexadmin.php" class="nav-link">Inicio</a>
    <a href="usuarios.php" class="nav-link">Administración de Clientes</a>
    <a href="pagos.php" class="nav-link">Administración de Pagos</a>
    <a href="inventario.php" class="nav-link">Gestión de Inventario</a>
    <a href="#" class="nav-link">Listado de Productos</a>
    <a href="../vista/login.php" class="nav-link">Cerrar Sesión</a>
</nav>

<header>
    <img src="../scr/imagenes/logo.jpg" alt="Logo Vaguettos" />
    <h1>Listado de Productos</h1>
</header>

<!-- Combo de filtro por categoría -->
<div class="filtro-categoria">
    <label for="categoriaFiltro"><strong>Filtrar por categoría:</strong></label>
    <select id="categoriaFiltro" onchange="filtrarPorCategoria()">
        <option value="todos">-- Todas las categorías --</option>
        <?php while ($cat = $categorias->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($cat['nombre']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
        <?php endwhile; ?>
    </select>
</div>

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
        <tbody id="tablaProductos">
            <?php if ($res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <?php $estado = ($row['stock'] > 0) ? "En Stock" : "Agotado"; ?>
                    <tr data-categoria="<?= htmlspecialchars($row['categoria_nombre'] ?? 'Sin categoría') ?>">
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
                            <a href="../controlador/eliminar_producto.php?id=<?= $row['id_producto'] ?>" onclick="return confirm('¿Eliminar producto?');">
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

<!-- Script para filtrar por categoría -->
<script>
function filtrarPorCategoria() {
    const categoriaSeleccionada = document.getElementById('categoriaFiltro').value;
    const filas = document.querySelectorAll('#tablaProductos tr');

    filas.forEach(fila => {
        const categoria = fila.getAttribute('data-categoria');
        if (categoriaSeleccionada === 'todos' || categoria === categoriaSeleccionada) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>

</body>
</html>


