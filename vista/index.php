<?php
// --------------------- SESIÓN E INACTIVIDAD ---------------------
ini_set('session.gc_maxlifetime', 604800); // 7 días
ini_set('session.cookie_lifetime', 604800);
session_set_cookie_params(604800);
session_start();

$tiempo_max_inactivo = 300;

if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > $tiempo_max_inactivo) {
        session_unset();
        session_destroy();
        header("Location: login.php?expirado=1");
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time();

$usuario = $_SESSION['usuario'] ?? null;
$nombre_usuario = $_SESSION['nombre_completo'] ?? $usuario ?? null;

// --- Conexión a la base de datos ---
include '../modelo/conexion2.php';
$conn = $conn2;

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta productos destacados
$sql = "
SELECT p.*, c.nombre AS nombre_categoria
FROM categorias c
LEFT JOIN productos p ON p.id_categoria = c.id_categoria
AND p.id_producto = (
    SELECT MAX(id_producto)
    FROM productos
    WHERE id_categoria = c.id_categoria
)
ORDER BY c.nombre;
";

$resultado_productos = $conn->query($sql);

if (!$resultado_productos) {
    die("Error en la consulta: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Vaguettos Accesorios</title>
  <link rel="stylesheet" href="../scr/css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>
<header>
  <div class="top-bar">
    <img src="../scr/imagenes/logo.jpg" alt="Vaguettos Logo" class="logo" width="80" height="80">
    <p>Vaguettos</p>

    <?php if ($nombre_usuario): ?>
      <form method="POST" action="../controlador/logout.php" style="display:inline;">
        <button type="submit" class="btn-login">
          <?= htmlspecialchars($nombre_usuario); ?> (Cerrar sesión)
        </button>
      </form>
    <?php else: ?>
      <a href="login.php" class="btn-login">Iniciar Sesión</a>
    <?php endif; ?>
  </div>
  <nav class="main-nav">
    <a href="index.php">Inicio</a>
    <a href="catalogo.php">Catálogo de Productos</a>
    <?php if ($usuario): ?>
      <a href="carrito.php">Carrito de Compras</a>
      <a href="editar_perfil.php">Editar Perfil</a>
      <a href="../controlador/logout.php">Cerrar sesión</a>
    <?php endif; ?>
  </nav>
</header>

<section class="banner">
  <h1>“Todo Para Tu Auto, Cuando Y<br>Donde Quieras”</h1>
</section>

<section class="productos">
  <h2>Productos Destacados</h2>
  <div class="cards">
    <?php while ($producto = $resultado_productos->fetch_assoc()): ?>
      <?php if ($producto['id_producto'] !== null): ?>
        <div class="card">
          <span class="categoria"><?= htmlspecialchars($producto['nombre_categoria']) ?></span>
          <img src="../scr/imagenes/productos/<?= htmlspecialchars($producto['imagen_url']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
          <p class="precio">$<?= number_format($producto['precio'], 2) ?></p>
        </div>
      <?php endif; ?>
    <?php endwhile; ?>
  </div>
</section>

<section class="marcas">
  <h2>Marcas Destacadas</h2>
  <div class="marca-botones">
    <button>Jetta</button>
    <button>Golf</button>
    <button>Tiguan</button>
    <button>Beetle</button>
    <button>Polo</button>
  </div>
</section>

<section class="info">
  <div class="servicios">
    <h3>Nuestros Servicios</h3>
    <ul>
      <li>Compra en Línea rápida y segura</li>
      <li>Servicio técnico especializado a distancia</li>
      <li>Envío a domicilio</li>
      <li>Garantía de satisfacción en cada pedido</li>
    </ul>
  </div>
  <div class="ubicacion">
    <h3>Ubicación</h3>
    <div id="mapa" style="width: 250px; height: 250px; border-radius: 10px; border: 2px solid #c2c7d0; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></div>
  </div>
  <script>
    function initMap() {
      const location = { lat: 19.4038383, lng: -98.9882725 };
      const map = new google.maps.Map(document.getElementById("mapa"), {
        zoom: 14,
        center: location,
      });
      const marker = new google.maps.Marker({ position: location, map: map });
    }
  </script>
  <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCdpfD74JIrCWQHOzMWlJSgxl-20HZC_Y4&callback=initMap"></script>
</section>

<!--! Redes Sociales -->
<section class="redes" style="text-align: center; padding: 2rem;">
  <h3 style="font-size: 1.8rem; color: #2c3e50; margin-bottom: 1rem;">Nuestras Redes</h3>

  <div class="mini-posts-instagram" style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; justify-content: center;">
    <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/DMImVJcsH5y/?igsh=dWF4a2RvaTd2YXB0" data-instgrm-version="14" style="max-width: 320px; width: 100%;">
      <a href="https://www.instagram.com/reel/DMYltDKSnEs/?igsh=OHpodm94cWYwMjdp" target="_blank" rel="noopener noreferrer">Ver esta publicación en Instagram</a>
    </blockquote>

    <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/DMdN0d3MZsV/?igsh=MXhjZHo5bXVoYXlucw==" data-instgrm-version="14" style="max-width: 320px; width: 100%;">
      <a href="https://www.instagram.com/reel/DMdN0d3MZsV/?igsh=MXhjZHo5bXVoYXlucw==" target="_blank" rel="noopener noreferrer">Ver esta publicación en Instagram</a>
    </blockquote>
  </div>

  <p style="margin-top: 2rem;">
    <a href="https://www.instagram.com/volkswagenmexico" target="_blank" style="padding: 0.6rem 1.2rem; background-color: #E1306C; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 1rem; display: inline-block;">
      Visita nuestro Instagram Oficial
    </a>
  </p>

  <script async src="//www.instagram.com/embed.js"></script>
</section>





<footer class="footer">
  <div class="footer-links">
    <a href="#">Contacto</a>
    <a href="#">Servicios al Cliente</a>
    <a href="#">Información</a>
  </div>
  <p>&copy; 2025 VAGUETTOS</p>
</footer>

<script>
  let tiempoInactivo = 0;
  const limite = 300;

  function resetInactividad() { tiempoInactivo = 0; }
  ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt =>
    window.addEventListener(evt, resetInactividad)
  );

  setInterval(() => {
    tiempoInactivo++;
    if (tiempoInactivo === (limite - 60)) {
      alert("\u26a0 Tu sesión está por expirar en 1 minuto por inactividad.");
    }
    if (tiempoInactivo < limite) {
      fetch("../controlador/ping.php").catch(() => {});
    } else {
      window.location.href = "../controlador/sesion_headler.php";
    }
  }, 1000);
</script>
</body>
</html>
