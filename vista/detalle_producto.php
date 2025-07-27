<?php

//include '../modelo/conexion.php';   // conexión local ($conn)
include '../modelo/conexion2.php';  // conexión remota ($conn2)


$usarRemoto = true;
$db = $usarRemoto ? $conn2 : $conn;


$id_producto = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_producto <= 0) {
    die("ID de producto inválido.");
}

// Preparar la consulta para obtener el producto
$stmt = $db->prepare("SELECT p.*, c.nombre AS categoria_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.id_producto = ?");
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Producto no encontrado.");
}

$producto = $resultado->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detalle del Producto - <?= htmlspecialchars($producto['nombre']) ?></title>
  <link rel="stylesheet" href="../scr/css/detalle_producto.css" />
</head>
<body>

<header>
  <a href="#" class="logo">
    <img src="../scr/imagenes/logo.jpg" alt="Logo" />
    <h2>Vaguettos</h2>
  </a>

  <nav>
    <a href="index.php" class="nav-link">Inicio</a>
    <a href="catalogo.php" class="nav-link">Catálogo de Productos</a>
    <a href="#" class="nav-link">Carrito de Compras</a>
    <a href="#" class="nav-link">Editar Perfil</a>
    <a href="cerrarSesion.html" class="nav-link">Cerrar Sesión</a>
  </nav>
</header>

<main>
  <h1><?= htmlspecialchars($producto['nombre']) ?></h1>

  <img src="../scr/imagenes/productos/<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" loading="lazy">

  <p><strong>Categoría:</strong> <?= htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría') ?></p>
  <p><strong>Modelo de Auto:</strong> <?= htmlspecialchars($producto['modelo_auto']) ?></p>
  <p><strong>Tipo de accesorio:</strong> <?= htmlspecialchars($producto['tipo']) ?></p>
  <p><strong>Años aplicables:</strong> <?= htmlspecialchars($producto['years_aplicables']) ?></p>
  <p><strong>Precio:</strong> $<?= number_format($producto['precio'], 2) ?></p>
  <p><strong>Stock disponible:</strong> <?= intval($producto['stock']) ?></p>

  <h3>Descripción</h3>
  <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

  <p><a href="catalogo.php">&larr; Volver al catálogo</a></p>
</main>

</body>
</html>
