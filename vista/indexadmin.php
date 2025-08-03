<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../vista/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>administrador</title>
  <link rel="stylesheet" href="../scr/css/indexadmin.css" />
</head>

<body>
  <header>
    <a href="#" class="logo">
      <img src="../scr/imagenes/logo.jpg" alt="Logo" />
      <h2>Vaguettos</h2>
    </a>

    <nav>
      <a href="#" class="nav-link">Inicio</a>
      <a href="usuarios.php" class="nav-link">Administración de Clientes</a>
      <a href="pagos.php" class="nav-link">Administración de Pagos</a>
      <a href="inventario.php" class="nav-link">Gestión de Inventario</a>
      <a href="stock.php" class="nav-link">Listado de Productos</a>
      <a href="../vista/login.php" class="nav-link">Cerrar Sesión</a>
    </nav>

    <h1 class="titulo-principal">Panel de Administración</h1>
  </header>

  <section>
    <h2>¡Bienvenido, <?php echo $_SESSION['nombre_completo']; ?>!</h2>
    <p>
      Desde aquí puedes gestionar los módulos del sistema: clientes, pagos e inventario.
      Usa la barra de navegación superior para acceder a cada sección.
    </p>
  </section>
</body>
</html>
