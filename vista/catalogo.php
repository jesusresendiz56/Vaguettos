<?php
// Conexión local y remota
include '../modelo/conexion.php';   // $conn (local)
include '../modelo/conexion2.php';  // $conn2 (remota)

// Puedes elegir aquí cuál conexión usar:
// $db = $conn;    // Local
$db = $conn2;      // Remota

// Obtener opciones únicas para los filtros 
$submarcas = mysqli_query($db, "SELECT DISTINCT modelo_auto FROM productos ORDER BY modelo_auto");
$fechas = mysqli_query($db, "SELECT DISTINCT fechas_aplicables FROM productos ORDER BY fechas_aplicables");
$tipos = mysqli_query($db, "SELECT DISTINCT tipo FROM productos ORDER BY tipo");

$f_submarca = $_GET['modelo_auto'] ?? '';
$f_fecha = $_GET['fecha'] ?? '';
$f_tipo = $_GET['tipo'] ?? '';

// Construir la cláusula WHERE según los filtros seleccionados
$where = [];
if ($f_submarca !== '') $where[] = "modelo_auto = '" . mysqli_real_escape_string($db, $f_submarca) . "'";
if ($f_fecha !== '')    $where[] = "fechas_aplicables = '" . mysqli_real_escape_string($db, $f_fecha) . "'";
if ($f_tipo !== '')     $where[] = "tipo = '" . mysqli_real_escape_string($db, $f_tipo) . "'";

$where_sql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : '';

// Consultar productos filtrados
$productos = mysqli_query($db, "SELECT * FROM productos $where_sql");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Catálogo Vaguettos</title>
  <link rel="stylesheet" href="../scr/css/catalogo.css">
</head>
<body>

<header>
  <a href="#" class="logo">
    <img src="../scr/imagenes/logo.jpg" alt="Logo" />
    <h2>Vaguettos</h2>
  </a>

  <nav>
    <a href="index.php" class="nav-link">Inicio</a>
    <a href="#" class="nav-link">Catálogo de Productos</a>
    <a href="#" class="nav-link">Carrito de Compras</a>
    <a href="#" class="nav-link">Editar Perfil</a>
    <a href="cerrarSesion.html" class="nav-link">Cerrar Sesión</a>
  </nav>
</header>

<main class="container">
  <h1 class="title">Catálogo de Productos</h1>

  <!-- Formulario de filtros -->
  <form method="GET" class="filter-form">
    <select name="modelo_auto" onchange="this.form.submit()">
      <option value="">Submarca (Todas)</option>
      <?php while ($row = mysqli_fetch_assoc($submarcas)): ?>
        <option value="<?= htmlspecialchars($row['modelo_auto']) ?>" <?= $row['modelo_auto'] === $f_submarca ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['modelo_auto']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <select name="fecha" onchange="this.form.submit()">
      <option value="">Fecha (Todas)</option>
      <?php while ($row = mysqli_fetch_assoc($fechas)): ?>
        <option value="<?= htmlspecialchars($row['fechas_aplicables']) ?>" <?= $row['fechas_aplicables'] === $f_fecha ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['fechas_aplicables']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <select name="tipo" onchange="this.form.submit()">
      <option value="">Tipo (Todos)</option>
      <?php while ($row = mysqli_fetch_assoc($tipos)): ?>
        <option value="<?= htmlspecialchars($row['tipo']) ?>" <?= $row['tipo'] === $f_tipo ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['tipo']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <!-- Lista de productos -->
  <section class="productos-grid">
    <?php if (mysqli_num_rows($productos) > 0): ?>
      <?php while ($p = mysqli_fetch_assoc($productos)): ?>
        <article class="producto-card">
          <img src="../scr/productos/<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy">
          <h2 class="producto-nombre"><?= htmlspecialchars($p['nombre']) ?></h2>
          <p class="producto-precio">$<?= number_format($p['precio'], 2) ?></p>
          <a href="detalle_producto.php?id=<?= intval($p['id_producto']) ?>" class="btn-ver-mas">Ver más</a>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="sin-resultados">No se encontraron productos con esos filtros.</p>
    <?php endif; ?>
  </section>
</main>

</body>
</html>
