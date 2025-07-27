<?php
// --- Incluir conexiones ---
include '../modelo/conexion.php';   // local
//include '../modelo/conexion2.php';  // remota

// --- Cambiar aquí a false para usar local ---
$usarRemoto = false;
$conn = $usarRemoto ? $conn2 : $conn;

// Obtener opciones únicas para los filtros
$submarcas = $conn->query("SELECT DISTINCT modelo_auto FROM productos ORDER BY modelo_auto");
$fechas = $conn->query("SELECT DISTINCT years_aplicables FROM productos ORDER BY years_aplicables");
$tipos = $conn->query("SELECT DISTINCT tipo FROM productos ORDER BY tipo");

$f_submarca = $_GET['modelo_auto'] ?? '';
$f_fecha = $_GET['fecha'] ?? '';
$f_tipo = $_GET['tipo'] ?? '';

$where = [];
if ($f_submarca !== '') $where[] = "modelo_auto = '" . $conn->real_escape_string($f_submarca) . "'";
if ($f_fecha !== '')    $where[] = "years_aplicables = '" . $conn->real_escape_string($f_fecha) . "'";
if ($f_tipo !== '')     $where[] = "tipo = '" . $conn->real_escape_string($f_tipo) . "'";

$where_sql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : '';

$productos = $conn->query("SELECT * FROM productos $where_sql");
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
    <a href="carrito.html" class="nav-link">Carrito de Compras</a>
    <a href="#" class="nav-link">Editar Perfil</a>
  
  </nav>
</header>

<main class="container">
  <h1 class="title">Catálogo de Productos</h1>

  <!-- Formulario de filtros -->
  <form method="GET" class="filter-form">
    <select name="modelo_auto" onchange="this.form.submit()">
      <option value="">Submarca (Todas)</option>
      <?php while ($row = $submarcas->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['modelo_auto']) ?>" <?= $row['modelo_auto'] === $f_submarca ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['modelo_auto']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <select name="fecha" onchange="this.form.submit()">
      <option value="">Años aplicables (Todos)</option>
      <?php while ($row = $fechas->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['years_aplicables']) ?>" <?= $row['years_aplicables'] === $f_fecha ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['years_aplicables']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <select name="tipo" onchange="this.form.submit()">
      <option value="">Tipo (Todos)</option>
      <?php while ($row = $tipos->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['tipo']) ?>" <?= $row['tipo'] === $f_tipo ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['tipo']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <!-- Lista de productos -->
  <section class="productos-grid">
    <?php if ($productos && $productos->num_rows > 0): ?>
      <?php while ($p = $productos->fetch_assoc()): ?>
        <article class="producto-card">
          <img src="../scr/imagenes/productos/<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy">
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
